import '../core/database.dart';

class DashboardData {
  final int nbCommandes, nbClients, nbStockFaible;
  final double caTotal, panierMoyen;
  final List<(String, double)> parMois, parDepartement, topClients;
  final List<(String, int)> topProduits;
  final List<(String, int)> stockFaible;
  final int nbRequetes, dureeMs;

  DashboardData({
    required this.nbCommandes,
    required this.nbClients,
    required this.nbStockFaible,
    required this.caTotal,
    required this.panierMoyen,
    required this.parMois,
    required this.parDepartement,
    required this.topClients,
    required this.topProduits,
    required this.stockFaible,
    required this.nbRequetes,
    required this.dureeMs,
  });
}

/// Mêmes agrégations SQL que DashboardController (SUM / GROUP BY / jointures).
class DashboardRepository {
  // Montant d'une ligne = quantité × prix
  static const _m = 'lc.qteCmd * p.prixUProd';

  Future<DashboardData> charger() async {
    final t0 = DateTime.now();
    final db = await AppDatabase.instance;
    int nb = 0; // compteur de requêtes (« Page calculée avec N requêtes… »)

    Future<List<Map<String, Object?>>> q(String sql) {
      nb++;
      return db.rawQuery(sql);
    }

    final nbCmd =
    (await q('SELECT COUNT(*) AS n FROM commande')).first['n'] as int;
    final nbCli =
    (await q('SELECT COUNT(*) AS n FROM client')).first['n'] as int;
    final ca = ((await q('SELECT COALESCE(SUM($_m),0) AS t '
        'FROM ligneCommande lc JOIN produit p ON p.idProd = lc.idProd'))
        .first['t'] as num)
        .toDouble();

    final mois = await q('''
      SELECT strftime('%Y-%m', c.dateCmd) AS k, SUM($_m) AS v
      FROM ligneCommande lc
      JOIN produit p ON p.idProd = lc.idProd
      JOIN commande c ON c.idCmd = lc.idCmd
      GROUP BY k ORDER BY k''');

    final topProd = await q('''
      SELECT p.designProd AS k, SUM(lc.qteCmd) AS v
      FROM ligneCommande lc JOIN produit p ON p.idProd = lc.idProd
      GROUP BY p.idProd, p.designProd ORDER BY v DESC LIMIT 5''');

    final dep = await q('''
      SELECT d.nomDep AS k, SUM($_m) AS v
      FROM ligneCommande lc
      JOIN produit p ON p.idProd = lc.idProd
      JOIN commande c ON c.idCmd = lc.idCmd
      JOIN client cl ON cl.idCli = c.idCli
      JOIN region r ON r.idRegion = cl.idRegion
      JOIN departement d ON d.idDep = r.idDep
      GROUP BY d.idDep, d.nomDep ORDER BY v DESC''');

    final cli = await q('''
      SELECT cl.prenomCli || ' ' || cl.nomCli AS k, SUM($_m) AS v
      FROM ligneCommande lc
      JOIN produit p ON p.idProd = lc.idProd
      JOIN commande c ON c.idCmd = lc.idCmd
      JOIN client cl ON cl.idCli = c.idCli
      GROUP BY cl.idCli, cl.prenomCli, cl.nomCli ORDER BY v DESC LIMIT 5''');

    final faible = await q('SELECT designProd AS k, qteStock AS v FROM produit '
        'WHERE qteStock < 20 ORDER BY qteStock');

    List<(String, double)> d(List<Map<String, Object?>> r) =>
        r.map((e) => (e['k'] as String, (e['v'] as num).toDouble())).toList();

    return DashboardData(
      nbCommandes: nbCmd,
      nbClients: nbCli,
      nbStockFaible: faible.length,
      caTotal: ca,
      panierMoyen: nbCmd > 0 ? ca / nbCmd : 0,
      parMois: d(mois),
      parDepartement: d(dep),
      topClients: d(cli),
      topProduits: topProd
          .map((e) => (e['k'] as String, (e['v'] as num).toInt()))
          .toList(),
      stockFaible: faible
          .map((e) => (e['k'] as String, (e['v'] as num).toInt()))
          .toList(),
      nbRequetes: nb,
      dureeMs: DateTime.now().difference(t0).inMilliseconds,
    );
  }
}