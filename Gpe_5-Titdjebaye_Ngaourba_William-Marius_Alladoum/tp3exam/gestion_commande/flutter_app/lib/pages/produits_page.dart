import 'package:flutter/material.dart';
import '../api_service.dart';
import '../theme.dart';
import '../widgets/common.dart';

const int seuilStockFaible = 10;

class ProduitsPage extends StatefulWidget {
  const ProduitsPage({super.key});
  @override
  State<ProduitsPage> createState() => _ProduitsPageState();
}

class _ProduitsPageState extends State<ProduitsPage> {
  late Future<List<dynamic>> future;
  String query = '';
  int filter = 0; // 0 = tous, 1 = stock faible, 2 = rupture

  @override
  void initState() {
    super.initState();
    future = api.produits();
  }

  void reload() => setState(() => future = api.produits());

  Future<void> edit([Map<String, dynamic>? p]) async {
    final name = TextEditingController(text: p?['designProd']?.toString() ?? '');
    final stock = TextEditingController(text: p?['qteStock']?.toString() ?? '0');
    final price = TextEditingController(
        text: p == null ? '0' : toNum(p['prixUProd']).round().toString());
    final form = GlobalKey<FormState>();

    final result = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(children: [
          IconBadge(
              icon: p == null ? Icons.add_box_outlined : Icons.edit_outlined,
              color: AppColors.navy,
              size: 40),
          const SizedBox(width: 12),
          Expanded(
            child: Text(p == null ? 'Nouveau produit' : 'Modifier le produit',
                style: const TextStyle(fontSize: 19, fontWeight: FontWeight.w800)),
          ),
        ]),
        content: SizedBox(
          width: 380,
          child: Form(
            key: form,
            child: SingleChildScrollView(
              child: Column(mainAxisSize: MainAxisSize.min, children: [
                const SizedBox(height: 8),
                TextFormField(
                  controller: name,
                  decoration: fieldDecoration('Désignation', icon: Icons.label_outline),
                  validator: (v) =>
                      v == null || v.trim().isEmpty ? 'Champ obligatoire' : null,
                ),
                const SizedBox(height: 14),
                TextFormField(
                  controller: stock,
                  keyboardType: TextInputType.number,
                  decoration:
                      fieldDecoration('Quantité en stock', icon: Icons.inventory_2_outlined),
                  validator: (v) {
                    final n = int.tryParse(v ?? '');
                    return n == null || n < 0 ? 'Stock invalide' : null;
                  },
                ),
                const SizedBox(height: 14),
                TextFormField(
                  controller: price,
                  keyboardType: const TextInputType.numberWithOptions(decimal: true),
                  decoration:
                      fieldDecoration('Prix unitaire (FCFA)', icon: Icons.payments_outlined),
                  validator: (v) {
                    final n = double.tryParse(v ?? '');
                    return n == null || n < 0 ? 'Prix invalide' : null;
                  },
                ),
              ]),
            ),
          ),
        ),
        actionsPadding: const EdgeInsets.fromLTRB(20, 0, 20, 16),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx, false), child: const Text('Annuler')),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: AppColors.navy),
            onPressed: () {
              if (form.currentState!.validate()) Navigator.pop(ctx, true);
            },
            child: const Text('Enregistrer'),
          ),
        ],
      ),
    );

    if (result == true) {
      try {
        await api.saveProduit({
          'idProd': p?['idProd'],
          'designProd': name.text.trim(),
          'qteStock': int.parse(stock.text),
          'prixUProd': double.parse(price.text),
        });
        if (mounted) {
          showMessage(context, 'Produit enregistré.');
          reload();
        }
      } catch (e) {
        if (mounted) showMessage(context, e.toString(), error: true);
      }
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        backgroundColor: Colors.transparent,
        floatingActionButton: FloatingActionButton.extended(
          onPressed: () => edit(),
          backgroundColor: AppColors.navy,
          foregroundColor: Colors.white,
          icon: const Icon(Icons.add),
          label: const Text('Ajouter un produit'),
        ),
        body: FutureBuilder<List<dynamic>>(
          future: future,
          builder: (c, s) {
            if (s.connectionState == ConnectionState.waiting) return const LoadingView();
            if (s.hasError) return ErrorPanel(error: s.error.toString(), retry: reload);

            final all = s.data!.map((e) => Map<String, dynamic>.from(e)).toList();
            final faible = all
                .where((p) => toNum(p['qteStock']) > 0 && toNum(p['qteStock']) <= seuilStockFaible)
                .length;
            final rupture = all.where((p) => toNum(p['qteStock']) <= 0).length;

            final q = query.trim().toLowerCase();
            final shown = all.where((p) {
              final stock = toNum(p['qteStock']);
              if (filter == 1 && !(stock > 0 && stock <= seuilStockFaible)) return false;
              if (filter == 2 && stock > 0) return false;
              return q.isEmpty || p['designProd'].toString().toLowerCase().contains(q);
            }).toList();

            return RefreshIndicator(
              onRefresh: () async {
                reload();
                await future;
              },
              child: PageList(children: [
                PageHeader(
                  title: 'Produits',
                  subtitle: '${all.length} référence(s) au catalogue',
                  actions: [
                    IconButton(
                        tooltip: 'Actualiser',
                        onPressed: reload,
                        icon: const Icon(Icons.refresh, color: AppColors.navy)),
                  ],
                ),
                SearchField(
                    hint: 'Rechercher un produit…',
                    onChanged: (v) => setState(() => query = v)),
                const SizedBox(height: 12),
                Wrap(spacing: 8, runSpacing: 8, children: [
                  _chip('Tous (${all.length})', 0),
                  _chip('Stock faible ($faible)', 1),
                  _chip('Rupture ($rupture)', 2),
                ]),
                const SizedBox(height: 16),
                if (shown.isEmpty)
                  const EmptyState(icon: Icons.search_off, message: 'Aucun produit trouvé.')
                else
                  ResponsiveWrap(
                    minItemWidth: 330,
                    children: [for (final p in shown) _productCard(p)],
                  ),
              ]),
            );
          },
        ),
      );

  Widget _chip(String label, int value) => ChoiceChip(
        label: Text(label),
        selected: filter == value,
        showCheckmark: false,
        selectedColor: AppColors.navy.withOpacity(0.14),
        backgroundColor: Colors.white,
        side: BorderSide(color: filter == value ? AppColors.navy : AppColors.line),
        labelStyle: TextStyle(
            color: filter == value ? AppColors.navy : AppColors.muted,
            fontWeight: FontWeight.w600),
        onSelected: (_) => setState(() => filter = value),
      );

  Widget _productCard(Map<String, dynamic> p) {
    final name = p['designProd'].toString();
    final stock = toNum(p['qteStock']).round();
    final Pill pill;
    if (stock <= 0) {
      pill = const Pill('Rupture de stock', color: AppColors.rose, icon: Icons.error_outline);
    } else if (stock <= seuilStockFaible) {
      pill = Pill('Stock faible : $stock',
          color: AppColors.amber, icon: Icons.warning_amber_rounded);
    } else {
      pill = Pill('En stock : $stock',
          color: AppColors.teal, icon: Icons.check_circle_outline);
    }
    return AppCard(
      onTap: () => edit(p),
      child: Row(children: [
        IconBadge(icon: iconForProduct(name), color: colorFor(name), size: 52),
        const SizedBox(width: 14),
        Expanded(
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(name,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(fontSize: 15.5, fontWeight: FontWeight.w700)),
            const SizedBox(height: 3),
            Text(money(p['prixUProd']),
                style: const TextStyle(
                    fontSize: 15, fontWeight: FontWeight.w800, color: AppColors.navy)),
            const SizedBox(height: 7),
            pill,
          ]),
        ),
        const Icon(Icons.edit_outlined, color: AppColors.muted, size: 20),
      ]),
    );
  }
}
