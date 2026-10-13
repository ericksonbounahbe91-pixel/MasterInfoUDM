import 'package:flutter/material.dart';
import '../api.dart';
import '../utils.dart';

class ProduitsPage extends StatefulWidget {
  const ProduitsPage({super.key});

  @override
  State<ProduitsPage> createState() => _ProduitsPageState();
}

class _ProduitsPageState extends State<ProduitsPage> {
  late Future<List<dynamic>> _f = _load();

  Future<List<dynamic>> _load() async => await Api.get('/produits') as List<dynamic>;

  void _reload() => setState(() {
        _f = _load();
      });

  Future<void> _ouvrir([Map<String, dynamic>? p]) async {
    final ok = await Navigator.push<bool>(
      context,
      MaterialPageRoute(builder: (_) => ProduitFormPage(produit: p)),
    );
    if (ok == true) _reload();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Produits')),
      floatingActionButton: FloatingActionButton(
        onPressed: () => _ouvrir(),
        child: const Icon(Icons.add),
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
              final p = Map<String, dynamic>.from(liste[i] as Map);
              final stock = toInt(p['qteStock']);
              return ListTile(
                leading: const CircleAvatar(child: Icon(Icons.inventory_2_outlined)),
                title: Text('${p['designProd']}'),
                subtitle: Text('Stock : $stock', style: TextStyle(color: stock < 10 ? Colors.red : null)),
                trailing: Text(fcfa(p['prixUProd']), style: const TextStyle(fontWeight: FontWeight.bold)),
                onTap: () => _ouvrir(p),
              );
            },
          ),
        ),
      ),
    );
  }
}

// Création et modification d'un produit
class ProduitFormPage extends StatefulWidget {
  final Map<String, dynamic>? produit;
  const ProduitFormPage({super.key, this.produit});

  @override
  State<ProduitFormPage> createState() => _ProduitFormPageState();
}

class _ProduitFormPageState extends State<ProduitFormPage> {
  final _form = GlobalKey<FormState>();
  late final TextEditingController _design =
      TextEditingController(text: widget.produit == null ? '' : '${widget.produit!['designProd']}');
  late final TextEditingController _qte =
      TextEditingController(text: widget.produit == null ? '' : '${toInt(widget.produit!['qteStock'])}');
  late final TextEditingController _prix =
      TextEditingController(text: widget.produit == null ? '' : plain(toNum(widget.produit!['prixUProd'])));
  bool _busy = false;

  @override
  void dispose() {
    _design.dispose();
    _qte.dispose();
    _prix.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (!_form.currentState!.validate()) return;
    setState(() => _busy = true);
    final body = {
      'designProd': _design.text.trim(),
      'qteStock': int.parse(_qte.text.trim()),
      'prixUProd': num.parse(_prix.text.trim().replaceAll(',', '.')),
    };
    try {
      if (widget.produit == null) {
        await Api.post('/produits', body);
      } else {
        await Api.put('/produits/${widget.produit!['idProd']}', body);
      }
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
    final edition = widget.produit != null;
    return Scaffold(
      appBar: AppBar(title: Text(edition ? 'Modifier le produit' : 'Nouveau produit')),
      body: Form(
        key: _form,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            TextFormField(
              controller: _design,
              decoration: const InputDecoration(labelText: 'Désignation', border: OutlineInputBorder()),
              validator: (v) => (v == null || v.trim().isEmpty) ? 'Champ obligatoire' : null,
            ),
            const SizedBox(height: 14),
            TextFormField(
              controller: _qte,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(labelText: 'Quantité en stock', border: OutlineInputBorder()),
              validator: (v) => int.tryParse((v ?? '').trim()) == null || int.parse(v!.trim()) < 0
                  ? 'Entier positif requis'
                  : null,
            ),
            const SizedBox(height: 14),
            TextFormField(
              controller: _prix,
              keyboardType: const TextInputType.numberWithOptions(decimal: true),
              decoration: const InputDecoration(labelText: 'Prix unitaire (FCFA)', border: OutlineInputBorder()),
              validator: (v) {
                final n = num.tryParse((v ?? '').trim().replaceAll(',', '.'));
                return (n == null || n < 0) ? 'Nombre positif requis' : null;
              },
            ),
            const SizedBox(height: 20),
            FilledButton(
              onPressed: _busy ? null : _save,
              child: Text(_busy ? 'Enregistrement…' : 'Enregistrer'),
            ),
          ],
        ),
      ),
    );
  }
}
