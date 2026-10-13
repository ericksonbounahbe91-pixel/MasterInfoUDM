import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../core/theme.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/auth_card.dart';
import '../../widgets/bs_button.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});
  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final _form = GlobalKey<FormState>();
  final _email = TextEditingController();
  final _pwd = TextEditingController();
  bool _remember = false;
  bool _loading = false;
  String? _error; // = @error('email') du Blade

  @override
  void dispose() {
    _email.dispose();
    _pwd.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    setState(() => _error = null);
    if (!_form.currentState!.validate()) return;
    setState(() => _loading = true);
    try {
      await context
          .read<AuthProvider>()
          .login(_email.text, _pwd.text, _remember);
      // go_router redirige tout seul vers le tableau de bord
    } catch (e) {
      setState(() => _error = e.toString());
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
          const Text('Connectez-vous pour accéder à la plateforme',
              textAlign: TextAlign.center, style: TextStyle(fontSize: 20)),
          const SizedBox(height: 24),
          TextFormField(
            controller: _email,
            keyboardType: TextInputType.emailAddress,
            decoration: InputDecoration(
                labelText: 'Adresse e-mail', errorText: _error),
            validator: (v) {
              final t = (v ?? '').trim();
              if (t.isEmpty) return "L'adresse e-mail est obligatoire.";
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
            decoration: const InputDecoration(labelText: 'Mot de passe'),
            validator: (v) => (v == null || v.isEmpty)
                ? 'Le mot de passe est obligatoire.'
                : null,
          ),
          CheckboxListTile(
            contentPadding: EdgeInsets.zero,
            controlAffinity: ListTileControlAffinity.leading,
            title: const Text('Se souvenir de moi'),
            value: _remember,
            onChanged: (v) => setState(() => _remember = v ?? false),
          ),
          BsButton(_loading ? 'Connexion…' : 'Se connecter',
              onPressed: _loading ? null : _submit),
          const SizedBox(height: 24),
          Row(mainAxisAlignment: MainAxisAlignment.center, children: [
            const Text('Pas encore de compte ? ',
                style: TextStyle(color: Bs.muted)),
            GestureDetector(
              onTap: () => context.go('/register'),
              child: const Text('Créer un compte',
                  style: TextStyle(color: Bs.primary)),
            ),
          ]),
        ]),
      ),
    );
  }
}