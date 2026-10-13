import 'package:flutter_test/flutter_test.dart';
import 'package:sqflite_common_ffi/sqflite_ffi.dart';
import 'package:gestion_commande/core/database.dart';
import 'package:gestion_commande/repositories/client_repository.dart';
import 'package:gestion_commande/repositories/commande_repository.dart';

void main() {
  sqfliteFfiInit();
  databaseFactory = databaseFactoryFfi;

  final repo = CommandeRepository();
  late int idCmd;
  late int idProd;
  late int idCli;

  Future<int> stock() async {
    final db = await AppDatabase.instance;
    final r =
    await db.query('produit', where: 'idProd = ?', whereArgs: [idProd]);
    return r.first['qteStock'] as int;
  }

  setUp(() async {
    await AppDatabase.reset();
    AppDatabase.overridePath = inMemoryDatabasePath; // base vide en mémoire
    final db = await AppDatabase.instance;
    final dep = await db.insert('departement', {'nomDep': 'D'});
    final reg = await db.insert('region', {'nomRegion': 'R', 'idDep': dep});
    idCli = await db.insert('client', {
      'nomCli': 'A',
      'prenomCli': 'B',
      'email': 'a@b.c',
      'idRegion': reg,
    });
    idProd = await db.insert(
        'produit', {'designProd': 'Riz', 'qteStock': 10, 'prixUProd': 1000});
    idCmd = await repo.creer(idCli: idCli, date: DateTime(2026, 10, 11));
  });

  test('ajouter un produit décrémente le stock', () async {
    await repo.ajouterProduit(idCmd, idProd, 3);
    expect(await stock(), 7);
  });

  test('refuse une quantité supérieure au stock', () async {
    await expectLater(
        repo.ajouterProduit(idCmd, idProd, 11), throwsA(isA<ErreurMetier>()));
    expect(await stock(), 10); // rien n'a changé
  });

  test('refuse le même produit deux fois', () async {
    await repo.ajouterProduit(idCmd, idProd, 2);
    await expectLater(
        repo.ajouterProduit(idCmd, idProd, 1), throwsA(isA<ErreurMetier>()));
  });

  test('modifier la quantité ajuste le stock de la différence', () async {
    await repo.ajouterProduit(idCmd, idProd, 3); // stock 7
    await repo.modifierQuantite(idCmd, idProd, 5); // stock 5
    expect(await stock(), 5);
    await repo.modifierQuantite(idCmd, idProd, 1); // stock 9
    expect(await stock(), 9);
  });

  test('retirer un produit remet la quantité en stock', () async {
    await repo.ajouterProduit(idCmd, idProd, 4);
    await repo.retirerProduit(idCmd, idProd);
    expect(await stock(), 10);
  });

  test('supprimer la commande remet tout en stock', () async {
    await repo.ajouterProduit(idCmd, idProd, 6);
    await repo.supprimer(idCmd);
    expect(await stock(), 10);
    expect(await repo.parId(idCmd), isNull);
  });

  test('mettreAJour est annulé en entier si le stock est insuffisant', () async {
    await repo.ajouterProduit(idCmd, idProd, 3); // stock 7
    await expectLater(
      repo.mettreAJour(idCmd,
          idCli: idCli,
          date: DateTime(2026, 10, 12),
          quantites: {idProd: 50},
          aRetirer: {}),
      throwsA(isA<ErreurMetier>()),
    );
    expect(await stock(), 7); // transaction annulée
  });
}