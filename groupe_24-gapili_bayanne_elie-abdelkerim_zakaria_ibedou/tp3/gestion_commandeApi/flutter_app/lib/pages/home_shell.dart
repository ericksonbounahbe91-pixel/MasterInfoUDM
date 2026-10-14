import 'package:flutter/material.dart';
import '../theme.dart';
import 'clients_page.dart';
import 'commandes_page.dart';
import 'dashboard_page.dart';
import 'login_page.dart';
import 'produits_page.dart';

class _Dest {
  final IconData icon;
  final IconData selectedIcon;
  final String label;
  const _Dest(this.icon, this.selectedIcon, this.label);
}

const _destinations = [
  _Dest(Icons.space_dashboard_outlined, Icons.space_dashboard, 'Tableau de bord'),
  _Dest(Icons.inventory_2_outlined, Icons.inventory_2, 'Produits'),
  _Dest(Icons.receipt_long_outlined, Icons.receipt_long, 'Commandes'),
  _Dest(Icons.people_outline, Icons.people, 'Clients'),
];

class HomeShell extends StatefulWidget {
  const HomeShell({super.key});
  @override
  State<HomeShell> createState() => _HomeShellState();
}

class _HomeShellState extends State<HomeShell> {
  int index = 0;

  Widget _page() {
    switch (index) {
      case 1:
        return const ProduitsPage();
      case 2:
        return const CommandesPage();
      case 3:
        return const ClientsPage();
      default:
        return const DashboardPage();
    }
  }

  void _logout() => Navigator.pushAndRemoveUntil(
      context, MaterialPageRoute(builder: (_) => const LoginPage()), (r) => false);

  Widget _sidebar() => Container(
        width: 256,
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [AppColors.navyDark, AppColors.navy],
          ),
        ),
        child: SafeArea(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Padding(
                padding: const EdgeInsets.fromLTRB(20, 26, 20, 22),
                child: Row(children: [
                  Container(
                    width: 42,
                    height: 42,
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.14),
                      borderRadius: BorderRadius.circular(13),
                    ),
                    child: const Icon(Icons.shopping_cart_checkout, color: Colors.white),
                  ),
                  const SizedBox(width: 12),
                  const Expanded(
                    child: Text('Gestion des\ncommandes',
                        style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w800,
                            height: 1.15,
                            fontSize: 16)),
                  ),
                ]),
              ),
              for (var i = 0; i < _destinations.length; i++) _sideItem(i),
              const Spacer(),
              Padding(
                padding: const EdgeInsets.fromLTRB(12, 0, 12, 16),
                child: InkWell(
                  borderRadius: BorderRadius.circular(12),
                  onTap: _logout,
                  child: Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                    child: Row(children: [
                      Icon(Icons.logout, color: Colors.white.withOpacity(0.75), size: 20),
                      const SizedBox(width: 12),
                      Text('Déconnexion',
                          style: TextStyle(color: Colors.white.withOpacity(0.75))),
                    ]),
                  ),
                ),
              ),
            ],
          ),
        ),
      );

  Widget _sideItem(int i) {
    final d = _destinations[i];
    final selected = i == index;
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 2),
      child: Material(
        color: selected ? Colors.white.withOpacity(0.14) : Colors.transparent,
        borderRadius: BorderRadius.circular(12),
        child: InkWell(
          borderRadius: BorderRadius.circular(12),
          onTap: () => setState(() => index = i),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
            child: Row(children: [
              Icon(selected ? d.selectedIcon : d.icon,
                  color: selected ? AppColors.amber : Colors.white70, size: 22),
              const SizedBox(width: 12),
              Text(d.label,
                  style: TextStyle(
                      color: selected ? Colors.white : Colors.white70,
                      fontWeight: selected ? FontWeight.w700 : FontWeight.w500)),
            ]),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final wide = MediaQuery.of(context).size.width >= 900;
    if (wide) {
      return Scaffold(
        body: Row(children: [
          _sidebar(),
          Expanded(child: _page()),
        ]),
      );
    }
    return Scaffold(
      appBar: AppBar(
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.transparent,
        elevation: 0,
        scrolledUnderElevation: 0,
        titleTextStyle: const TextStyle(
            color: AppColors.ink, fontSize: 19, fontWeight: FontWeight.w800),
        title: Row(children: [
          const Icon(Icons.shopping_cart_checkout, color: AppColors.navy, size: 22),
          const SizedBox(width: 10),
          Text(_destinations[index].label),
        ]),
        actions: [
          IconButton(
              tooltip: 'Déconnexion',
              icon: const Icon(Icons.logout, color: AppColors.muted),
              onPressed: _logout),
        ],
        bottom: const PreferredSize(
          preferredSize: Size.fromHeight(1),
          child: Divider(height: 1, color: AppColors.line),
        ),
      ),
      body: _page(),
      bottomNavigationBar: NavigationBar(
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.transparent,
        indicatorColor: AppColors.navy.withOpacity(0.12),
        selectedIndex: index,
        onDestinationSelected: (i) => setState(() => index = i),
        destinations: [
          for (final d in _destinations)
            NavigationDestination(
              icon: Icon(d.icon),
              selectedIcon: Icon(d.selectedIcon, color: AppColors.navy),
              label: d.label == 'Tableau de bord' ? 'Accueil' : d.label,
            ),
        ],
      ),
    );
  }
}
