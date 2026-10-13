import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../models/client.dart';
import '../../repositories/client_repository.dart';
import '../../widgets/app_shell.dart';
import '../../widgets/bs_button.dart';
import '../../widgets/bs_card.dart';
import '../../widgets/bs_table.dart';
import '../../widgets/page_header.dart';

class ClientsPage extends StatefulWidget {
  const ClientsPage({super.key});
  @override
  State<ClientsPage> createState() => _ClientsPageState();
}

class _ClientsPageState extends State<ClientsPage> {
  final _repo = ClientRepository();
  late Future<List<Client>> _future = _repo.tous();

  Future<void> _ouvrir(String route) async {
    await context.push(route);
    if (mounted) setState(() => _future = _repo.tous());
  }

  @override
  Widget build(BuildContext context) {
    return AppShell(
      title: 'Clients',
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        PageHeader(
          title: 'Liste des clients',
          trailing: BsButton('+ Ajouter un client',
              onPressed: () => _ouvrir('/clients/create')),
        ),
        BsCard(
          padding: EdgeInsets.zero,
          child: FutureBuilder<List<Client>>(
            future: _future,
            builder: (context, snap) {
              if (!snap.hasData) {
                return const Padding(
                    padding: EdgeInsets.all(24),
                    child: Center(child: CircularProgressIndicator()));
              }
              return BsTable(
                emptyText: 'Aucun client.',
                columns: const [
                  DataColumn(label: Text('#')),
                  DataColumn(label: Text('Nom')),
                  DataColumn(label: Text('Prénom')),
                  DataColumn(label: Text('Email')),
                  DataColumn(label: Text('Région')),
                  DataColumn(label: Text('Département')),
                  DataColumn(label: Text('Action')),
                ],
                rows: [
                  for (final c in snap.data!)
                    [
                      DataCell(Text('${c.id}')),
                      DataCell(Text(c.nom)),
                      DataCell(Text(c.prenom)),
                      DataCell(Text(c.email)),
                      DataCell(Text(c.nomRegion ?? '')),
                      DataCell(Text(c.nomDep ?? '')),
                      DataCell(BsButton('Modifier',
                          small: true,
                          onPressed: () => _ouvrir('/clients/${c.id}/edit'))),
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