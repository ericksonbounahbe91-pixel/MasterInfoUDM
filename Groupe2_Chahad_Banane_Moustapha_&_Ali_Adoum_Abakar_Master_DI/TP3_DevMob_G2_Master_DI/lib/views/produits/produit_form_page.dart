import 'package:flutter/material.dart';
import '../../core/theme.dart';
import '../../core/ui.dart';
import '../../models/produit.dart';
import '../../repositories/produit_repository.dart';
import '../../widgets/app_shell.dart';
import '../../widgets/bs_button.dart';
import '../../widgets/bs_card.dart';
import '../../widgets/page_header.dart';

class ProduitFormPage extends StatefulWidget {
  final int? id; // null = ajout, sinon modification
  const ProduitFormPage({super.key, this.id});
  @override
  State<ProduitFormPage> createState() => _ProduitFormPageState();
}

class _ProduitFormPageState extends State<ProduitFormPage> {
  final _repo = ProduitRepository();
  final _form = GlobalKey<FormState>();
  final _design = TextEditingController();
  final _stock = TextEditingController(text: '0');
  final _prix = TextEditingController();
  bool _ready = false;
  bool _saving = false;

  bool get _edition => widget.id != null;

  @override
  void initState() {
    super.initState();
    _charger();
  }

  Future<void> _charger() async {
    if (_edition) {
      final p = await _repo.parId(widget.id!);
      if (p != null) {
        _design.text = p.design;
        _stock.text = '${p.stock}';
        _prix.text = p.prix % 1 == 0 ? '${p.prix.toInt()}' : '${p.prix}';
      }
    }
    if (mounted) setState(() => _ready = true);
  }

  @override
  void dispose() {
    _design.dispose();
    _stock.dispose();
    _prix.dispose();
    super.dispose();
  }

  Future<void> _enregistrer() async {
    if (!_form.currentState!.validate()) return;
    setState(() => _saving = true);
    final p = Produit(
      design: _design.text.trim(),
      stock: int.parse(_stock.text.trim()),
      prix: double.parse(_prix.text.trim().replaceAll(',', '.')),
    );
    if (_edition) {
      await _repo.modifier(widget.id!, p);
    } else {
      await _repo.creer(p);
    }
    if (!mounted) return;
    toast(context,
        _edition ? 'Produit modifié avec succès.' : 'Produit ajouté avec succès.');
    retour(context, '/produits');
  }

  @override
  Widget build(BuildContext context) {
    final titre =
    _edition ? 'Modifier le produit n° ${widget.id}' : 'Ajouter un produit';
    if (!_ready) {
      return AppShell(
          title: titre, child: const Center(child: CircularProgressIndicator()));
    }
    return AppShell(
      title: titre,
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        PageHeader(
          title: titre,
          trailing: BsButton('← Retour',
              small: true,
              outline: true,
              color: Bs.secondary,
              onPressed: () => retour(context, '/produits')),
        ),
        Align(
          alignment: Alignment.centerLeft,
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 600),
            child: BsCard(
              child: Form(
                key: _form,
                child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      TextFormField(
                        controller: _design,
                        decoration:
                        const InputDecoration(labelText: 'Désignation'),
                        validator: (v) {
                          final t = (v ?? '').trim();
                          if (t.isEmpty) return 'La désignation est obligatoire.';
                          if (t.length > 150) {
                            return 'La désignation ne doit pas dépasser 150 caractères.';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _stock,
                        keyboardType: TextInputType.number,
                        decoration: const InputDecoration(
                            labelText: 'Quantité en stock'),
                        validator: (v) {
                          final t = (v ?? '').trim();
                          if (t.isEmpty) return 'Le stock est obligatoire.';
                          final n = int.tryParse(t);
                          if (n == null) {
                            return 'Le stock doit être un nombre entier.';
                          }
                          if (n < 0) return 'Le stock ne peut pas être négatif.';
                          return null;
                        },
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _prix,
                        keyboardType: const TextInputType.numberWithOptions(
                            decimal: true),
                        decoration: const InputDecoration(
                            labelText: 'Prix unitaire (FCFA)'),
                        validator: (v) {
                          final t = (v ?? '').trim().replaceAll(',', '.');
                          if (t.isEmpty) return 'Le prix est obligatoire.';
                          final n = double.tryParse(t);
                          if (n == null) return 'Le prix doit être un nombre.';
                          if (n < 0) return 'Le prix ne peut pas être négatif.';
                          return null;
                        },
                      ),
                      const SizedBox(height: 24),
                      Align(
                        alignment: Alignment.centerLeft,
                        child: BsButton(
                          'Enregistrer',
                          // vert à l'ajout, bleu à la modification (comme le Blade)
                          color: _edition ? Bs.primary : Bs.success,
                          onPressed: _saving ? null : _enregistrer,
                        ),
                      ),
                    ]),
              ),
            ),
          ),
        ),
      ]),
    );
  }
}