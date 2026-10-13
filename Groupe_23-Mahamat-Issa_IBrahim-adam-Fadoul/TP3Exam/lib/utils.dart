import 'package:flutter/material.dart';

num toNum(dynamic v) => v is num ? v : (num.tryParse('$v') ?? 0);
int toInt(dynamic v) => toNum(v).toInt();

// 18000 -> "18 000 FCFA"
String fcfa(dynamic v) {
  final s = toNum(v).round().toString();
  final b = StringBuffer();
  for (var i = 0; i < s.length; i++) {
    b.write(s[i]);
    final reste = s.length - i - 1;
    if (reste > 0 && reste % 3 == 0) b.write(' ');
  }
  return '$b FCFA';
}

String plain(num n) => n == n.roundToDouble() ? n.round().toString() : n.toString();

// "2026-10-07" -> "07/10/2026"
String dateFr(String iso) {
  final p = iso.split('-');
  return p.length == 3 ? '${p[2]}/${p[1]}/${p[0]}' : iso;
}

void snack(BuildContext context, String message) {
  ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
}

// Affiche un chargement, une erreur avec bouton « Réessayer », ou le contenu
class Chargement<T> extends StatelessWidget {
  final Future<T> future;
  final Widget Function(T data) builder;
  final VoidCallback onRetry;

  const Chargement({
    super.key,
    required this.future,
    required this.builder,
    required this.onRetry,
  });

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<T>(
      future: future,
      builder: (context, snap) {
        if (snap.connectionState != ConnectionState.done) {
          return const Center(child: CircularProgressIndicator());
        }
        if (snap.hasError) {
          return Center(
            child: Padding(
              padding: const EdgeInsets.all(24),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text('${snap.error}', textAlign: TextAlign.center),
                  const SizedBox(height: 12),
                  FilledButton(onPressed: onRetry, child: const Text('Réessayer')),
                ],
              ),
            ),
          );
        }
        return builder(snap.data as T);
      },
    );
  }
}

// Barres horizontales simples (éléments : {label, ca})
class BarList extends StatelessWidget {
  final List<dynamic> items;
  const BarList(this.items, {super.key});

  @override
  Widget build(BuildContext context) {
    if (items.isEmpty) return const Text('Aucune donnée.');
    double max = 0;
    for (final e in items) {
      final v = toNum(e['ca']).toDouble();
      if (v > max) max = v;
    }
    return Column(
      children: [
        for (final e in items)
          Padding(
            padding: const EdgeInsets.symmetric(vertical: 4),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Flexible(child: Text('${e['label']}', overflow: TextOverflow.ellipsis)),
                    const SizedBox(width: 8),
                    Text(fcfa(e['ca']), style: const TextStyle(fontWeight: FontWeight.bold)),
                  ],
                ),
                const SizedBox(height: 3),
                LinearProgressIndicator(
                  value: max == 0 ? 0 : toNum(e['ca']).toDouble() / max,
                  minHeight: 8,
                ),
              ],
            ),
          ),
      ],
    );
  }
}

class Carte extends StatelessWidget {
  final String titre;
  final Widget child;
  const Carte(this.titre, this.child, {super.key});

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(titre, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
            const SizedBox(height: 10),
            child,
          ],
        ),
      ),
    );
  }
}
