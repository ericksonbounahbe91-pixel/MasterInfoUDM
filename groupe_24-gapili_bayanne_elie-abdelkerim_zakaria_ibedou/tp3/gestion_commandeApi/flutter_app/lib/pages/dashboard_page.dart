import 'package:flutter/material.dart';
import '../api_service.dart';
import '../theme.dart';
import '../widgets/common.dart';

class DashboardPage extends StatefulWidget {
  const DashboardPage({super.key});
  @override
  State<DashboardPage> createState() => _DashboardPageState();
}

class _DashboardPageState extends State<DashboardPage> {
  late Future<Map<String, dynamic>> future;

  @override
  void initState() {
    super.initState();
    future = api.stats();
  }

  void reload() => setState(() => future = api.stats());

  List<dynamic> _list(Map<String, dynamic> d, String key) =>
      d[key] is List ? List<dynamic>.from(d[key] as List) : <dynamic>[];

  @override
  Widget build(BuildContext context) => FutureBuilder<Map<String, dynamic>>(
        future: future,
        builder: (context, s) {
          if (s.connectionState == ConnectionState.waiting) return const LoadingView();
          if (s.hasError) return ErrorPanel(error: s.error.toString(), retry: reload);
          final d = s.data!;
          final m = Map<String, dynamic>.from(d['summary']);
          final top = _list(d, 'topProduits');
          final mois = _list(d, 'caParMois');
          final clients = _list(d, 'topClients');
          final stock = _list(d, 'stockFaible');

          return RefreshIndicator(
            onRefresh: () async {
              reload();
              await future;
            },
            child: PageList(children: [
              PageHeader(
                title: 'Tableau de bord',
                subtitle: 'Vue d’ensemble de l’activité commerciale',
                actions: [
                  IconButton(
                      tooltip: 'Actualiser',
                      onPressed: reload,
                      icon: const Icon(Icons.refresh, color: AppColors.navy)),
                ],
              ),
              _hero(m, mois),
              const SizedBox(height: 14),
              ResponsiveWrap(
                minItemWidth: 140,
                maxColumns: 4,
                children: [
                  _StatTile(
                      label: 'Commandes',
                      value: '${m['totalCommandes']}',
                      icon: Icons.receipt_long,
                      color: AppColors.navy),
                  _StatTile(
                      label: 'Clients',
                      value: '${m['totalClients']}',
                      icon: Icons.people,
                      color: AppColors.teal),
                  _StatTile(
                      label: 'Produits',
                      value: '${m['totalProduits']}',
                      icon: Icons.inventory_2,
                      color: AppColors.amber),
                  _StatTile(
                      label: 'Quantités vendues',
                      value: '${m['quantiteVendue']}',
                      icon: Icons.shopping_bag,
                      color: const Color(0xFF7C5CBF)),
                ],
              ),
              const SizedBox(height: 18),
              ResponsiveWrap(
                minItemWidth: 440,
                maxColumns: 2,
                spacing: 14,
                children: [
                  _topProduits(top),
                  _topClients(clients),
                ],
              ),
              if (stock.isNotEmpty) ...[
                const SizedBox(height: 14),
                _stockFaible(stock),
              ],
              const SizedBox(height: 14),
              const Padding(
                padding: EdgeInsets.symmetric(horizontal: 4),
                child: Text(
                  'Analyse : le chiffre d’affaires est calculé à partir de la somme '
                  'quantité × prix unitaire pour chaque ligne de commande. Le top '
                  'produits aide à identifier les articles les plus demandés.',
                  style: TextStyle(color: AppColors.muted, fontSize: 12.5, height: 1.45),
                ),
              ),
            ]),
          );
        },
      );

  // --------------------------------------------------------------- hero
  Widget _hero(Map<String, dynamic> m, List<dynamic> mois) {
    final ca = toNum(m['chiffreAffaires']);
    final left = Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      mainAxisSize: MainAxisSize.min,
      children: [
        Text('Chiffre d’affaires',
            style: TextStyle(color: Colors.white.withOpacity(0.75), fontSize: 14)),
        const SizedBox(height: 6),
        FittedBox(
          fit: BoxFit.scaleDown,
          alignment: Alignment.centerLeft,
          child: Text(money(ca),
              style: const TextStyle(
                  color: Colors.white,
                  fontSize: 36,
                  fontWeight: FontWeight.w800,
                  letterSpacing: -0.8)),
        ),
        const SizedBox(height: 8),
        Text('${m['totalCommandes']} commande(s) · ${m['quantiteVendue']} unité(s) vendues',
            style: TextStyle(color: Colors.white.withOpacity(0.8), fontSize: 13.5)),
      ],
    );
    final chart = _MonthBars(data: mois);

    return Container(
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(22),
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [AppColors.navyDark, AppColors.navy, Color(0xFF0E6F7E)],
        ),
        boxShadow: const [
          BoxShadow(color: Color(0x33123B6D), blurRadius: 22, offset: Offset(0, 10)),
        ],
      ),
      child: LayoutBuilder(builder: (context, box) {
        if (box.maxWidth >= 640) {
          return Row(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              Expanded(flex: 5, child: left),
              const SizedBox(width: 24),
              Expanded(flex: 6, child: chart),
            ],
          );
        }
        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [left, const SizedBox(height: 18), chart],
        );
      }),
    );
  }

  // --------------------------------------------------------- top produits
  Widget _topProduits(List<dynamic> top) {
    final maxQ = top.isEmpty
        ? 1
        : top.map((e) => toNum(e['quantite'])).reduce((a, b) => a > b ? a : b);
    return AppCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(children: [
            Icon(Icons.trending_up, color: AppColors.teal, size: 20),
            SizedBox(width: 8),
            Text('Top produits vendus',
                style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w800)),
          ]),
          const SizedBox(height: 14),
          if (top.isEmpty)
            const EmptyState(icon: Icons.bar_chart, message: 'Aucune vente enregistrée.')
          else
            for (var i = 0; i < top.length; i++)
              Padding(
                padding: const EdgeInsets.only(bottom: 14),
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Row(children: [
                    Container(
                      width: 22,
                      height: 22,
                      alignment: Alignment.center,
                      decoration: BoxDecoration(
                        color: i == 0 ? AppColors.amber : AppColors.bg,
                        shape: BoxShape.circle,
                      ),
                      child: Text('${i + 1}',
                          style: TextStyle(
                              fontSize: 11.5,
                              fontWeight: FontWeight.w800,
                              color: i == 0 ? Colors.white : AppColors.muted)),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(top[i]['designProd'].toString(),
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(fontWeight: FontWeight.w600)),
                    ),
                    Text('${toNum(top[i]['quantite']).round()} unité(s)',
                        style: const TextStyle(color: AppColors.muted, fontSize: 13)),
                  ]),
                  const SizedBox(height: 7),
                  ClipRRect(
                    borderRadius: BorderRadius.circular(6),
                    child: LinearProgressIndicator(
                      value: (toNum(top[i]['quantite']) / maxQ).clamp(0.02, 1.0).toDouble(),
                      minHeight: 8,
                      backgroundColor: AppColors.bg,
                      color: i == 0 ? AppColors.teal : AppColors.navy.withOpacity(0.55),
                    ),
                  ),
                ]),
              ),
        ],
      ),
    );
  }

  // --------------------------------------------------------- top clients
  Widget _topClients(List<dynamic> clients) => AppCard(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(children: [
              Icon(Icons.workspace_premium, color: AppColors.amber, size: 20),
              SizedBox(width: 8),
              Text('Meilleurs clients',
                  style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w800)),
            ]),
            const SizedBox(height: 12),
            if (clients.isEmpty)
              const EmptyState(icon: Icons.people_outline, message: 'Aucun achat enregistré.')
            else
              for (final c in clients)
                Padding(
                  padding: const EdgeInsets.symmetric(vertical: 6),
                  child: Row(children: [
                    InitialsAvatar(c['client'].toString(), size: 38),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(c['client'].toString(),
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(fontWeight: FontWeight.w600)),
                    ),
                    Text(money(c['montant']),
                        style: const TextStyle(
                            fontWeight: FontWeight.w800, color: AppColors.navy, fontSize: 13.5)),
                  ]),
                ),
          ],
        ),
      );

  // --------------------------------------------------------- stock faible
  Widget _stockFaible(List<dynamic> stock) => AppCard(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(children: [
              Icon(Icons.warning_amber_rounded, color: AppColors.amber, size: 22),
              SizedBox(width: 8),
              Text('Stock à surveiller',
                  style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w800)),
            ]),
            const SizedBox(height: 12),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                for (final p in stock)
                  Pill(
                    '${p['designProd']} : ${toNum(p['qteStock']) <= 0 ? 'rupture' : p['qteStock']}',
                    color: toNum(p['qteStock']) <= 0 ? AppColors.rose : AppColors.amber,
                  ),
              ],
            ),
          ],
        ),
      );
}

class _StatTile extends StatelessWidget {
  final String label, value;
  final IconData icon;
  final Color color;
  const _StatTile(
      {required this.label, required this.value, required this.icon, required this.color});

  @override
  Widget build(BuildContext context) => AppCard(
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          IconBadge(icon: icon, color: color, size: 42),
          const SizedBox(height: 14),
          Text(value,
              style: const TextStyle(
                  fontSize: 28, fontWeight: FontWeight.w800, color: AppColors.ink, height: 1)),
          const SizedBox(height: 4),
          Text(label, style: const TextStyle(color: AppColors.muted, fontSize: 13.5)),
        ]),
      );
}

/// Histogramme du chiffre d'affaires mensuel (sans dépendance externe).
class _MonthBars extends StatelessWidget {
  final List<dynamic> data;
  const _MonthBars({required this.data});

  @override
  Widget build(BuildContext context) {
    if (data.isEmpty) {
      return Text('Pas encore de données mensuelles.',
          style: TextStyle(color: Colors.white.withOpacity(0.7)));
    }
    final values = data.map((e) => toNum(e['montant'])).toList();
    final maxV = values.reduce((a, b) => a > b ? a : b);
    const barMax = 84.0;
    return SizedBox(
      height: 150,
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          for (var i = 0; i < data.length; i++)
            Expanded(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 4),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    Text(compact(values[i]),
                        style: TextStyle(
                            color: Colors.white.withOpacity(0.85),
                            fontSize: 11,
                            fontWeight: FontWeight.w600)),
                    const SizedBox(height: 4),
                    Container(
                      height: maxV == 0 ? 4.0 : (values[i] / maxV * barMax).clamp(4.0, barMax).toDouble(),
                      decoration: BoxDecoration(
                        color: i == data.length - 1
                            ? AppColors.amber
                            : Colors.white.withOpacity(0.35),
                        borderRadius: BorderRadius.circular(6),
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(moisCourt(data[i]['mois']),
                        style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 11.5)),
                  ],
                ),
              ),
            ),
        ],
      ),
    );
  }
}
