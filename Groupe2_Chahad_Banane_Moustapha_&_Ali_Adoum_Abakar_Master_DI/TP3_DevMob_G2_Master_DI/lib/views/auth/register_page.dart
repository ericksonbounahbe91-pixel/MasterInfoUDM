import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../core/theme.dart';
import '../../providers/auth_provider.dart';
import '../../repositories/client_repository.dart' show ErreurMetier;
import '../../widgets/auth_card.dart';
import '../../widgets/bs_button.dart';

class RegisterPage extends StatefulWidget {
  const RegisterPage({super.key});
  @override
  State<RegisterPage> createState() => _RegisterPageState();
}

class _RegisterPageState extends State<RegisterPage> {
  final _form = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _pwd = TextEditingController();
  final _confirm = TextEditingController();
  bool _loading = false;
  String? _emailErreur; // erreur venant de la base (e-mail déjà pris)

  @override
  void dispose() {
    _name.dispose();
    _email.dispose();
    _pwd.dispose();
    _confirm.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    setState(() => _emailErreur = null);
    if (!_form.currentState!.validate()) return;
    setState(() => _loading = true);
    try {
      await context
          .read<AuthProvider>()
          .register(_name.text, _email.text, _pwd.text, _confirm.text);
      // go_router redirige vers le tableau de bord (utilisateur connecté)
    } on ErreurMetier catch (e) {
      setState(() => _emailErreur = e.message);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return AuthCard(
      child: Form(
        key: _form,
        child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
          const Text('Créer un compte',
              textAlign: TextAlign.center, style: TextStyle(fontSize: 20)),
          const SizedBox(height: 24),
          TextFormField(
            controller: _name,
            textCapitalization: TextCapitalization.words,
            maxLength: 100,
            decoration:
            const InputDecoration(labelText: 'Nom complet', counterText: ''),
            validator: (v) =>
            (v ?? '').trim().isEmpty ? 'Le nom est obligatoire.' : null,
          ),
          const SizedBox(height: 16),
          TextFormField(
            controller: _email,
            keyboardType: TextInputType.emailAddress,
            decoration: InputDecoration(
                labelText: 'Adresse e-mail', errorText: _emailErreur),
            validator: (v) {
              final t = (v ?? '').trim();
              if (t.isEmpty) return "L'adresse e-mail est obligatoire.";
              if (t.length > 150) return "L'adresse e-mail est trop longue.";
              if (!RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$').hasMatch(t)) {
                return "L'adresse e-mail n'est pas valide.";
              }
              return null;
            },
          ),
          const SizedBox(height: 16),
          TextFormField(
            controller: _pwd,
            obscureText: true,
            decoration: const InputDecoration(
                labelText: 'Mot de passe (8 caractères minimum)'),
            validator: (v) {
              if (v == null || v.isEmpty) {
                return 'Le mot de passe est obligatoire.';
              }
              if (v.length < 8) {
                return 'Le mot de passe doit contenir au moins 8 caractères.';
              }
              return null;
            },
          ),
          const SizedBox(height: 16),
          TextFormField(
            controller: _confirm,
            obscureText: true,
            decoration:
            const InputDecoration(labelText: 'Confirmer le mot de passe'),
            validator: (v) => v != _pwd.text
                ? 'La confirmation du mot de passe ne correspond pas.'
                : null,
          ),
          const SizedBox(height: 24),
          BsButton(_loading ? 'Création…' : 'Créer mon compte',
              onPressed: _loading ? null : _submit),
          const SizedBox(height: 24),
          Row(mainAxisAlignment: MainAxisAlignment.center, children: [
            const Text('Vous avez déjà un compte ? ',
                style: TextStyle(color: Bs.muted)),
            GestureDetector(
              onTap: () => context.go('/login'),
              child: const Text('Se connecter',
                  style: TextStyle(color: Bs.primary)),
            ),
          ]),
        ]),
      ),
    );
  }
}