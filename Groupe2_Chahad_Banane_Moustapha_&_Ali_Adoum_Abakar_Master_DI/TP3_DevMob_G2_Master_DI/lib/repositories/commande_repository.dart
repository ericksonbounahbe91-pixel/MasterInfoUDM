import '../core/database.dart';
import '../models/commande.dart';
import '../models/ligne_commande.dart';
import '../models/produit.dart';
import 'client_repository.dart' show ErreurMetier;

/// Équivalent de CommandeController + modèle Commande (avec la gestion du stock).
class CommandeRepository {
  static const _selectCommande = '''
    SELECT cm.idCmd, cm.dateCmd, cm.idCli,
           cl.prenomCli || ' ' || cl.nomCli AS client,
           r.nomRegion, d.nomDep
    FROM commande cm
    JOIN client cl ON cl.idCli = cm.idCli
    JOIN region r ON r.idRegion = cl.idRegion
    JOIN departement d ON d.idDep = r.idDep
  ''';

  /// Liste (≈ index) : triée par date décroissante, avec totaux
  Future<List<Commande>> toutes() async {
    final db = await AppDatabase.instance;
    final rows = await db
        .rawQuery('$_selectCommande ORDER BY cm.dateCmd DESC, cm.idCmd DESC');
    final out = <Commande>[];
    for (final m in rows) {
      out.add(_map(m, await _lignes(m['idCmd'] as int)));
    }
    return out;
  }

  /// Détail d'une commande (≈ show)
  Future<Commande?> parId(int id) async {
    final db = await AppDatabase.instance;
    final rows =
    await db.rawQuery('$_selectCommande WHERE cm.idCmd = ?', [id]);
    if (rows.isEmpty) return null;
    return _map(rows.first, await _lignes(id));
  }

  Commande _map(Map<String, Object?> m, List<LigneCommande> l) => Commande(
    id: m['idCmd'] as int,
    date: DateTime.parse(m['dateCmd'] as String),
    idCli: m['idCli'] as int,
    clientNom: m['client'] as String,
    nomRegion: m['nomRegion'] as String,
    nomDep: m['nomDep'] as String,
    lignes: l,
  );

  Future<List<LigneCommande>> _lignes(int idCmd) async {
    final db = await AppDatabase.instance;
    final r = await db.rawQuery('''
      SELECT p.idProd, p.designProd, p.prixUProd, p.qteStock, lc.qteCmd
      FROM ligneCommande lc JOIN produit p ON p.idProd = lc.idProd
      WHERE lc.idCmd = ? ORDER BY p.designProd''', [idCmd]);
    return r
        .map((m) => LigneCommande(
      idProd: m['idProd'] as int,
      design: m['designProd'] as String,
      prix: (m['prixUProd'] as num).toDouble(),
      qte: m['qteCmd'] as int,
      stockActuel: m['qteStock'] as int,
    ))
        .toList();
  }

  /// Produits ajoutables : en stock et pas déjà dans la commande
  Future<List<Produit>> produitsDisponibles(int idCmd) async {
    final db = await AppDatabase.instance;
    final r = await db.rawQuery('''
      SELECT * FROM produit
      WHERE qteStock > 0
        AND idProd NOT IN (SELECT idProd FROM ligneCommande WHERE idCmd = ?)
      ORDER BY designProd''', [idCmd]);
    return r.map(Produit.fromMap).toList();
  }

  /// Création (≈ store). Retourne l'id de la nouvelle commande.
  Future<int> creer({required int idCli, required DateTime date}) async {
    final db = await AppDatabase.instance;
    return db.insert('commande', {
      'idCli': idCli,
      'dateCmd': date.toIso8601String().substring(0, 10),
    });
  }

  /// Ajouter un produit (≈ ajouterProduit) : vérifie doublon + stock
  Future<void> ajouterProduit(int idCmd, int idProd, int qte) async {
    final db = await AppDatabase.instance;
    await db.transaction((tx) async {
      final p = (await tx
          .query('produit', where: 'idProd = ?', whereArgs: [idProd]))
          .first;
      final stock = p['qteStock'] as int;

      final dejaLa = await tx.query('ligneCommande',
          where: 'idCmd = ? AND idProd = ?', whereArgs: [idCmd, idProd]);
      if (dejaLa.isNotEmpty) {
        throw ErreurMetier('Ce produit figure déjà dans la commande.');
      }
      if (qte > stock) {
        throw ErreurMetier(
            "Stock insuffisant : il ne reste que $stock unité(s) de « ${p['designProd']} ».");
      }
      await tx.insert(
          'ligneCommande', {'idCmd': idCmd, 'idProd': idProd, 'qteCmd': qte});
      await tx.rawUpdate(
          'UPDATE produit SET qteStock = qteStock - ? WHERE idProd = ?',
          [qte, idProd]);
    });
  }

  /// Modifier la quantité (≈ modifierProduit) : ajuste le stock de la différence
  Future<void> modifierQuantite(int idCmd, int idProd, int nouvelle) async {
    final db = await AppDatabase.instance;
    await db.transaction((tx) async {
      final l = await tx.query('ligneCommande',
          where: 'idCmd = ? AND idProd = ?', whereArgs: [idCmd, idProd]);
      if (l.isEmpty) {
        throw ErreurMetier('Ce produit ne figure pas dans cette commande.');
      }
      final p = (await tx
          .query('produit', where: 'idProd = ?', whereArgs: [idProd]))
          .first;
      final ancienne = l.first['qteCmd'] as int;
      final stock = p['qteStock'] as int;
      final diff = nouvelle - ancienne;

      if (diff > stock) {
        throw ErreurMetier(
            'Stock insuffisant : vous pouvez commander au maximum ${stock + ancienne} '
                "unité(s) de « ${p['designProd']} ».");
      }
      await tx.update('ligneCommande', {'qteCmd': nouvelle},
          where: 'idCmd = ? AND idProd = ?', whereArgs: [idCmd, idProd]);
      // diff > 0 : on prend du stock ; diff < 0 : on en remet
      await tx.rawUpdate(
          'UPDATE produit SET qteStock = qteStock - ? WHERE idProd = ?',
          [diff, idProd]);
    });
  }

  /// Retirer un produit (≈ retirerProduit) : la quantité retourne en stock
  Future<void> retirerProduit(int idCmd, int idProd) async {
    final db = await AppDatabase.instance;
    await db.transaction((tx) async {
      final l = await tx.query('ligneCommande',
          where: 'idCmd = ? AND idProd = ?', whereArgs: [idCmd, idProd]);
      if (l.isEmpty) {
        throw ErreurMetier('Ce produit ne figure pas dans cette commande.');
      }
      final qte = l.first['qteCmd'] as int;

      await tx.delete('ligneCommande',
          where: 'idCmd = ? AND idProd = ?', whereArgs: [idCmd, idProd]);
      await tx.rawUpdate(
          'UPDATE produit SET qteStock = qteStock + ? WHERE idProd = ?',
          [qte, idProd]);
    });
  }

  /// Supprimer toute la commande (≈ destroy) : remet tout en stock
  Future<void> supprimer(int idCmd) async {
    final db = await AppDatabase.instance;
    await db.transaction((tx) async {
      final lignes = await tx
          .query('ligneCommande', where: 'idCmd = ?', whereArgs: [idCmd]);
      for (final l in lignes) {
        await tx.rawUpdate(
            'UPDATE produit SET qteStock = qteStock + ? WHERE idProd = ?',
            [l['qteCmd'], l['idProd']]);
      }
      // les lignes partent aussi via ON DELETE CASCADE
      await tx.delete('commande', where: 'idCmd = ?', whereArgs: [idCmd]);
    });
  }

  /// Équivalent de CommandeController::update : tout est enregistré, ou rien.
  Future<void> mettreAJour(
      int idCmd, {
        required int idCli,
        required DateTime date,
        required Map<int, int> quantites, // idProd -> nouvelle quantité
        required Set<int> aRetirer, // produits à retirer
        int? nouveauProd, // produit ajouté (facultatif)
        int nouveauQte = 1,
      }) async {
    final db = await AppDatabase.instance;
    await db.transaction((tx) async {
      // 1. Client et date
      await tx.update(
        'commande',
        {'idCli': idCli, 'dateCmd': date.toIso8601String().substring(0, 10)},
        where: 'idCmd = ?',
        whereArgs: [idCmd],
      );

      // 2. Lignes existantes : retrait ou changement de quantité
      final lignes = await tx.rawQuery('''
        SELECT lc.idProd, lc.qteCmd, p.designProd, p.qteStock
        FROM ligneCommande lc JOIN produit p ON p.idProd = lc.idProd
        WHERE lc.idCmd = ?''', [idCmd]);

      for (final l in lignes) {
        final id = l['idProd'] as int;
        final ancienne = l['qteCmd'] as int;
        final stock = l['qteStock'] as int;
        final nom = l['designProd'] as String;

        if (aRetirer.contains(id)) {
          await tx.delete('ligneCommande',
              where: 'idCmd = ? AND idProd = ?', whereArgs: [idCmd, id]);
          await tx.rawUpdate(
              'UPDATE produit SET qteStock = qteStock + ? WHERE idProd = ?',
              [ancienne, id]); // la quantité retourne en stock
          continue;
        }

        final nouvelle = quantites[id] ?? ancienne;
        final diff = nouvelle - ancienne;
        if (diff > stock) {
          throw ErreurMetier(
              'Stock insuffisant pour « $nom » : maximum ${stock + ancienne} unité(s).');
        }
        if (diff != 0) {
          await tx.update('ligneCommande', {'qteCmd': nouvelle},
              where: 'idCmd = ? AND idProd = ?', whereArgs: [idCmd, id]);
          await tx.rawUpdate(
              'UPDATE produit SET qteStock = qteStock - ? WHERE idProd = ?',
              [diff, id]);
        }
      }

      // 3. Produit ajouté (facultatif)
      if (nouveauProd != null) {
        final p = (await tx.query('produit',
            where: 'idProd = ?', whereArgs: [nouveauProd]))
            .first;
        final stock = p['qteStock'] as int;

        final dejaLa = await tx.query('ligneCommande',
            where: 'idCmd = ? AND idProd = ?',
            whereArgs: [idCmd, nouveauProd]);
        if (dejaLa.isNotEmpty) {
          throw ErreurMetier('Ce produit figure déjà dans la commande.');
        }
        if (nouveauQte > stock) {
          throw ErreurMetier(
              "Stock insuffisant : il ne reste que $stock unité(s) de « ${p['designProd']} ».");
        }
        await tx.insert('ligneCommande',
            {'idCmd': idCmd, 'idProd': nouveauProd, 'qteCmd': nouveauQte});
        await tx.rawUpdate(
            'UPDATE produit SET qteStock = qteStock - ? WHERE idProd = ?',
            [nouveauQte, nouveauProd]);
      }
    });
  }
}