import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../core/format.dart';
import '../../core/theme.dart';
import '../../models/commande.dart';
import '../../repositories/commande_repository.dart';
import '../../widgets/app_shell.dart';
import '../../widgets/bs_button.dart';
import '../../widgets/bs_card.dart';
import '../../widgets/bs_table.dart';
import '../../widgets/page_header.dart';

class CommandesPage extends StatefulWidget {
  const CommandesPage({super.key});
  @override
  State<CommandesPage> createState() => _CommandesPageState();
}

class _CommandesPageState extends State<CommandesPage> {
  final _repo = CommandeRepository();
  late Future<List<Commande>> _future = _repo.toutes();

  void _recharger() => setState(() => _future = _repo.toutes());

  /// Ouvre un écran puis recharge la liste au retour.
  Future<void> _ouvrir(String route) async {
    await context.push(route);
    if (mounted) _recharger();
  }

  /// Nouvelle commande : à la création, on ouvre directement son détail.
  Future<void> _nouvelle() async {
    final nouvelId = await context.push<int>('/commandes/create');
    if (!mounted) return;
    _recharger();
    if (nouvelId != null) _ouvrir('/commandes/$nouvelId');
  }

  @override
  Widget build(BuildContext context) {
    return AppShell(
      title: 'Commandes',
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        PageHeader(
          title: 'Liste des commandes',
          trailing: BsButton('+ Nouvelle commande', onPressed: _nouvelle),
        ),
        BsCard(
          padding: EdgeInsets.zero,
          child: FutureBuilder<List<Commande>>(
            future: _future,
            builder: (context, snap) {
              if (!snap.hasData) {
                return const Padding(
                    padding: EdgeInsets.all(24),
                    child: Center(child: CircularProgressIndicator()));
              }
              return BsTable(
                emptyText: 'Aucune commande.',
                columns: const [
                  DataColumn(label: Text('N°')),
                  DataColumn(label: Text('Date')),
                  DataColumn(label: Text('Client')),
                  DataColumn(label: Text('Département')),
                  DataColumn(label: Text('Région')),
                  DataColumn(label: Text('Produits'), numeric: true),
                  DataColumn(label: Text('Total'), numeric: true),
                  DataColumn(label: Text('Action')),
                ],
                rows: [
                  for (final c in snap.data!)
                    [
                      DataCell(Text('${c.id}')),
                      DataCell(Text(dateFr(c.date))),
                      DataCell(Text(c.clientNom)),
                      DataCell(Text(c.nomDep)),
                      DataCell(Text(c.nomRegion)),
                      DataCell(Text('${c.lignes.length}')),
                      DataCell(Text(fcfa(c.total))),
                      DataCell(Row(children: [
                        BsButton('Voir',
                            small: true,
                            onPressed: () => _ouvrir('/commandes/${c.id}')),
                        const SizedBox(width: 6),
                        BsButton('Modifier',
                            small: true,
                            color: Bs.secondary,
                            onPressed: () =>
                                _ouvrir('/commandes/${c.id}/edit')),
                      ])),
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