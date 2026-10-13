import 'package:flutter/material.dart';
import '../core/theme.dart';

/// Bouton Bootstrap : .btn-primary / .btn-success / .btn-outline-secondary / .btn-sm
class BsButton extends StatelessWidget {
  final String label;
  final VoidCallback? onPressed;
  final Color color;
  final Color textColor;
  final bool small;
  final bool outline;

  const BsButton(
      this.label, {
        super.key,
        required this.onPressed,
        this.color = Bs.primary,
        this.textColor = Colors.white,
        this.small = false,
        this.outline = false,
      });

  @override
  Widget build(BuildContext context) {
    final pad = EdgeInsets.symmetric(
        horizontal: small ? 10 : 16, vertical: small ? 6 : 12);
    final shape = RoundedRectangleBorder(borderRadius: BorderRadius.circular(6));
    final text = Text(label, style: TextStyle(fontSize: small ? 13 : 15));

    if (outline) {
      return OutlinedButton(
        style: OutlinedButton.styleFrom(
          foregroundColor: color,
          side: BorderSide(color: color),
          padding: pad,
          shape: shape,
        ),
        onPressed: onPressed,
        child: text,
      );
    }
    return ElevatedButton(
      style: ElevatedButton.styleFrom(
        backgroundColor: color,
        foregroundColor: textColor,
        elevation: 0,
        padding: pad,
        shape: shape,
      ),
      onPressed: onPressed,
      child: text,
    );
  }
}