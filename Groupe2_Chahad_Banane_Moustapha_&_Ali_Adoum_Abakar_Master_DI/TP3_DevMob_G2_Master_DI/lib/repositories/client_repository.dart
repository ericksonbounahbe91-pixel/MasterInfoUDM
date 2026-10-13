import 'package:sqflite/sqflite.dart';
import '../core/database.dart';
import '../models/client.dart';

/// Erreur métier affichable à l'utilisateur (équivalent de ValidationException).
class ErreurMetier implements Exception {
  final String message;
  ErreurMetier(this.message);
  @override
  String toString() => message;
}

class ClientRepository {
  static const _select = '''
    SELECT c.*, r.nomRegion, d.nomDep
    FROM client c
    JOIN region r ON r.idRegion = c.idRegion
    JOIN departement d ON d.idDep = r.idDep
  ''';

  Future<List<Client>> tous() async {
    final db = await AppDatabase.instance;
    final rows = await db.rawQuery('$_select ORDER BY c.nomCli');
    return rows.map(Client.fromMap).toList();
  }

  Future<Client?> parId(int id) async {
    final db = await AppDatabase.instance;
    final rows = await db.rawQuery('$_select WHERE c.idCli = ?', [id]);
    return rows.isEmpty ? null : Client.fromMap(rows.first);
  }

  /// Suggestions pour les champs avec <datalist> du site web
  Future<List<String>> departements() async {
    final db = await AppDatabase.instance;
    final r =
    await db.query('departement', columns: ['nomDep'], orderBy: 'nomDep');
    return r.map((e) => e['nomDep'] as String).toList();
  }

  Future<List<String>> regions() async {
    final db = await AppDatabase.instance;
    final r = await db
        .rawQuery('SELECT DISTINCT nomRegion FROM region ORDER BY nomRegion');
    return r.map((e) => e['nomRegion'] as String).toList();
  }

  /// firstOrCreate du département puis de la région (même logique que Laravel)
  Future<int> _regionId(
      DatabaseExecutor tx, String nomDep, String nomRegion) async {
    nomDep = nomDep.trim();
    nomRegion = nomRegion.trim();

    final dep = await tx.query('departement',
        where: 'nomDep = ?', whereArgs: [nomDep], limit: 1);
    final idDep = dep.isNotEmpty
        ? dep.first['idDep'] as int
        : await tx.insert('departement', {'nomDep': nomDep});

    final reg = await tx.query('region',
        where: 'nomRegion = ? AND idDep = ?',
        whereArgs: [nomRegion, idDep],
        limit: 1);
    return reg.isNotEmpty
        ? reg.first['idRegion'] as int
        : await tx.insert('region', {'nomRegion': nomRegion, 'idDep': idDep});
  }

  /// Création (id == null) ou modification d'un client.
  Future<void> enregistrer({
    int? id,
    required String nom,
    required String prenom,
    required String email,
    required String nomDep,
    required String nomRegion,
  }) async {
    final db = await AppDatabase.instance;
    email = email.trim().toLowerCase();

    // Règle `unique:client,email` à la création
    if (id == null) {
      final dup =
      await db.query('client', where: 'email = ?', whereArgs: [email]);
      if (dup.isNotEmpty) {
        throw ErreurMetier('Cet email est déjà utilisé par un autre client.');
      }
    }

    try {
      // Transaction : tout est enregistré, ou rien (comme DB::transaction)
      await db.transaction((tx) async {
        final idRegion = await _regionId(tx, nomDep, nomRegion);
        final data = {
          'nomCli': nom,
          'prenomCli': prenom,
          'email': email,
          'idRegion': idRegion,
        };
        if (id == null) {
          await tx.insert('client', data);
        } else {
          await tx.update('client', data, where: 'idCli = ?', whereArgs: [id]);
        }
      });
    } on DatabaseException catch (e) {
      // Modification avec un e-mail déjà pris par un autre client
      if (e.isUniqueConstraintError()) {
        throw ErreurMetier('Cet email est déjà utilisé par un autre client.');
      }
      rethrow;
    }
  }
}