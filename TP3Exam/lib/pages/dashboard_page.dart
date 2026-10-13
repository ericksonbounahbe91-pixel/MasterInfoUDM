import 'package:flutter/material.dart';
import '../api.dart';
import '../utils.dart';

class DashboardPage extends StatefulWidget {
  const DashboardPage({super.key});

  @override
  State<DashboardPage> createState() => _DashboardPageState();
}

class _DashboardPageState extends State<DashboardPage> {
  late Future<Map<String, dynamic>> _f = _load();

  Future<Map<String, dynamic>> _load() async =>
      Map<String, dynamic>.from(await Api.get('/dashboard') as Map);

  void _reload() => setState(() {
        _f = _load();
      });

  Future<void> _refresh() async {
    _reload();
    try {
      await _f;
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Tableau de bord')),
      body: Chargement<Map<String, dynamic>>(
        future: _f,
        onRetry: _reload,
        builder: (d) {
          final k = Map<String, dynamic>.from(d['kpi'] as Map);
          final stock = d['stock_faible'] as List;
          return RefreshIndicator(
            onRefresh: _refresh,
            child: ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.all(12),
              children: [
                GridView.count(
                  crossAxisCount: 2,
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  childAspectRatio: 2.0,
                  mainAxisSpacing: 8,
                  crossAxisSpacing: 8,
                  children: [
                    _Kpi("Chiffre d'affaires", fcfa(k['ca'])),
                    _Kpi('Commandes', '${k['commandes']}'),
                    _Kpi('Panier moyen', fcfa(k['panier_moyen'])),
                    _Kpi('Unités vendues', '${k['unites']}'),
                  ],
                ),
                const SizedBox(height: 12),
                Carte('Top 5 produits (CA)', BarList(d['top_produits'] as List)),
                Carte('CA par région', BarList(d['par_region'] as List)),
                Carte('Évolution mensuelle du CA', BarList(d['par_mois'] as List)),
                Carte(
                  'Alertes de stock faible (moins de 10)',
                  stock.isEmpty
                      ? const Text('Aucun produit en alerte.')
                      : Column(
                          children: [
                            for (final p in stock)
                              ListTile(
                                dense: true,
                                contentPadding: EdgeInsets.zero,
                                title: Text('${p['designProd']}'),
                                trailing: Text('${p['qteStock']}',
                                    style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.red)),
                              ),
                          ],
                        ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}

class _Kpi extends StatelessWidget {
  final String titre;
  final String valeur;
  const _Kpi(this.titre, this.valeur);

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(titre, style: const TextStyle(fontSize: 12, color: Colors.grey)),
            const SizedBox(height: 4),
            FittedBox(
              fit: BoxFit.scaleDown,
              child: Text(valeur, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }
}
