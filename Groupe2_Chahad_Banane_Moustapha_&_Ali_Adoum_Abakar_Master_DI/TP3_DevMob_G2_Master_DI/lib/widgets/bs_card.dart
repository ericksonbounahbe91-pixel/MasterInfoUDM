import 'package:flutter/material.dart';
import '../core/theme.dart';

/// Carte Bootstrap : en-tête gras optionnel + corps.
class BsCard extends StatelessWidget {
  final String? header;
  final Widget child;
  final EdgeInsets padding;

  const BsCard({
    super.key,
    this.header,
    required this.child,
    this.padding = const EdgeInsets.all(16),
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          if (header != null)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: const BoxDecoration(
                color: Color(0x08000000), // .card-header
                border: Border(bottom: BorderSide(color: Bs.border)),
              ),
              child: Text(header!,
                  style: const TextStyle(fontWeight: FontWeight.bold)),
            ),
          Padding(padding: padding, child: child),
        ],
      ),
    );
  }
}