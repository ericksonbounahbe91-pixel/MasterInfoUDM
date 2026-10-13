import 'package:flutter/material.dart';

/// En-tête de page : titre h3 à gauche, bouton d'action à droite
/// (équivalent de « d-flex justify-content-between align-items-center mb-3 »).
class PageHeader extends StatelessWidget {
  final String title;
  final Widget? trailing;
  const PageHeader({super.key, required this.title, this.trailing});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: Wrap(
        alignment: WrapAlignment.spaceBetween,
        crossAxisAlignment: WrapCrossAlignment.center,
        spacing: 8,
        runSpacing: 8,
        children: [
          Text(title,
              style: const TextStyle(fontSize: 28, fontWeight: FontWeight.w500)),
          if (trailing != null) trailing!,
        ],
      ),
    );
  }
}