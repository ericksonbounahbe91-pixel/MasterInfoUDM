import 'package:flutter/material.dart';
import '../core/theme.dart';

/// Fond dégradé + carte centrée (= layouts/auth.blade.php).
class AuthCard extends StatelessWidget {
  final Widget child;
  const AuthCard({super.key, required this.child});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [Bs.dark, Bs.primary], // linear-gradient(135deg, ...)
          ),
        ),
        child: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 440),
                child: Card(
                  elevation: 12,
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16)),
                  child: Padding(
                    padding: const EdgeInsets.all(32),
                    child: Column(mainAxisSize: MainAxisSize.min, children: [
                      const Icon(Icons.shopping_cart_outlined,
                          size: 40, color: Bs.primary),
                      const SizedBox(height: 8),
                      const Text('Gestion des commandes',
                          style: TextStyle(
                              fontSize: 22, fontWeight: FontWeight.bold)),
                      const SizedBox(height: 24),
                      child,
                    ]),
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}