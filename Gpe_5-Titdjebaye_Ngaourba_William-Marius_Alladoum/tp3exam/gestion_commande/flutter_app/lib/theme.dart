import 'package:flutter/material.dart';

/// Palette : bleu nuit (identité), turquoise (action positive),
/// ambre (alerte douce), rose (danger).
class AppColors {
  static const navy = Color(0xFF123B6D);
  static const navyDark = Color(0xFF0B2547);
  static const teal = Color(0xFF12A594);
  static const amber = Color(0xFFF2A93B);
  static const rose = Color(0xFFE5484D);
  static const bg = Color(0xFFF2F5F9);
  static const ink = Color(0xFF16233A);
  static const muted = Color(0xFF6B7A90);
  static const line = Color(0xFFE3E8EF);
}

ThemeData buildTheme() {
  final scheme = ColorScheme.fromSeed(
    seedColor: AppColors.navy,
    primary: AppColors.navy,
    secondary: AppColors.teal,
    surface: Colors.white,
    brightness: Brightness.light,
  );
  return ThemeData(
    useMaterial3: true,
    colorScheme: scheme,
    scaffoldBackgroundColor: AppColors.bg,
    visualDensity: VisualDensity.adaptivePlatformDensity,
  );
}

/// Champ de saisie commun (fond clair, coins arrondis).
InputDecoration fieldDecoration(String? label,
    {IconData? icon, Widget? suffix, String? hint, Color fill = const Color(0xFFF7F9FC)}) {
  OutlineInputBorder border(Color c, [double w = 1]) => OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: BorderSide(color: c, width: w),
      );
  return InputDecoration(
    labelText: label,
    hintText: hint,
    prefixIcon: icon == null ? null : Icon(icon, size: 20),
    suffixIcon: suffix,
    filled: true,
    fillColor: fill,
    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
    border: border(AppColors.line),
    enabledBorder: border(AppColors.line),
    focusedBorder: border(AppColors.navy, 1.6),
    errorBorder: border(AppColors.rose),
    focusedErrorBorder: border(AppColors.rose, 1.6),
  );
}

// ----------------------------------------------------------------- formats

/// Convertit une valeur JSON (nombre ou texte) en nombre.
num toNum(dynamic v) => num.tryParse(v.toString()) ?? 0;

/// 1 250 000 -> « 1,3 M » ; 45 000 -> « 45 k »
String compact(num n) {
  if (n >= 1000000) return '${(n / 1000000).toStringAsFixed(1).replaceAll('.', ',')} M';
  if (n >= 1000) return '${(n / 1000).round()} k';
  return n.round().toString();
}

/// 382500 -> « 382 500 FCFA »
String money(dynamic value) {
  final n = (double.tryParse(value.toString()) ?? 0).round();
  final digits = n.abs().toString();
  final buf = StringBuffer();
  for (var i = 0; i < digits.length; i++) {
    if (i > 0 && (digits.length - i) % 3 == 0) buf.write('\u00A0');
    buf.write(digits[i]);
  }
  return '${n < 0 ? '-' : ''}${buf.toString()}\u00A0FCFA';
}

/// "2026-10-13 10:15:00" -> "13/10/2026"
String shortDate(dynamic value) {
  final d = DateTime.tryParse(value.toString().replaceFirst(' ', 'T'));
  if (d == null) return value.toString();
  String two(int n) => n.toString().padLeft(2, '0');
  return '${two(d.day)}/${two(d.month)}/${d.year}';
}

const _moisCourts = [
  'janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin',
  'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'
];

/// "2026-10" -> "oct."
String moisCourt(dynamic value) {
  final parts = value.toString().split('-');
  if (parts.length < 2) return value.toString();
  final m = int.tryParse(parts[1]) ?? 0;
  return (m >= 1 && m <= 12) ? _moisCourts[m - 1] : value.toString();
}

/// Initiales (ex. « Ali Mahamat » -> « AM »).
String initials(String name) {
  final p = name.trim().split(RegExp(r'\s+')).where((e) => e.isNotEmpty).toList();
  if (p.isEmpty) return '?';
  if (p.length == 1) return p.first.substring(0, 1).toUpperCase();
  return (p.first.substring(0, 1) + p.last.substring(0, 1)).toUpperCase();
}

/// Couleur stable à partir d'un texte (pour les avatars).
Color colorFor(String text) {
  const palette = [
    Color(0xFF123B6D), Color(0xFF12A594), Color(0xFF7C5CBF),
    Color(0xFFE07A3F), Color(0xFF2F80C0), Color(0xFFC2506B),
    Color(0xFF5B8C3A), Color(0xFF8A6D3B),
  ];
  var h = 0;
  for (final c in text.codeUnits) {
    h = (h * 31 + c) & 0x7fffffff;
  }
  return palette[h % palette.length];
}

/// Icône adaptée au nom du produit.
IconData iconForProduct(String name) {
  final n = name.toLowerCase();
  bool has(List<String> keys) => keys.any(n.contains);
  if (has(['ordinateur', 'laptop', 'pc '])) return Icons.laptop_mac;
  if (has(['souris'])) return Icons.mouse;
  if (has(['clavier'])) return Icons.keyboard;
  if (has(['clé usb', 'cle usb'])) return Icons.usb;
  if (has(['écran', 'ecran', 'moniteur'])) return Icons.desktop_windows;
  if (has(['imprimante'])) return Icons.print;
  if (has(['casque'])) return Icons.headphones;
  if (has(['disque'])) return Icons.storage;
  if (has(['webcam', 'caméra', 'camera'])) return Icons.videocam;
  if (has(['routeur', 'wi-fi', 'wifi'])) return Icons.router;
  if (has(['hdmi', 'câble', 'cable'])) return Icons.cable;
  if (has(['chargeur'])) return Icons.battery_charging_full;
  if (has(['tablette'])) return Icons.tablet_mac;
  if (has(['onduleur', 'multiprise'])) return Icons.power;
  if (has(['carte mémoire', 'carte memoire'])) return Icons.sd_card;
  if (has(['sac'])) return Icons.backpack;
  if (has(['enceinte', 'haut-parleur'])) return Icons.speaker;
  if (has(['smartphone', 'téléphone', 'telephone'])) return Icons.smartphone;
  return Icons.inventory_2;
}
