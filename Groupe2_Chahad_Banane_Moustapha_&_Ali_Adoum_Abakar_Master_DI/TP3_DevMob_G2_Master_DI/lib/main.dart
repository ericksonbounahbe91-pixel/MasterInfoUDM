import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'package:provider/provider.dart';
import 'core/database.dart';
import 'core/theme.dart';
import 'providers/auth_provider.dart';
import 'repositories/auth_repository.dart';
import 'router.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await initializeDateFormatting('fr_FR');
  await AppDatabase.instance; // crée les tables au premier lancement

  final auth = AuthProvider(AuthRepository());
  await auth.init(); // relit la session éventuelle

  runApp(
    ChangeNotifierProvider<AuthProvider>.value(
      value: auth,
      child: App(auth: auth),
    ),
  );
}

class App extends StatefulWidget {
  final AuthProvider auth;
  const App({super.key, required this.auth});
  @override
  State<App> createState() => _AppState();
}

class _AppState extends State<App> {
  // Le routeur est créé une seule fois (sinon la navigation se réinitialise)
  late final GoRouter _router = buildRouter(widget.auth);

  @override
  Widget build(BuildContext context) {
    return MaterialApp.router(
      title: 'Gestion des commandes',
      debugShowCheckedModeBanner: false,
      theme: buildTheme(),
      locale: const Locale('fr', 'FR'),
      supportedLocales: const [Locale('fr', 'FR')],
      localizationsDelegates: GlobalMaterialLocalizations.delegates,
      routerConfig: _router,
    );
  }
}