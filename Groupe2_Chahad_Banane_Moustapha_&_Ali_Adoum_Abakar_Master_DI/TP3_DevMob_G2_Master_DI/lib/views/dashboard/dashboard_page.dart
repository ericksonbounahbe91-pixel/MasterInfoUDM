import 'package:fl_chart/fl_chart.dart';
import 'package:flutter/material.dart';
import '../../core/format.dart';
import '../../core/theme.dart';
import '../../repositories/dashboard_repository.dart';
import '../../widgets/app_shell.dart';
import '../../widgets/bs_card.dart';
import '../../widgets/stat_card.dart';

class DashboardPage extends StatefulWidget {
  const DashboardPage({super.key});
  @override
  State<DashboardPage> createState() => _DashboardPageState();
}

class _DashboardPageState extends State<DashboardPage> {
  // Chargé une seule fois (évite de relancer 7 requêtes à chaque rotation d'écran)
  late final Future<DashboardData> _future = DashboardRepository().charger();

  // Couleurs par défaut de Chart.js, pour garder le même donut
  static const _palette = [
    Color(0xFFFF6384),
    Color(0xFF36A2EB),
    Color(0xFFFFCE56),
    Color(0xFF4BC0C0),
    Color(0xFF9966FF),
    Color(0xFFFF9F40),
  ];

  Widget _vide() => const Padding(
    padding: EdgeInsets.all(24),
    child: Center(
        child: Text('Aucune donnée.', style: TextStyle(color: Bs.muted))),
  );

  /// col-lg-X / col-lg-Y : côte à côte sur grand écran, empilés sinon
  Widget _duo(bool wide, int fa, int fb, Widget a, Widget b) => wide
      ? Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
    Expanded(flex: fa, child: a),
    const SizedBox(width: 24),
    Expanded(flex: fb, child: b),
  ])
      : Column(children: [a, const SizedBox(height: 24), b]);

  Widget _courbe(DashboardData d) {
    if (d.parMois.isEmpty) return _vide();
    final n = d.parMois.length;
    return SizedBox(
      height: 220,
      child: LineChart(LineChartData(
        minX: 0,
        maxX: (n - 1).clamp(1, 100000).toDouble(),
        gridData: const FlGridData(show: true),
        borderData: FlBorderData(show: false),
        titlesData: FlTitlesData(
          rightTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
          topTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
          bottomTitles: AxisTitles(
            sideTitles: SideTitles(
              showTitles: true,
              interval: 1,
              getTitlesWidget: (v, _) {
                final i = v.toInt();
                if (i < 0 || i >= n || v != i.toDouble()) {
                  return const SizedBox();
                }
                return Padding(
                  padding: const EdgeInsets.only(top: 6),
                  child: Text(d.parMois[i].$1,
                      style: const TextStyle(fontSize: 10)),
                );
              },
            ),
          ),
        ),
        lineBarsData: [
          LineChartBarData(
            spots: [
              for (var i = 0; i < n; i++) FlSpot(i.toDouble(), d.parMois[i].$2),
            ],
            isCurved: true,
            curveSmoothness: 0.3,
            color: Bs.primary, // borderColor #0d6efd
            barWidth: 2,
            dotData: const FlDotData(show: true),
          ),
        ],
      )),
    );
  }

  Widget _donut(DashboardData d) {
    if (d.parDepartement.isEmpty) return _vide();
    return SizedBox(
      height: 220,
      child: PieChart(PieChartData(
        centerSpaceRadius: 50,
        sections: [
          for (var i = 0; i < d.parDepartement.length; i++)
            PieChartSectionData(
              value: d.parDepartement[i].$2,
              title: d.parDepartement[i].$1,
              radius: 40,
              titleStyle: const TextStyle(fontSize: 11, color: Colors.white),
              color: _palette[i % _palette.length],
            ),
        ],
      )),
    );
  }

  Widget _barres(DashboardData d) {
    if (d.topProduits.isEmpty) return _vide();
    return SizedBox(
      height: 240,
      child: BarChart(BarChartData(
        alignment: BarChartAlignment.spaceAround,
        borderData: FlBorderData(show: false),
        titlesData: FlTitlesData(
          rightTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
          topTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
          bottomTitles: AxisTitles(
            sideTitles: SideTitles(
              showTitles: true,
              reservedSize: 44,
              getTitlesWidget: (v, _) {
                final i = v.toInt();
                if (i < 0 || i >= d.topProduits.length) return const SizedBox();
                return Padding(
                  padding: const EdgeInsets.only(top: 6),
                  child: SizedBox(
                    width: 64,
                    child: Text(d.topProduits[i].$1,
                        textAlign: TextAlign.center,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(fontSize: 10)),
                  ),
                );
              },
            ),
          ),
        ),
        barGroups: [
          for (var i = 0; i < d.topProduits.length; i++)
            BarChartGroupData(x: i, barRods: [
              BarChartRodData(
                toY: d.topProduits[i].$2.toDouble(),
                color: Bs.success, // backgroundColor #198754
                width: 22,
              ),
            ]),
        ],
      )),
    );
  }

  @override
  Widget build(BuildContext context) {
    return AppShell(
      title: 'Tableau de bord',
      child: FutureBuilder<DashboardData>(
        future: _future,
        builder: (context, snap) {
          if (snap.hasError) {
            return Text('Erreur : ${snap.error}',
                style: const TextStyle(color: Bs.danger));
          }
          if (!snap.hasData) {
            return const Padding(
                padding: EdgeInsets.all(48),
                child: Center(child: CircularProgressIndicator()));
          }
          final d = snap.data!;
          final wide = MediaQuery.sizeOf(context).width >= 992;

          final topClients = BsCard(
            header: 'Top 5 clients (CA)',
            padding: EdgeInsets.zero,
            child: d.topClients.isEmpty
                ? _vide()
                : Column(children: [
              for (var i = 0; i < d.topClients.length; i++)
                ListTile(
                  dense: true,
                  leading: Text('${i + 1}',
                      style: const TextStyle(color: Bs.muted)),
                  title: Text(d.topClients[i].$1),
                  trailing: Text(fcfa(d.topClients[i].$2)),
                ),
            ]),
          );

          final stockFaible = BsCard(
            header: 'Produits à réapprovisionner (stock < 20)',
            padding: EdgeInsets.zero,
            child: d.stockFaible.isEmpty
                ? const Padding(
                padding: EdgeInsets.all(16),
                child: Center(
                    child: Text('Aucun produit en stock faible.',
                        style: TextStyle(color: Bs.muted))))
                : Column(children: [
              for (final p in d.stockFaible.take(5))
                ListTile(
                  title: Text(p.$1),
                  trailing: Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                        color: Bs.danger,
                        borderRadius: BorderRadius.circular(4)),
                    child: Text('${p.$2}',
                        style: const TextStyle(
                            color: Colors.white, fontSize: 12)),
                  ),
                ),
            ]),
          );

          return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            const Text('Tableau de bord',
                style: TextStyle(fontSize: 28, fontWeight: FontWeight.w500)),
            const SizedBox(height: 16),

            // 4 cartes : col-6 col-lg-3 => 2 par ligne (mobile), 4 (large)
            LayoutBuilder(builder: (context, c) {
              final perRow = wide ? 4 : 2;
              final w = (c.maxWidth - 16 * (perRow - 1)) / perRow;
              Widget cell(Widget child) => SizedBox(width: w, child: child);
              return Wrap(spacing: 16, runSpacing: 16, children: [
                cell(StatCard(
                    label: 'Commandes',
                    value: '${d.nbCommandes}',
                    color: Bs.primary)),
                cell(StatCard(
                    label: "Chiffre d'affaires",
                    value: nombre(d.caTotal),
                    unit: 'FCFA',
                    color: Bs.success)),
                cell(StatCard(
                    label: 'Panier moyen',
                    value: nombre(d.panierMoyen),
                    unit: 'FCFA',
                    color: Bs.info)),
                cell(StatCard(
                    label: 'Produits à stock faible',
                    value: '${d.nbStockFaible}',
                    color: Bs.danger)),
              ]);
            }),
            const SizedBox(height: 24),

            _duo(
              wide,
              8,
              4,
              BsCard(
                  header: "Chiffre d'affaires par mois (FCFA)",
                  child: _courbe(d)),
              BsCard(header: 'CA par région', child: _donut(d)),
            ),
            const SizedBox(height: 24),

            _duo(
              wide,
              1,
              1,
              BsCard(
                  header: 'Top 5 produits vendus (quantités)',
                  child: _barres(d)),
              topClients,
            ),
            const SizedBox(height: 24),

            stockFaible,
            const SizedBox(height: 8),

            // Mesure de performance (conservée du TP)
            Align(
              alignment: Alignment.centerRight,
              child: Text(
                'Page calculée avec ${d.nbRequetes} requêtes SQL en ${d.dureeMs} ms.',
                style: const TextStyle(color: Bs.muted, fontSize: 12),
              ),
            ),
          ]);
        },
      ),
    );
  }
}