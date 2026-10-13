import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../core/format.dart';
import '../../models/produit.dart';
import '../../repositories/produit_repository.dart';
import '../../widgets/app_shell.dart';
import '../../widgets/bs_button.dart';
import '../../widgets/bs_card.dart';
import '../../widgets/bs_table.dart';
import '../../widgets/page_header.dart';

class ProduitsPage extends StatefulWidget {
  const ProduitsPage({super.key});
  @override
  State<ProduitsPage> createState() => _ProduitsPageState();
}

class _ProduitsPageState extends State<ProduitsPage> {
  final _repo = ProduitRepository();
  late Future<List<Produit>> _future = _repo.tous();

  /// Ouvre un écran puis recharge la liste au retour.
  Future<void> _ouvrir(String route) async {
    await context.push(route);
    if (mounted) setState(() => _future = _repo.tous());
  }

  @override
  Widget build(BuildContext context) {
    return AppShell(
      title: 'Produits',
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        PageHeader(
          title: 'Liste des produits',
          trailing: BsButton('+ Ajouter un produit',
              onPressed: () => _ouvrir('/produits/create')),
        ),
        BsCard(
          padding: EdgeInsets.zero,
          child: FutureBuilder<List<Produit>>(
            future: _future,
            builder: (context, snap) {
              if (!snap.hasData) {
                return const Padding(
                    padding: EdgeInsets.all(24),
                    child: Center(child: CircularProgressIndicator()));
              }
              return BsTable(
                emptyText: 'Aucun produit.',
                columns: const [
                  DataColumn(label: Text('#')),
                  DataColumn(label: Text('Désignation')),
                  DataColumn(label: Text('Prix unitaire')),
                  DataColumn(label: Text('Stock')),
                  DataColumn(label: Text('Action')),
                ],
                rows: [
                  for (final p in snap.data!)
                    [
                      DataCell(Text('${p.id}')),
                      DataCell(Text(p.design)),
                      DataCell(Text(fcfa(p.prix))),
                      DataCell(Text('${p.stock}')),
                      DataCell(BsButton('Modifier',
                          small: true,
                          onPressed: () => _ouvrir('/produits/${p.id}/edit'))),
                    ],
                ],
              );
            },
          ),
        ),
      ]),
    );
  }
}