import 'package:flutter/material.dart';
import '../api_service.dart';
import '../theme.dart';
import '../widgets/common.dart';
import 'commande_detail_page.dart';

class CommandesPage extends StatefulWidget {
  const CommandesPage({super.key});
  @override
  State<CommandesPage> createState() => _CommandesPageState();
}

class _CommandesPageState extends State<CommandesPage> {
  late Future<List<dynamic>> future;
  String query = '';

  @override
  void initState() {
    super.initState();
    future = api.commandes();
  }

  void reload() => setState(() => future = api.commandes());

  Future<void> open(Map<String, dynamic> order) async {
    await Navigator.push(
        context, MaterialPageRoute(builder: (_) => CommandeDetailPage(order: order)));
    if (mounted) reload();
  }

  /// Crée une commande vide pour un client, puis ouvre son détail.
  Future<void> newOrder() async {
    List<dynamic> clients;
    try {
      clients = await api.clients();
    } catch (e) {
      if (mounted) showMessage(context, e.toString(), error: true);
      return;
    }
    if (!mounted) return;
    if (clients.isEmpty) {
      showMessage(context, 'Aucun client disponible.', error: true);
      return;
    }
    int? clientId = int.tryParse(clients.first['idCli'].toString());

    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setLocal) => AlertDialog(
          backgroundColor: Colors.white,
          surfaceTintColor: Colors.transparent,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: Row(children: [
            const IconBadge(
                icon: Icons.add_shopping_cart, color: AppColors.navy, size: 40),
            const SizedBox(width: 12),
            const Expanded(
              child: Text('Nouvelle commande',
                  style: TextStyle(fontSize: 19, fontWeight: FontWeight.w800)),
            ),
          ]),
          content: SizedBox(
            width: 380,
            child: DropdownButtonFormField<int>(
              value: clientId,
              isExpanded: true,
              decoration: fieldDecoration('Client', icon: Icons.person_outline),
              items: [
                for (final c in clients)
                  DropdownMenuItem<int>(
                    value: int.parse(c['idCli'].toString()),
                    child: Text('${c['prenomCli']} ${c['nomCli']}',
                        overflow: TextOverflow.ellipsis),
                  ),
              ],
              onChanged: (v) => setLocal(() => clientId = v),
            ),
          ),
          actionsPadding: const EdgeInsets.fromLTRB(20, 0, 20, 16),
          actions: [
            TextButton(
                onPressed: () => Navigator.pop(ctx, false), child: const Text('Annuler')),
            FilledButton(
              style: FilledButton.styleFrom(backgroundColor: AppColors.navy),
              onPressed: () => Navigator.pop(ctx, true),
              child: const Text('Créer la commande'),
            ),
          ],
        ),
      ),
    );

    if (ok == true && clientId != null) {
      try {
        final id = await api.addCommande(clientId!);
        final client = clients.firstWhere((c) => c['idCli'].toString() == clientId.toString());
        if (!mounted) return;
        await open({
          'idCmd': id,
          'client': '${client['prenomCli']} ${client['nomCli']}',
          'dateCmd': DateTime.now().toString(),
        });
      } catch (e) {
        if (mounted) showMessage(context, e.toString(), error: true);
      }
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        backgroundColor: Colors.transparent,
        floatingActionButton: FloatingActionButton.extended(
          onPressed: newOrder,
          backgroundColor: AppColors.navy,
          foregroundColor: Colors.white,
          icon: const Icon(Icons.add),
          label: const Text('Nouvelle commande'),
        ),
        body: FutureBuilder<List<dynamic>>(
          future: future,
          builder: (c, s) {
            if (s.connectionState == ConnectionState.waiting) return const LoadingView();
            if (s.hasError) return ErrorPanel(error: s.error.toString(), retry: reload);

            final all = s.data!.map((e) => Map<String, dynamic>.from(e)).toList();
            final q = query.trim().toLowerCase();
            final shown = all.where((o) {
              if (q.isEmpty) return true;
              return o['client'].toString().toLowerCase().contains(q) ||
                  o['idCmd'].toString() == q.replaceAll('#', '');
            }).toList();
            final total = all.fold<num>(0, (sum, o) => sum + toNum(o['montant']));

            return RefreshIndicator(
              onRefresh: () async {
                reload();
                await future;
              },
              child: PageList(children: [
                PageHeader(
                  title: 'Commandes',
                  subtitle: '${all.length} commande(s) · ${money(total)} au total',
                  actions: [
                    IconButton(
                        tooltip: 'Actualiser',
                        onPressed: reload,
                        icon: const Icon(Icons.refresh, color: AppColors.navy)),
                  ],
                ),
                SearchField(
                    hint: 'Rechercher par client ou n° de commande…',
                    onChanged: (v) => setState(() => query = v)),
                const SizedBox(height: 16),
                if (shown.isEmpty)
                  const EmptyState(icon: Icons.search_off, message: 'Aucune commande trouvée.')
                else
                  ResponsiveWrap(
                    minItemWidth: 400,
                    children: [for (final o in shown) _orderCard(o)],
                  ),
              ]),
            );
          },
        ),
      );

  Widget _orderCard(Map<String, dynamic> o) {
    final nb = toNum(o['nbProduits']).round();
    return AppCard(
      onTap: () => open(o),
      child: Row(children: [
        InitialsAvatar(o['client'].toString(), size: 48),
        const SizedBox(width: 14),
        Expanded(
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(o['client'].toString(),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(fontSize: 15.5, fontWeight: FontWeight.w700)),
            const SizedBox(height: 2),
            Text('Commande #${o['idCmd']} · ${shortDate(o['dateCmd'])}',
                style: const TextStyle(color: AppColors.muted, fontSize: 13)),
            const SizedBox(height: 8),
            Pill(nb == 0 ? 'Aucun produit' : '$nb produit(s)',
                color: nb == 0 ? AppColors.amber : AppColors.teal,
                icon: Icons.shopping_bag_outlined),
          ]),
        ),
        const SizedBox(width: 8),
        Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
          Text(money(o['montant']),
              style: const TextStyle(
                  fontSize: 14.5, fontWeight: FontWeight.w800, color: AppColors.navy)),
          const SizedBox(height: 10),
          const Icon(Icons.chevron_right, color: AppColors.muted),
        ]),
      ]),
    );
  }
}
