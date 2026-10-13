import 'package:flutter/material.dart';
import '../api.dart';
import '../utils.dart';

// ---------------------------------------------------------------- Liste
class CommandesPage extends StatefulWidget {
  const CommandesPage({super.key});

  @override
  State<CommandesPage> createState() => _CommandesPageState();
}

class _CommandesPageState extends State<CommandesPage> {
  late Future<List<dynamic>> _f = _load();

  Future<List<dynamic>> _load() async => await Api.get('/commandes') as List<dynamic>;

  void _reload() => setState(() {
        _f = _load();
      });

  Future<void> _aller(Widget page) async {
    await Navigator.push(context, MaterialPageRoute(builder: (_) => page));
    if (mounted) _reload();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Commandes')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _aller(const CommandeFormPage()),
        icon: const Icon(Icons.add),
        label: const Text('Nouvelle'),
      ),
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
                title: Text('Commande n°${c['idCmd']} — ${c['client']}'),
                subtitle: Text('${dateFr('${c['dateCmd']}')} • ${c['nbProduits']} produit(s)'),
                trailing: Text(fcfa(c['total']), style: const TextStyle(fontWeight: FontWeight.bold)),
                onTap: () => _aller(CommandeDetailPage(id: toInt(c['idCmd']))),
              );
            },
          ),
        ),
      ),
    );
  }
}

// --------------------------------------------------------------- Détail
class CommandeDetailPage extends StatefulWidget {
  final int id;
  const CommandeDetailPage({super.key, required this.id});

  @override
  State<CommandeDetailPage> createState() => _CommandeDetailPageState();
}

class _CommandeDetailPageState extends State<CommandeDetailPage> {
  late Future<Map<String, dynamic>> _f = _load();

  Future<Map<String, dynamic>> _load() async =>
      Map<String, dynamic>.from(await Api.get('/commandes/${widget.id}') as Map);

  void _reload() => setState(() {
        _f = _load();
      });

  // Supprimer un produit dans la commande (le stock est restitué côté serveur)
  Future<void> _retirer(Map l) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Retirer ce produit ?'),
        content: Text('« ${l['designProd']} » sera retiré de la commande et le stock restitué.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Annuler')),
          FilledButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Supprimer')),
        ],
      ),
    );
    if (ok != true) return;
    try {
      await Api.delete('/commandes/${widget.id}/produits/${l['idProd']}');
      if (!mounted) return;
      snack(context, 'Produit retiré de la commande.');
      _reload();
    } on ApiException catch (e) {
      if (!mounted) return;
      snack(context, e.message);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Commande n°${widget.id}')),
      body: Chargement<Map<String, dynamic>>(
        future: _f,
        onRetry: _reload,
        builder: (c) {
          final lignes = c['lignes'] as List;
          return ListView(
            padding: const EdgeInsets.all(12),
            children: [
              Card(
                child: ListTile(
                  title: Text('${c['client']}', style: const TextStyle(fontWeight: FontWeight.bold)),
                  subtitle: Text('${c['region']} — ${c['departement']}\n${dateFr('${c['dateCmd']}')}'),
                  isThreeLine: true,
                ),
              ),
              const SizedBox(height: 8),
              if (lignes.isEmpty)
                const Padding(
                  padding: EdgeInsets.all(16),
                  child: Text('Aucun produit dans cette commande.'),
                ),
              for (final l in lignes)
                Card(
                  child: ListTile(
                    title: Text('${l['designProd']}'),
                    subtitle: Text('${l['qteCmd']} × ${fcfa(l['prixUProd'])}'),
                    trailing: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(fcfa(l['sousTotal']), style: const TextStyle(fontWeight: FontWeight.bold)),
                        IconButton(
                          icon: const Icon(Icons.delete_outline, color: Colors.red),
                          onPressed: () => _retirer(l as Map),
                        ),
                      ],
                    ),
                  ),
                ),
              const SizedBox(height: 8),
              Align(
                alignment: Alignment.centerRight,
                child: Text('Total : ${fcfa(c['total'])}',
                    style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              ),
            ],
          );
        },
      ),
    );
  }
}

// -------------------------------------------------------------- Création
class _Ligne {
  int? idProd;
  final TextEditingController qte = TextEditingController(text: '1');
}

class CommandeFormPage extends StatefulWidget {
  const CommandeFormPage({super.key});

  @override
  State<CommandeFormPage> createState() => _CommandeFormPageState();
}

class _CommandeFormPageState extends State<CommandeFormPage> {
  late final Future<List<dynamic>> _f = Future.wait([Api.get('/clients'), Api.get('/produits')]);

  int? _idCli;
  DateTime _date = DateTime.now();
  final List<_Ligne> _lignes = [_Ligne()];
  bool _busy = false;

  @override
  void dispose() {
    for (final l in _lignes) {
      l.qte.dispose();
    }
    super.dispose();
  }

  String get _dateIso => _date.toIso8601String().substring(0, 10);

  Future<void> _choisirDate() async {
    final d = await showDatePicker(
      context: context,
      initialDate: _date,
      firstDate: DateTime(2020),
      lastDate: DateTime(2100),
    );
    if (d != null) setState(() => _date = d);
  }

  num _total(List produits) {
    num t = 0;
    for (final l in _lignes) {
      if (l.idProd == null) continue;
      final p = produits.firstWhere((p) => toInt(p['idProd']) == l.idProd, orElse: () => null);
      if (p != null) t += toNum(p['prixUProd']) * (int.tryParse(l.qte.text) ?? 0);
    }
    return t;
  }

  Future<void> _save() async {
    if (_idCli == null) {
      snack(context, 'Choisissez un client.');
      return;
    }
    final lignes = <Map<String, dynamic>>[];
    for (final l in _lignes) {
      final q = int.tryParse(l.qte.text.trim()) ?? 0;
      if (l.idProd == null || q < 1) {
        snack(context, 'Complétez chaque ligne : produit et quantité (au moins 1).');
        return;
      }
      lignes.add({'idProd': l.idProd, 'qteCmd': q});
    }
    if (lignes.isEmpty) {
      snack(context, 'Ajoutez au moins un produit.');
      return;
    }
    setState(() => _busy = true);
    try {
      await Api.post('/commandes', {'idCli': _idCli, 'dateCmd': _dateIso, 'lignes': lignes});
      if (!mounted) return;
      Navigator.pop(context, true);
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() => _busy = false);
      snack(context, e.message);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Nouvelle commande')),
      body: Chargement<List<dynamic>>(
        future: _f,
        onRetry: () => Navigator.pop(context),
        builder: (data) {
          final clients = data[0] as List;
          final produits = (data[1] as List).where((p) => toInt(p['qteStock']) > 0).toList();
          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              DropdownButtonFormField<int>(
                isExpanded: true,
                value: _idCli,
                decoration: const InputDecoration(labelText: 'Client', border: OutlineInputBorder()),
                items: [
                  for (final c in clients)
                    DropdownMenuItem<int>(
                      value: toInt(c['idCli']),
                      child: Text('${c['nomCli']} ${c['prenomCli']}', overflow: TextOverflow.ellipsis),
                    ),
                ],
                onChanged: (v) => setState(() => _idCli = v),
              ),
              const SizedBox(height: 12),
              OutlinedButton.icon(
                onPressed: _choisirDate,
                icon: const Icon(Icons.calendar_today),
                label: Text('Date : ${dateFr(_dateIso)}'),
              ),
              const SizedBox(height: 16),
              const Text('Produits de la commande', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
              const SizedBox(height: 8),
              for (var i = 0; i < _lignes.length; i++)
                Padding(
                  key: ObjectKey(_lignes[i]),
                  padding: const EdgeInsets.only(bottom: 10),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        flex: 3,
                        child: DropdownButtonFormField<int>(
                          isExpanded: true,
                          value: _lignes[i].idProd,
                          hint: const Text('Produit'),
                          decoration: const InputDecoration(border: OutlineInputBorder()),
                          items: [
                            for (final p in produits)
                              DropdownMenuItem<int>(
                                value: toInt(p['idProd']),
                                child: Text('${p['designProd']} (${p['qteStock']})', overflow: TextOverflow.ellipsis),
                              ),
                          ],
                          onChanged: (v) => setState(() => _lignes[i].idProd = v),
                        ),
                      ),
                      const SizedBox(width: 8),
                      SizedBox(
                        width: 70,
                        child: TextField(
                          controller: _lignes[i].qte,
                          keyboardType: TextInputType.number,
                          decoration: const InputDecoration(labelText: 'Qté', border: OutlineInputBorder()),
                          onChanged: (_) => setState(() {}),
                        ),
                      ),
                      IconButton(
                        icon: const Icon(Icons.delete_outline),
                        onPressed: _lignes.length == 1
                            ? null
                            : () => setState(() {
                                  _lignes[i].qte.dispose();
                                  _lignes.removeAt(i);
                                }),
                      ),
                    ],
                  ),
                ),
              TextButton.icon(
                onPressed: () => setState(() => _lignes.add(_Ligne())),
                icon: const Icon(Icons.add),
                label: const Text('Ajouter un produit'),
              ),
              const Divider(),
              Align(
                alignment: Alignment.centerRight,
                child: Text('Total estimé : ${fcfa(_total(produits))}',
                    style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold)),
              ),
              const SizedBox(height: 16),
              FilledButton(
                onPressed: _busy ? null : _save,
                child: Text(_busy ? 'Enregistrement…' : 'Enregistrer la commande'),
              ),
            ],
          );
        },
      ),
    );
  }
}
