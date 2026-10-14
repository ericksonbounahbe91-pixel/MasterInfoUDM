import 'package:flutter/material.dart';
import 'pages/login_page.dart';
import 'theme.dart';

void main() => runApp(const GestionCommandeApp());

class GestionCommandeApp extends StatelessWidget {
  const GestionCommandeApp({super.key});

  @override
  Widget build(BuildContext context) => MaterialApp(
        title: 'Gestion des commandes',
        debugShowCheckedModeBanner: false,
        theme: buildTheme(),
        home: const LoginPage(),
      );
}
