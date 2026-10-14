import 'package:flutter/material.dart';
import '../api_service.dart';
import '../theme.dart';
import '../widgets/common.dart';

/// Détail d'une commande : liste des lignes, ajout et suppression d'un produit.
class CommandeDetailPage extends StatefulWidget {
  final Map<String, dynamic> order;
  const CommandeDetailPage({super.key, required this.order});
  @override
  State<CommandeDetailPage> createState() => _CommandeDetailPageState();
}

class _CommandeDetailPageState extends State<CommandeDetailPage> {
  late Future<List<dynamic>> future;
  int get idCmd => int.parse(widget.order['idCmd'].toString());

  @override
  void initState() {
    super.initState();
    future = api.lignes(idCmd);
  }

  void reload() => setState(() => future = api.lignes(idCmd));

  Future<void> addLine() async {
    List<dynamic> products;
    try {
      products = await api.produits();
    } catch (e) {
      if (mounted) showMessage(context, e.toString(), error: true);
      return;
    }
    if (!mounted) return;
    if (products.isEmpty) {
      showMessage(context, 'Aucun produit disponible.', error: true);
      return;
    }
    int? productId = int.tryParse(products.first['idProd'].toString());
    int qty = 1;

    await showDialog<void>(
      context: context,
      builder: (ctx) => StatefulBuilder(builder: (ctx, setLocal) {
        final selected = products.firstWhere(
            (p) => p['idProd'].toString() == productId.toString(),
            orElse: () => products.first);
        final subtotal = toNum(selected['prixUProd']) * qty;
        return AlertDialog(
          backgroundColor: Colors.white,
          surfaceTintColor: Colors.transparent,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: Row(children: [
            const IconBadge(
                icon: Icons.add_shopping_cart, color: AppColors.navy, size: 40),
            const SizedBox(width: 12),
            Expanded(
              child: Text('Ajouter à la commande #$idCmd',
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800)),
            ),
          ]),
          content: SizedBox(
            width: 380,
            child: Column(mainAxisSize: MainAxisSize.min, children: [
              const SizedBox(height: 6),
              DropdownButtonFormField<int>(
                value: productId,
                isExpanded: true,
                decoration: fieldDecoration('Produit', icon: Icons.inventory_2_outlined),
                items: [
                  for (final p in products)
                    DropdownMenuItem<int>(
                      value: int.parse(p['idProd'].toString()),
                      child: Text('${p['designProd']} (stock ${p['qteStock']})',
                          overflow: TextOverflow.ellipsis),
                    ),
                ],
                onChanged: (v) => setLocal(() => productId = v),
              ),
              const SizedBox(height: 16),
              Row(children: [
                const Text('Quantité', style: TextStyle(fontWeight: FontWeight.w600)),
                const Spacer(),
                IconButton.filledTonal(
                  onPressed: qty > 1 ? () => setLocal(() => qty--) : null,
                  icon: const Icon(Icons.remove),
                ),
                SizedBox(
                  width: 48,
                  child: Text('$qty',
                      textAlign: TextAlign.center,
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800)),
                ),
                IconButton.filledTonal(
                  onPressed: () => setLocal(() => qty++),
                  icon: const Icon(Icons.add),
                ),
              ]),
              const SizedBox(height: 14),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.bg,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(children: [
                  const Text('Sous-total', style: TextStyle(color: AppColors.muted)),
                  const Spacer(),
                  Text(money(subtotal),
                      style: const TextStyle(
                          fontWeight: FontWeight.w800, color: AppColors.navy)),
                ]),
              ),
            ]),
          ),
          actionsPadding: const EdgeInsets.fromLTRB(20, 0, 20, 16),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Annuler')),
            FilledButton(
              style: FilledButton.styleFrom(backgroundColor: AppColors.navy),
              onPressed: () async {
                if (productId == null) return;
                try {
                  await api.addLigne(idCmd, productId!, qty);
                  if (ctx.mounted) Navigator.pop(ctx);
                  if (mounted) {
                    showMessage(context, 'Produit ajouté à la commande.');
                    reload();
                  }
                } catch (e) {
                  if (mounted) showMessage(context, e.toString(), error: true);
                }
              },
              child: const Text('Ajouter'),
            ),
          ],
        );
      }),
    );
  }

  Future<void> deleteLine(Map<String, dynamic> l) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('Retirer ce produit ?',
            style: TextStyle(fontSize: 19, fontWeight: FontWeight.w800)),
        content: Text('« ${l['designProd']} » sera supprimé de la commande #$idCmd.'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx, false), child: const Text('Annuler')),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: AppColors.rose),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Supprimer'),
          ),
        ],
      ),
    );
    if (ok != true) return;
    try {
      await api.deleteLigne(idCmd, int.parse(l['idProd'].toString()));
      if (mounted) {
        showMessage(context, 'Produit supprimé de la commande.');
        reload();
      }
    } catch (e) {
      if (mounted) showMessage(context, e.toString(), error: true);
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(
          backgroundColor: Colors.white,
          surfaceTintColor: Colors.transparent,
          elevation: 0,
          scrolledUnderElevation: 0,
          iconTheme: const IconThemeData(color: AppColors.ink),
          titleTextStyle: const TextStyle(
              color: AppColors.ink, fontSize: 19, fontWeight: FontWeight.w800),
          title: Text('Commande #$idCmd'),
          bottom: const PreferredSize(
            preferredSize: Size.fromHeight(1),
            child: Divider(height: 1, color: AppColors.line),
          ),
        ),
        floatingActionButton: FloatingActionButton.extended(
          onPressed: addLine,
          backgroundColor: AppColors.navy,
          foregroundColor: Colors.white,
          icon: const Icon(Icons.add_shopping_cart),
          label: const Text('Ajouter un produit'),
        ),
        body: FutureBuilder<List<dynamic>>(
          future: future,
          builder: (c, s) {
            if (s.connectionState == ConnectionState.waiting) return const LoadingView();
            if (s.hasError) return ErrorPanel(error: s.error.toString(), retry: reload);
            final lines = s.data!.map((e) => Map<String, dynamic>.from(e)).toList();
            final total = lines.fold<num>(0, (sum, l) => sum + toNum(l['sousTotal']));
            final units = lines.fold<num>(0, (sum, l) => sum + toNum(l['qteCmd']));
            final client = widget.order['client'].toString();

            return PageList(children: [
              AppCard(
                child: Row(children: [
                  InitialsAvatar(client, size: 56),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Text(client,
                          style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w800)),
                      const SizedBox(height: 2),
                      Text('Passée le ${shortDate(widget.order['dateCmd'])}',
                          style: const TextStyle(color: AppColors.muted, fontSize: 13)),
                    ]),
                  ),
                  Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
                    const Text('Total', style: TextStyle(color: AppColors.muted, fontSize: 12.5)),
                    Text(money(total),
                        style: const TextStyle(
                            fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.navy)),
                    Text('${units.round()} unité(s)',
                        style: const TextStyle(color: AppColors.muted, fontSize: 12.5)),
                  ]),
                ]),
              ),
              const SizedBox(height: 18),
              const SectionTitle('Produits de la commande'),
              if (lines.isEmpty)
                const EmptyState(
                    icon: Icons.shopping_cart_outlined,
                    message: 'Cette commande ne contient encore aucun produit.')
              else
                ResponsiveWrap(
                  minItemWidth: 420,
                  children: [for (final l in lines) _lineCard(l)],
                ),
            ]);
          },
        ),
      );

  Widget _lineCard(Map<String, dynamic> l) {
    final name = l['designProd'].toString();
    return AppCard(
      child: Row(children: [
        IconBadge(icon: iconForProduct(name), color: colorFor(name), size: 48),
        const SizedBox(width: 14),
        Expanded(
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(name,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700)),
            const SizedBox(height: 3),
            Text('${toNum(l['qteCmd']).round()} × ${money(l['prixUProd'])}',
                style: const TextStyle(color: AppColors.muted, fontSize: 13)),
          ]),
        ),
        Text(money(l['sousTotal']),
            style: const TextStyle(fontWeight: FontWeight.w800, color: AppColors.navy)),
        IconButton(
          tooltip: 'Retirer de la commande',
          icon: const Icon(Icons.delete_outline, color: AppColors.rose),
          onPressed: () => deleteLine(l),
        ),
      ]),
    );
  }
}
