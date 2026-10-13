import 'package:flutter/material.dart';
import '../core/theme.dart';

/// Carte d'indicateur du dashboard : bordure colorée (border-primary, etc.)
class StatCard extends StatelessWidget {
  final String label;
  final String value;
  final String? unit;
  final Color color;

  const StatCard({
    super.key,
    required this.label,
    required this.value,
    this.unit,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: color),
        boxShadow: const [BoxShadow(color: Color(0x14000000), blurRadius: 3)],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: const TextStyle(color: Bs.muted, fontSize: 13)),
          const SizedBox(height: 4),
          Text.rich(TextSpan(children: [
            TextSpan(
                text: value,
                style:
                const TextStyle(fontSize: 24, fontWeight: FontWeight.bold)),
            if (unit != null)
              TextSpan(text: ' $unit', style: const TextStyle(fontSize: 13)),
          ])),
        ],
      ),
    );
  }
}