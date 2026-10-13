import 'package:flutter/material.dart';
import '../../core/format.dart';
import '../../core/theme.dart';
import '../../core/ui.dart';
import '../../models/ligne_commande.dart';
import '../../repositories/client_repository.dart' show ErreurMetier;
import '../../repositories/commande_repository.dart';
import '../../widgets/app_shell.dart';
import '../../widgets/bs_button.dart';
import '../../widgets/bs_card.dart';
import '../../widgets/page_header.dart';

class LigneEditPage extends StatefulWidget {
  final int idCmd;
  final int idProd;
  const LigneEditPage({super.key, required this.idCmd, required this.idProd});
  @override
  State<LigneEditPage> createState() => _LigneEditPageState();
}

class _LigneEditPageState extends State<LigneEditPage> {
  final _repo = CommandeRepository();
  final _form = GlobalKey<FormState>();
  final _qte = TextEditingController();
  LigneCommande? _ligne;
  bool _ready = false;
  bool _saving = false;
  String? _erreur;

  String get _retourRoute => '/commandes/${widget.idCmd}';

  @override
  void initState() {
    super.initState();
    _charger();
  }

  @override
  void dispose() {
    _qte.dispose();
    super.dispose();
  }

  Future<void> _charger() async {
    final c = await _repo.parId(widget.idCmd);
    _ligne = c?.lignes.where((l) => l.idProd == widget.idProd).firstOrNull;
    if (_ligne != null) _qte.text = '${_ligne!.qte}';
    if (mounted) setState(() => _ready = true);
  }

  Future<void> _enregistrer() async {
    if (!_form.currentState!.validate()) return;
    setState(() {
      _saving = true;
      _erreur = null;
    });
    try {
      await _repo.modifierQuantite(
          widget.idCmd, widget.idProd, int.parse(_qte.text.trim()));
      if (!mounted) return;
      toast(context, 'Quantité mise à jour, stock ajusté.');
      retour(context, _retourRoute);
    } on ErreurMetier catch (e) {
      setState(() => _erreur = e.message);
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  Future<void> _retirer() async {
    final l = _ligne!;
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        content: Text('Retirer « ${l.design} » de la commande ?'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(context, false),
              child: const Text('Annuler')),
          TextButton(
              onPressed: () => Navigator.pop(context, true),
              child: const Text('Retirer', style: TextStyle(color: Bs.danger))),
        ],
      ),
    );
    if (ok != true) return;
    try {
      await _repo.retirerProduit(widget.idCmd, widget.idProd);
      if (!mounted) return;
      toast(context, 'Produit retiré de la commande, stock remis à jour.');
      retour(context, _retourRoute);
    } on ErreurMetier catch (e) {
      setState(() => _erreur = e.message);
    }
  }

  Widget _info(String k, String v) => Padding(
    padding: const EdgeInsets.symmetric(vertical: 4),
    child: Row(children: [
      Expanded(
          child:
          Text(k, style: const TextStyle(fontWeight: FontWeight.bold))),
      Expanded(child: Text(v)),
    ]),
  );

  @override
  Widget build(BuildContext context) {
    final titre = 'Modifier la commande n° ${widget.idCmd}';
    if (!_ready) {
      return AppShell(
          title: titre, child: const Center(child: CircularProgressIndicator()));
    }
    final btnRetour = BsButton('← Retour à la commande',
        small: true,
        outline: true,
        color: Bs.secondary,
        onPressed: () => retour(context, _retourRoute));

    final l = _ligne;
    if (l == null) {
      return AppShell(
        title: titre,
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          PageHeader(title: titre, trailing: btnRetour),
          const AlertBox.danger(
              child: Text('Ce produit ne figure pas dans cette commande.')),
        ]),
      );
    }

    return AppShell(
      title: titre,
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        PageHeader(title: titre, trailing: btnRetour),
        Align(
          alignment: Alignment.centerLeft,
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 600),
            child:
            Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
              BsCard(
                child: Form(
                  key: _form,
                  child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        if (_erreur != null) ...[
                          AlertBox.danger(child: Text(_erreur!)),
                          const SizedBox(height: 16),
                        ],
                        _info('Produit', l.design),
                        _info('Prix unitaire', fcfa(l.prix)),
                        _info('Quantité actuelle', '${l.qte}'),
                        _info('Quantité maximale possible', '${l.qteMax}'),
                        const SizedBox(height: 24),
                        TextFormField(
                          controller: _qte,
                          keyboardType: TextInputType.number,
                          decoration: const InputDecoration(
                              labelText: 'Nouvelle quantité'),
                          validator: (v) {
                            final t = (v ?? '').trim();
                            if (t.isEmpty) return 'La quantité est obligatoire.';
                            final n = int.tryParse(t);
                            if (n == null) {
                              return 'La quantité doit être un nombre entier.';
                            }
                            if (n < 1) {
                              return 'La quantité doit être au moins égale à 1.';
                            }
                            return null;
                          },
                        ),
                        const SizedBox(height: 16),
                        Wrap(spacing: 8, children: [
                          BsButton('Enregistrer',
                              color: Bs.success,
                              onPressed: _saving ? null : _enregistrer),
                          BsButton('Annuler',
                              outline: true,
                              color: Bs.secondary,
                              onPressed: () => retour(context, _retourRoute)),
                        ]),
                      ]),
                ),
              ),
              const SizedBox(height: 24),
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(color: Bs.danger),
                ),
                child: Wrap(
                  alignment: WrapAlignment.spaceBetween,
                  crossAxisAlignment: WrapCrossAlignment.center,
                  spacing: 12,
                  runSpacing: 12,
                  children: [
                    Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      const Text('Retirer ce produit de la commande',
                          style: TextStyle(fontWeight: FontWeight.bold)),
                      Text('Les ${l.qte} unité(s) seront remises en stock.',
                          style: const TextStyle(color: Bs.muted, fontSize: 13)),
                    ]),
                    BsButton('Retirer', color: Bs.danger, onPressed: _retirer),
                  ],
                ),
              ),
            ]),
          ),
        ),
      ]),
    );
  }
}