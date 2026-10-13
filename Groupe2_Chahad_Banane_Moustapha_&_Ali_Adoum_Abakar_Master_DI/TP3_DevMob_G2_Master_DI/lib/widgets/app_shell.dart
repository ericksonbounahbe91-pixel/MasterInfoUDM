import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../core/theme.dart';
import '../providers/auth_provider.dart';

class _NavItem {
  final String label, route;
  final IconData icon;
  const _NavItem(this.label, this.route, this.icon);
}

const _items = [
  _NavItem('Tableau de bord', '/', Icons.speed),
  _NavItem('Clients', '/clients', Icons.people_outline),
  _NavItem('Produits', '/produits', Icons.inventory_2_outlined),
  _NavItem('Commandes', '/commandes', Icons.shopping_cart_outlined),
];

/// Équivalent de layouts/app.blade.php : sidebar + zone de contenu.
class AppShell extends StatelessWidget {
  final String title;
  final Widget child;
  final List<Widget> actions;

  const AppShell({
    super.key,
    required this.title,
    required this.child,
    this.actions = const [],
  });

  @override
  Widget build(BuildContext context) {
    // Même seuil que le @media (min-width: 992px) du site web
    final wide = MediaQuery.sizeOf(context).width >= 992;
    final path = GoRouterState.of(context).uri.path;

    final Widget scaffold = wide
        ? Scaffold(
      body: Row(children: [
        SizedBox(width: 270, child: _Sidebar()),
        Expanded(
          child: SingleChildScrollView(
              padding: const EdgeInsets.all(24), child: child),
        ),
      ]),
    )
        : Scaffold(
      appBar: AppBar(
        backgroundColor: Bs.dark,
        title: const Text('Gestion des commandes',
            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
        actions: actions,
      ),
      drawer: Drawer(backgroundColor: Bs.dark, child: _Sidebar()),
      body: SingleChildScrollView(
          padding: const EdgeInsets.all(16), child: child),
    );

    // Bouton retour Android : depuis une section (Clients, Produits…), on revient
    // au tableau de bord ; depuis le tableau de bord, on quitte l'application.
    return PopScope(
      canPop: path == '/' || context.canPop(),
      onPopInvokedWithResult: (didPop, _) {
        if (!didPop) context.go('/');
      },
      child: scaffold,
    );
  }
}

class _Sidebar extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    final current = GoRouterState.of(context).uri.path;
    final user = context.watch<AuthProvider>().user;

    bool active(String route) =>
        route == '/' ? current == '/' : current.startsWith(route);

    return Container(
      color: Bs.dark,
      child: SafeArea(
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          const Padding(
            padding: EdgeInsets.all(16),
            child: Text('Gestion des commandes',
                style: TextStyle(
                    color: Colors.white,
                    fontSize: 22,
                    fontWeight: FontWeight.bold)),
          ),
          // Les liens défilent si l'écran est trop bas (téléphone en paysage)
          Expanded(
            child: SingleChildScrollView(
              child: Column(children: [
                for (final it in _items)
                  Padding(
                    padding:
                    const EdgeInsets.symmetric(horizontal: 12, vertical: 2),
                    child: Material(
                      color: active(it.route) ? Bs.primary : Colors.transparent,
                      borderRadius: BorderRadius.circular(6),
                      child: InkWell(
                        borderRadius: BorderRadius.circular(6),
                        hoverColor: Colors.white10,
                        onTap: () {
                          if (Scaffold.maybeOf(context)?.isDrawerOpen ?? false) {
                            Navigator.pop(context); // ferme le menu
                          }
                          context.go(it.route);
                        },
                        child: Padding(
                          padding: const EdgeInsets.all(14),
                          child: Row(children: [
                            Icon(it.icon,
                                color:
                                active(it.route) ? Colors.white : Bs.navText),
                            const SizedBox(width: 12),
                            Text(it.label,
                                style: TextStyle(
                                    fontSize: 18,
                                    color: active(it.route)
                                        ? Colors.white
                                        : Bs.navText)),
                          ]),
                        ),
                      ),
                    ),
                  ),
              ]),
            ),
          ),
          const Divider(color: Bs.secondary),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
            child: Row(children: [
              const Icon(Icons.account_circle_outlined,
                  color: Colors.white54, size: 20),
              const SizedBox(width: 8),
              Expanded(
                child: Text(user?.name ?? '',
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(color: Colors.white54)),
              ),
            ]),
          ),
          Padding(
            padding: const EdgeInsets.all(16),
            child: SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                style: OutlinedButton.styleFrom(
                    foregroundColor: Colors.white,
                    side: const BorderSide(color: Colors.white54)),
                icon: const Icon(Icons.logout, size: 18),
                label: const Text('Déconnexion'),
                onPressed: () => context.read<AuthProvider>().logout(),
              ),
            ),
          ),
        ]),
      ),
    );
  }
}