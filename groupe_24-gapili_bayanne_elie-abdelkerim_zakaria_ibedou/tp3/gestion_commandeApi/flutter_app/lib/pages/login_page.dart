import 'package:flutter/material.dart';
import '../api_service.dart';
import '../theme.dart';
import '../widgets/common.dart';
import 'home_shell.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});
  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final login = TextEditingController(text: 'MasTMound2');
  final password = TextEditingController(text: 'MoundMasT2@2026');
  bool busy = false;
  bool hide = true;
  String? error;

  @override
  void dispose() {
    login.dispose();
    password.dispose();
    super.dispose();
  }

  Future<void> submit() async {
    if (busy) return;
    setState(() {
      busy = true;
      error = null;
    });
    try {
      await api.login(login.text.trim(), password.text);
      if (!mounted) return;
      Navigator.pushReplacement(
          context, MaterialPageRoute(builder: (_) => const HomeShell()));
    } catch (e) {
      setState(() => error = e.toString().replaceFirst('Exception: ', ''));
    } finally {
      if (mounted) setState(() => busy = false);
    }
  }

  Widget _brandPanel() => Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 64,
            height: 64,
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.14),
              borderRadius: BorderRadius.circular(20),
            ),
            child: const Icon(Icons.shopping_cart_checkout, color: Colors.white, size: 34),
          ),
          const SizedBox(height: 24),
          const Text('Gestion des\ncommandes',
              style: TextStyle(
                  color: Colors.white,
                  fontSize: 42,
                  height: 1.1,
                  fontWeight: FontWeight.w800,
                  letterSpacing: -1)),
          const SizedBox(height: 14),
          Text('Clients, produits et lignes de commande réunis au même endroit.',
              style: TextStyle(color: Colors.white.withOpacity(0.78), fontSize: 16, height: 1.4)),
          const SizedBox(height: 28),
          _feature(Icons.receipt_long, 'Suivi des commandes et de leurs lignes'),
          _feature(Icons.inventory_2_outlined, 'Catalogue produits et alertes de stock'),
          _feature(Icons.insights_outlined, 'Chiffre d’affaires et meilleures ventes'),
        ],
      );

  Widget _feature(IconData icon, String text) => Padding(
        padding: const EdgeInsets.only(bottom: 12),
        child: Row(children: [
          Icon(icon, color: AppColors.amber, size: 20),
          const SizedBox(width: 12),
          Expanded(
              child: Text(text,
                  style: TextStyle(color: Colors.white.withOpacity(0.9), fontSize: 14.5))),
        ]),
      );

  Widget _form() => Container(
        padding: const EdgeInsets.all(28),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(24),
          boxShadow: const [
            BoxShadow(color: Color(0x33000000), blurRadius: 40, offset: Offset(0, 18)),
          ],
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Text('Connexion',
                style: TextStyle(
                    fontSize: 26, fontWeight: FontWeight.w800, color: AppColors.ink)),
            const SizedBox(height: 4),
            const Text('Connectez-vous pour continuer',
                style: TextStyle(color: AppColors.muted)),
            const SizedBox(height: 24),
            TextField(
              controller: login,
              textInputAction: TextInputAction.next,
              decoration: fieldDecoration('Login', icon: Icons.person_outline),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: password,
              obscureText: hide,
              onSubmitted: (_) => submit(),
              decoration: fieldDecoration(
                'Mot de passe',
                icon: Icons.lock_outline,
                suffix: IconButton(
                  tooltip: hide ? 'Afficher' : 'Masquer',
                  icon: Icon(hide ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                      size: 20),
                  onPressed: () => setState(() => hide = !hide),
                ),
              ),
            ),
            if (error != null)
              Container(
                margin: const EdgeInsets.only(top: 14),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.rose.withOpacity(0.08),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(children: [
                  const Icon(Icons.error_outline, color: AppColors.rose, size: 20),
                  const SizedBox(width: 8),
                  Expanded(
                      child: Text(error!,
                          style: const TextStyle(color: AppColors.rose, fontSize: 13.5))),
                ]),
              ),
            const SizedBox(height: 22),
            FilledButton(
              onPressed: busy ? null : submit,
              style: FilledButton.styleFrom(
                backgroundColor: AppColors.navy,
                minimumSize: const Size.fromHeight(52),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
              child: busy
                  ? const SizedBox(
                      width: 22,
                      height: 22,
                      child: CircularProgressIndicator(strokeWidth: 2.4, color: Colors.white))
                  : const Text('Se connecter',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
            ),
          ],
        ),
      );

  @override
  Widget build(BuildContext context) => Scaffold(
        body: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [AppColors.navyDark, AppColors.navy, Color(0xFF0E6F7E)],
            ),
          ),
          child: SafeArea(
            child: LayoutBuilder(builder: (context, box) {
              final wide = box.maxWidth >= 960;
              return Center(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(24),
                  child: wide
                      ? Row(
                          mainAxisSize: MainAxisSize.min,
                          crossAxisAlignment: CrossAxisAlignment.center,
                          children: [
                            SizedBox(width: 380, child: _brandPanel()),
                            const SizedBox(width: 56),
                            SizedBox(width: 400, child: _form()),
                          ],
                        )
                      : Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.shopping_cart_checkout,
                                color: Colors.white, size: 44),
                            const SizedBox(height: 10),
                            const Text('GESTION DES COMMANDES',
                                style: TextStyle(
                                    color: Colors.white,
                                    fontSize: 19,
                                    fontWeight: FontWeight.w800,
                                    letterSpacing: 0.4)),
                            const SizedBox(height: 22),
                            ConstrainedBox(
                                constraints: const BoxConstraints(maxWidth: 420),
                                child: _form()),
                          ],
                        ),
                ),
              );
            }),
          ),
        ),
      );
}
