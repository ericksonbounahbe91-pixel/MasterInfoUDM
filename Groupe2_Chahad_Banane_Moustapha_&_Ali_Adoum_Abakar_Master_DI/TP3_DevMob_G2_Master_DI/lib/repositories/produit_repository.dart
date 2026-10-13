import '../core/database.dart';
import '../models/produit.dart';

class ProduitRepository {
  Future<List<Produit>> tous() async {
    final db = await AppDatabase.instance;
    final rows = await db.query('produit', orderBy: 'designProd');
    return rows.map(Produit.fromMap).toList();
  }

  Future<Produit?> parId(int id) async {
    final db = await AppDatabase.instance;
    final rows =
    await db.query('produit', where: 'idProd = ?', whereArgs: [id]);
    return rows.isEmpty ? null : Produit.fromMap(rows.first);
  }

  Future<void> creer(Produit p) async {
    final db = await AppDatabase.instance;
    await db.insert('produit', p.toMap());
  }

  Future<void> modifier(int id, Produit p) async {
    final db = await AppDatabase.instance;
    await db.update('produit', p.toMap(), where: 'idProd = ?', whereArgs: [id]);
  }
}