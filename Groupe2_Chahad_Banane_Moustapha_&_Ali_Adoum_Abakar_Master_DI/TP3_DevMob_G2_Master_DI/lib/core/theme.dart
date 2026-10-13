import 'package:flutter/material.dart';

/// Palette Bootstrap 5.3 : mêmes valeurs que sur le site web.
class Bs {
  static const primary = Color(0xFF0D6EFD);
  static const success = Color(0xFF198754);
  static const danger = Color(0xFFDC3545);
  static const warning = Color(0xFFFFC107);
  static const info = Color(0xFF0DCAF0);
  static const secondary = Color(0xFF6C757D);
  static const dark = Color(0xFF212529);
  static const light = Color(0xFFF8F9FA);
  static const muted = Color(0xFF6C757D);
  static const border = Color(0xFFDEE2E6);
  static const navText = Color(0xFFCFD4DA);
}

ThemeData buildTheme() {
  return ThemeData(
    useMaterial3: false, // le Material 2 ressemble davantage à Bootstrap
    scaffoldBackgroundColor: Bs.light,
    colorScheme: ColorScheme.fromSeed(seedColor: Bs.primary).copyWith(
      primary: Bs.primary,
      error: Bs.danger,
    ),
    appBarTheme: const AppBarTheme(
      backgroundColor: Bs.dark,
      foregroundColor: Colors.white,
    ),
    inputDecorationTheme: InputDecorationTheme(
      isDense: true,
      filled: true,
      fillColor: Colors.white,
      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(6),
        borderSide: const BorderSide(color: Bs.border),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(6),
        borderSide: const BorderSide(color: Bs.border),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(6),
        borderSide: const BorderSide(color: Bs.primary, width: 1.5),
      ),
    ),
    cardTheme: CardThemeData(
      color: Colors.white,
      elevation: 1.5, // .shadow-sm
      margin: EdgeInsets.zero,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(6),
        side: const BorderSide(color: Color(0x20000000)),
      ),
    ),
  );
}