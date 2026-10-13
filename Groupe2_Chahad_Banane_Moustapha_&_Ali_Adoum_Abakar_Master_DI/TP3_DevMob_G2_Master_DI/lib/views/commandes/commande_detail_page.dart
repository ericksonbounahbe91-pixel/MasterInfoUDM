import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../core/format.dart';
import '../../core/theme.dart';
import '../../core/ui.dart';
import '../../models/commande.dart';
import '../../models/produit.dart';
import '../../repositories/client_repository.dart' show ErreurMetier;
import '../../repositories/commande_repository.dart';
import '../../widgets/app_shell.dart';
import '../../widgets/bs_button.dart';
import '../../widgets/bs_card.dart';
import '../../widgets/page_header.dart';

class CommandeDetailPage extends StatefulWidget {
  final int id;
  const CommandeDetailPage({super.key, required this.id});
  @override
  State<CommandeDetailPage> createState() => _CommandeDetailPageState();
}

class _CommandeDetailPageState extends State<CommandeDetailPage> {
  final _repo = CommandeRepository();
  Commande? _cmd;
  List<Produit> _dispos = [];
  int? _produitChoisi;
  final _qte = TextEditingController(text: '1');
  String? _erreur;
  bool _charge = false;

  @override
  void initState() {
    super.initState();
    _charger();
  }

  @override
  void dispose() {
    _qte.dispose();
    super.dispose();
  }

  Future<void> _charger() async {
    final c = await _repo.parId(widget.id);
    final d =
    c == null ? <Produit>[] : await _repo.produitsDisponibles(widget.id);
    if (mounted) {
      setState(() {
        _cmd = c;
        _dispos = d;
        _charge = true;
      });
    }
  }

  // = confirm('Supprimer définitivement la commande ...') du Blade
  Future<void> _supprimer() async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        content: Text('Supprimer définitivement la commande n° ${widget.id} ? '
            'Les quantités seront remises en stock.'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(context, false),
              child: const Text('Annuler')),
          TextButton(
              onPressed: () => Navigator.pop(context, true),
              child: const Text('Supprimer',
                  style: TextStyle(color: Bs.danger))),
        ],
      ),
    );
    if (ok != true) return;
    await _repo.supprimer(widget.id);
    if (!mounted) return;
    toast(context, 'Commande supprimée, stock remis à jour.');
    retour(context, '/commandes');
  }

  Future<void> _ajouter() async {
    final q = int.tryParse(_qte.text.trim());
    if (_produitChoisi == null) {
      setState(() => _erreur = 'Veuillez choisir un produit.');
      return;
    }
    if (q == null) {
      setState(() => _erreur = 'La quantité doit être un nombre entier.');
      return;
    }
    if (q < 1) {
      setState(() => _erreur = 'La quantité doit être au moins égale à 1.');
      return;
    }
    try {
      await _repo.ajouterProduit(widget.id, _produitChoisi!, q);
      _produitChoisi = null;
      _qte.text = '1';
      _erreur = null;
      await _charger();
      if (mounted) toast(context, 'Produit ajouté à la commande.');
    } on ErreurMetier catch (e) {
      setState(() => _erreur = e.message);
    }
  }

  @override
  Widget build(BuildContext context) {
    final titre = 'Commande n° ${widget.id}';
    if (!_charge) {
      return AppShell(
          title: titre, child: const Center(child: CircularProgressIndicator()));
    }
    final c = _cmd;
    if (c == null) {
      return AppShell(
        title: titre,
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          PageHeader(
            title: titre,
            trailing: BsButton('← Retour aux commandes',
                small: true,
                outline: true,
                color: Bs.secondary,
                onPressed: () => retour(context, '/commandes')),
          ),
          const AlertBox.danger(child: Text('Commande introuvable.')),
        ]),
      );
    }

    final wide = MediaQuery.sizeOf(context).width >= 992;

    final tableau = BsCard(
      header: 'Produits de la commande',
      padding: EdgeInsets.zero,
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: DataTable(
          columns: const [
            DataColumn(label: Text('Produit')),
            DataColumn(label: Text('Prix unitaire'), numeric: true),
            DataColumn(label: Text('Quantité')),
            DataColumn(label: Text('Sous-total'), numeric: true),
            DataColumn(label: Text('Action')),
          ],
          rows: [
            for (final l in c.lignes)
              DataRow(cells: [
                DataCell(Text(l.design)),
                DataCell(Text(fcfa(l.prix))),
                DataCell(Text('${l.qte}')),
                DataCell(Text(fcfa(l.sousTotal))),
                DataCell(BsButton(
                  'Modifier',
                  small: true,
                  color: Bs.warning,
                  textColor: Colors.black,
                  onPressed: () async {
                    await context
                        .push('/commandes/${c.id}/produits/${l.idProd}/edit');
                    _charger();
                  },
                )),
              ]),
            DataRow(
                color: WidgetStateProperty.all(const Color(0x0A000000)),
                cells: [
                  const DataCell(Text('')),
                  const DataCell(Text('')),
                  const DataCell(Text('Total',
                      style: TextStyle(fontWeight: FontWeight.bold))),
                  DataCell(Text(fcfa(c.total),
                      style: const TextStyle(fontWeight: FontWeight.bold))),
                  const DataCell(Text('')),
                ]),
          ],
        ),
      ),
    );

    final formAjout = BsCard(
      header: 'Ajouter un produit',
      child: _dispos.isEmpty
          ? const AlertBox.warning(
          child: Text(
              'Aucun produit disponible (tous sont déjà dans la commande ou en rupture de stock).'))
          : Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
        if (_erreur != null) ...[
          AlertBox.danger(child: Text(_erreur!)),
          const SizedBox(height: 12),
        ],
        DropdownButtonFormField<int>(
          value: _produitChoisi,
          isExpanded: true,
          decoration: const InputDecoration(labelText: 'Produit'),
          hint: const Text('-- Choisir un produit --'),
          items: [
            for (final p in _dispos)
              DropdownMenuItem(
                  value: p.id,
                  child: Text('${p.design} (stock : ${p.stock})',
                      overflow: TextOverflow.ellipsis)),
          ],
          onChanged: (v) => setState(() => _produitChoisi = v),
        ),
        const SizedBox(height: 16),
        TextField(
          controller: _qte,
          keyboardType: TextInputType.number,
          decoration: const InputDecoration(labelText: 'Quantité'),
        ),
        const SizedBox(height: 16),
        BsButton('Ajouter à la commande', onPressed: _ajouter),
      ]),
    );

    return AppShell(
      title: titre,
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        PageHeader(
          title: titre,
          trailing: Wrap(spacing: 8, runSpacing: 8, children: [
            BsButton('← Retour aux commandes',
                small: true,
                outline: true,
                color: Bs.secondary,
                onPressed: () => retour(context, '/commandes')),
            BsButton('Supprimer la commande',
                small: true, color: Bs.danger, onPressed: _supprimer),
          ]),
        ),
        Text.rich(TextSpan(
          style: const TextStyle(color: Bs.muted),
          children: [
            TextSpan(text: 'Passée le ${dateFr(c.date)} par '),
            TextSpan(
                text: c.clientNom,
                style: const TextStyle(fontWeight: FontWeight.bold)),
          ],
        )),
        const SizedBox(height: 16),
        // col-lg-7 / col-lg-5 : côte à côte sur large, empilés sinon
        wide
            ? Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Expanded(flex: 7, child: tableau),
          const SizedBox(width: 24),
          Expanded(flex: 5, child: formAjout),
        ])
            : Column(children: [tableau, const SizedBox(height: 24), formAjout]),
      ]),
    );
  }
}