import 'package:flutter/material.dart';
import '../api.dart';
import '../utils.dart';

class ClientsPage extends StatefulWidget {
  const ClientsPage({super.key});

  @override
  State<ClientsPage> createState() => _ClientsPageState();
}

class _ClientsPageState extends State<ClientsPage> {
  late Future<List<dynamic>> _f = _load();

  Future<List<dynamic>> _load() async => await Api.get('/clients') as List<dynamic>;

  void _reload() => setState(() {
        _f = _load();
      });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Clients')),
      body: Chargement<List<dynamic>>(
        future: _f,
        onRetry: _reload,
        builder: (liste) => RefreshIndicator(
          onRefresh: () async {
            _reload();
            try {
              await _f;
            } catch (_) {}
          },
          child: ListView.separated(
            physics: const AlwaysScrollableScrollPhysics(),
            itemCount: liste.length,
            separatorBuilder: (_, __) => const Divider(height: 1),
            itemBuilder: (_, i) {
              final c = liste[i] as Map;
              return ListTile(
                leading: CircleAvatar(child: Text('${c['prenomCli']}'.isEmpty ? '?' : '${c['prenomCli']}'[0])),
                title: Text('${c['prenomCli']} ${c['nomCli']}'),
                subtitle: Text('${c['email']}\n${c['region']} — ${c['departement']}'),
                isThreeLine: true,
              );
            },
          ),
        ),
      ),
    );
  }
}
