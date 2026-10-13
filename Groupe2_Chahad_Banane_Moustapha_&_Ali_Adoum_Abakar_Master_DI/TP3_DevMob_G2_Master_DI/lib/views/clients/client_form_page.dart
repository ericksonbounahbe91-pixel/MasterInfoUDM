import 'package:flutter/material.dart';
import '../../core/theme.dart';
import '../../core/ui.dart';
import '../../repositories/client_repository.dart';
import '../../widgets/app_shell.dart';
import '../../widgets/bs_button.dart';
import '../../widgets/bs_card.dart';
import '../../widgets/page_header.dart';

class ClientFormPage extends StatefulWidget {
  final int? id; // null = ajout
  const ClientFormPage({super.key, this.id});
  @override
  State<ClientFormPage> createState() => _ClientFormPageState();
}

class _ClientFormPageState extends State<ClientFormPage> {
  final _repo = ClientRepository();
  final _form = GlobalKey<FormState>();
  final _nom = TextEditingController();
  final _prenom = TextEditingController();
  final _email = TextEditingController();

  // Les contrôleurs des champs Autocomplete sont créés par le widget lui-même
  TextEditingController? _dep;
  TextEditingController? _reg;
  String _depInitial = '';
  String _regInitial = '';

  List<String> _deps = [];
  List<String> _regs = [];
  bool _ready = false;
  bool _saving = false;
  String? _erreur;

  bool get _edition => widget.id != null;

  @override
  void initState() {
    super.initState();
    _charger();
  }

  Future<void> _charger() async {
    _deps = await _repo.departements();
    _regs = await _repo.regions();
    if (_edition) {
      final c = await _repo.parId(widget.id!);
      if (c != null) {
        _nom.text = c.nom;
        _prenom.text = c.prenom;
        _email.text = c.email;
        _depInitial = c.nomDep ?? '';
        _regInitial = c.nomRegion ?? '';
      }
    }
    if (mounted) setState(() => _ready = true);
  }

  @override
  void dispose() {
    _nom.dispose();
    _prenom.dispose();
    _email.dispose();
    super.dispose();
  }

  Future<void> _enregistrer() async {
    if (!_form.currentState!.validate()) return;
    setState(() {
      _saving = true;
      _erreur = null;
    });
    try {
      await _repo.enregistrer(
        id: widget.id,
        nom: _nom.text.trim(),
        prenom: _prenom.text.trim(),
        email: _email.text,
        nomDep: _dep!.text,
        nomRegion: _reg!.text,
      );
      if (!mounted) return;
      toast(context,
          _edition ? 'Client modifié avec succès.' : 'Client ajouté avec succès.');
      retour(context, '/clients');
    } on ErreurMetier catch (e) {
      setState(() => _erreur = e.message);
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  /// Champ texte avec liste de suggestions (= <input list="..."> + <datalist>)
  Widget _suggest({
    required String label,
    required List<String> options,
    required String initial,
    required void Function(TextEditingController) onController,
    required String requis,
  }) {
    return Autocomplete<String>(
      initialValue: TextEditingValue(text: initial),
      optionsBuilder: (v) => options
          .where((o) => o.toLowerCase().contains(v.text.toLowerCase())),
      fieldViewBuilder: (context, ctrl, focus, onSubmit) {
        onController(ctrl); // référence pour lire la valeur saisie
        return TextFormField(
          controller: ctrl,
          focusNode: focus,
          decoration: InputDecoration(
            label: Text.rich(TextSpan(children: [
              TextSpan(text: label),
              const TextSpan(text: ' *', style: TextStyle(color: Bs.danger)),
            ])),
          ),
          validator: (v) => (v ?? '').trim().isEmpty ? requis : null,
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final titre = _edition ? 'Modifier un client' : 'Ajouter un client';
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
              onPressed: () => retour(context, '/clients')),
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
                      if (_erreur != null) ...[
                        AlertBox.danger(child: Text(_erreur!)),
                        const SizedBox(height: 16),
                      ],
                      TextFormField(
                        controller: _nom,
                        decoration: const InputDecoration(labelText: 'Nom'),
                        validator: (v) {
                          final t = (v ?? '').trim();
                          if (t.isEmpty) return 'Le nom est obligatoire.';
                          if (t.length > 100) {
                            return 'Le nom ne doit pas dépasser 100 caractères.';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _prenom,
                        decoration: const InputDecoration(labelText: 'Prénom'),
                        validator: (v) {
                          final t = (v ?? '').trim();
                          if (t.isEmpty) return 'Le prénom est obligatoire.';
                          if (t.length > 100) {
                            return 'Le prénom ne doit pas dépasser 100 caractères.';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _email,
                        keyboardType: TextInputType.emailAddress,
                        decoration: const InputDecoration(labelText: 'Email'),
                        validator: (v) {
                          final t = (v ?? '').trim();
                          if (t.isEmpty) return "L'email est obligatoire.";
                          if (!RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$')
                              .hasMatch(t)) {
                            return "L'email n'est pas valide.";
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 16),
                      _suggest(
                        label: 'Département',
                        options: _deps,
                        initial: _depInitial,
                        onController: (c) => _dep = c,
                        requis: 'Le département est obligatoire.',
                      ),
                      const SizedBox(height: 16),
                      _suggest(
                        label: 'Région',
                        options: _regs,
                        initial: _regInitial,
                        onController: (c) => _reg = c,
                        requis: 'La région est obligatoire.',
                      ),
                      const SizedBox(height: 24),
                      Wrap(spacing: 8, children: [
                        BsButton(
                          _edition ? 'Mettre à jour' : 'Enregistrer',
                          color: Bs.success,
                          onPressed: _saving ? null : _enregistrer,
                        ),
                        if (_edition)
                          BsButton('Annuler',
                              color: Bs.secondary,
                              onPressed: () => retour(context, '/clients')),
                      ]),
                    ]),
              ),
            ),
          ),
        ),
      ]),
    );
  }
}