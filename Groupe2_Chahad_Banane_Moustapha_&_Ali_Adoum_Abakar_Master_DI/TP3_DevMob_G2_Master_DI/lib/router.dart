import 'package:go_router/go_router.dart';
import 'providers/auth_provider.dart';
import 'views/auth/login_page.dart';
import 'views/auth/register_page.dart';
import 'views/clients/client_form_page.dart';
import 'views/clients/clients_page.dart';
import 'views/commandes/commande_detail_page.dart';
import 'views/commandes/commande_form_page.dart';
import 'views/commandes/commandes_page.dart';
import 'views/commandes/ligne_edit_page.dart';
import 'views/dashboard/dashboard_page.dart';
import 'views/produits/produit_form_page.dart';
import 'views/produits/produits_page.dart';

/// Équivalent de routes/web.php (+ middlewares guest et auth).
GoRouter buildRouter(AuthProvider auth) {
  return GoRouter(
    initialLocation: '/',
    // Se réévalue à chaque connexion / déconnexion
    refreshListenable: auth,

    // = middleware('guest') et middleware('auth')
    redirect: (context, state) {
      if (!auth.ready) return null;
      final onAuthPage = state.matchedLocation == '/login' ||
          state.matchedLocation == '/register';
      if (!auth.isLoggedIn && !onAuthPage) return '/login';
      if (auth.isLoggedIn && onAuthPage) return '/';
      return null;
    },

    routes: [
      GoRoute(path: '/login', builder: (_, __) => const LoginPage()),
      GoRoute(path: '/register', builder: (_, __) => const RegisterPage()),
      GoRoute(path: '/', builder: (_, __) => const DashboardPage()),

      // Produits ('create' avant les routes à paramètre, comme dans web.php)
      GoRoute(
        path: '/produits',
        builder: (_, __) => const ProduitsPage(),
        routes: [
          GoRoute(path: 'create', builder: (_, __) => const ProduitFormPage()),
          GoRoute(
            path: ':id/edit',
            builder: (_, s) =>
                ProduitFormPage(id: int.parse(s.pathParameters['id']!)),
          ),
        ],
      ),

      // Clients
      GoRoute(
        path: '/clients',
        builder: (_, __) => const ClientsPage(),
        routes: [
          GoRoute(path: 'create', builder: (_, __) => const ClientFormPage()),
          GoRoute(
            path: ':id/edit',
            builder: (_, s) =>
                ClientFormPage(id: int.parse(s.pathParameters['id']!)),
          ),
        ],
      ),

      // Commandes
      GoRoute(
        path: '/commandes',
        builder: (_, __) => const CommandesPage(),
        routes: [
          GoRoute(path: 'create', builder: (_, __) => const CommandeFormPage()),
          GoRoute(
            path: ':id',
            builder: (_, s) =>
                CommandeDetailPage(id: int.parse(s.pathParameters['id']!)),
            routes: [
              GoRoute(
                path: 'edit',
                builder: (_, s) =>
                    CommandeFormPage(id: int.parse(s.pathParameters['id']!)),
              ),
              GoRoute(
                path: 'produits/:idProd/edit',
                builder: (_, s) => LigneEditPage(
                  idCmd: int.parse(s.pathParameters['id']!),
                  idProd: int.parse(s.pathParameters['idProd']!),
                ),
              ),
            ],
          ),
        ],
      ),
    ],
  );
}