import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'theme.dart';

/// Message flash en bas d'écran (équivalent de session('success') du web).
void toast(BuildContext context, String message, {bool erreur = false}) {
  ScaffoldMessenger.of(context)
    ..hideCurrentSnackBar()
    ..showSnackBar(SnackBar(
      content: Text(message),
      backgroundColor: erreur ? Bs.danger : Bs.success,
    ));
}

/// Retour à l'écran précédent, ou vers [secours] si l'écran a été ouvert directement.
void retour(BuildContext context, String secours) {
  if (context.canPop()) {
    context.pop();
  } else {
    context.go(secours);
  }
}

/// Bloc d'alerte Bootstrap (.alert-danger / .alert-warning).
class AlertBox extends StatelessWidget {
  final Widget child;
  final Color background;
  final Color foreground;

  const AlertBox.danger({super.key, required this.child})
      : background = const Color(0xFFF8D7DA),
        foreground = const Color(0xFF842029);

  const AlertBox.warning({super.key, required this.child})
      : background = const Color(0xFFFFF3CD),
        foreground = const Color(0xFF664D03);

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: background,
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: Colors.black12),
      ),
      child: DefaultTextStyle(
        style: TextStyle(color: foreground, fontSize: 14),
        child: child,
      ),
    );
  }
}