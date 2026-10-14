import 'package:flutter/material.dart';
import '../api_service.dart';
import '../theme.dart';
import '../widgets/common.dart';

class ClientsPage extends StatefulWidget {
  const ClientsPage({super.key});
  @override
  State<ClientsPage> createState() => _ClientsPageState();
}

class _ClientsPageState extends State<ClientsPage> {
  late Future<List<dynamic>> future;
  String query = '';

  @override
  void initState() {
    super.initState();
    future = api.clients();
  }

  void reload() => setState(() => future = api.clients());

  @override
  Widget build(BuildContext context) => FutureBuilder<List<dynamic>>(
        future: future,
        builder: (c, s) {
          if (s.connectionState == ConnectionState.waiting) return const LoadingView();
          if (s.hasError) return ErrorPanel(error: s.error.toString(), retry: reload);

          final all = s.data!.map((e) => Map<String, dynamic>.from(e)).toList();
          final q = query.trim().toLowerCase();
          final shown = all.where((p) {
            if (q.isEmpty) return true;
            final text = '${p['prenomCli']} ${p['nomCli']} ${p['email'] ?? ''} '
                    '${p['nomRegion'] ?? ''}'
                .toLowerCase();
            return text.contains(q);
          }).toList();

          return RefreshIndicator(
            onRefresh: () async {
              reload();
              await future;
            },
            child: PageList(children: [
              PageHeader(
                title: 'Clients',
                subtitle: '${all.length} client(s) enregistré(s)',
                actions: [
                  IconButton(
                      tooltip: 'Actualiser',
                      onPressed: reload,
                      icon: const Icon(Icons.refresh, color: AppColors.navy)),
                ],
              ),
              SearchField(
                  hint: 'Rechercher par nom, e-mail ou région…',
                  onChanged: (v) => setState(() => query = v)),
              const SizedBox(height: 16),
              if (shown.isEmpty)
                const EmptyState(icon: Icons.search_off, message: 'Aucun client trouvé.')
              else
                ResponsiveWrap(
                  minItemWidth: 330,
                  children: [for (final p in shown) _clientCard(p)],
                ),
            ]),
          );
        },
      );

  Widget _clientCard(Map<String, dynamic> p) {
    final fullName = '${p['prenomCli']} ${p['nomCli']}';
    final nb = toNum(p['nbCommandes']).round();
    return AppCard(
      child: Row(children: [
        InitialsAvatar(fullName, size: 52),
        const SizedBox(width: 14),
        Expanded(
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(fullName,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(fontSize: 15.5, fontWeight: FontWeight.w700)),
            const SizedBox(height: 2),
            Text(p['email']?.toString() ?? 'E-mail non renseigné',
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(color: AppColors.muted, fontSize: 13)),
            const SizedBox(height: 8),
            Wrap(spacing: 6, runSpacing: 6, children: [
              Pill(p['nomRegion']?.toString() ?? 'Région inconnue',
                  color: AppColors.teal, icon: Icons.place_outlined),
              Pill('$nb commande(s)', color: AppColors.navy, icon: Icons.receipt_long),
            ]),
          ]),
        ),
      ]),
    );
  }
}
