import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../core/format.dart';
import '../../core/theme.dart';
import '../../core/ui.dart';
import '../../models/client.dart';
import '../../models/commande.dart';
import '../../models/produit.dart';
import '../../repositories/client_repository.dart';
import '../../repositories/commande_repository.dart';
import '../../widgets/app_shell.dart';
import '../../widgets/bs_button.dart';
import '../../widgets/bs_card.dart';
import '../../widgets/page_header.dart';

class CommandeFormPage extends StatefulWidget {
  final int? id; // null = nouvelle commande, sinon modification
  const CommandeFormPage({super.key, this.id});
  @override
  State<CommandeFormPage> createState() => _CommandeFormPageState();
}

class _CommandeFormPageState extends State<CommandeFormPage> {
  final _repo = CommandeRepository();
  final _form = GlobalKey<FormState>();

  List<Client> _clients = [];
  List<Produit> _dispos = [];
  Commande? _cmd;
  int? _idCli;
  DateTime _date = DateTime.now();

  final Map<int, TextEditingController> _qtes = {}; // idProd -> quantité saisie
  final Set<int> _retirer = {}; // cases « Retirer » cochées
  int? _nouveauProd;
  final _nouveauQte = TextEditingController(text: '1');

  bool _ready = false;
  bool _saving = false;
  String? _erreur;

  bool get _edition => widget.id != null;

  @override
  void initState() {
    super.initState();
    _charger();
  }

  @override
  void dispose() {
    for (final c in _qtes.values) {
      c.dispose();
    }
    _nouveauQte.dispose();
    super.dispose();
  }

  Future<void> _charger() async {
    final clients = await ClientRepository().tous();
    if (_edition) {
      final c = await _repo.parId(widget.id!);
      if (c != null) {
        _cmd = c;
        _idCli = c.idCli;
        _date = c.date;
        for (final l in c.lignes) {
          _qtes[l.idProd] = TextEditingController(text: '${l.qte}');
        }
        _dispos = await _repo.produitsDisponibles(c.id);
      }
    }
    if (!mounted) return;
    setState(() {
      _clients = clients;
      _ready = true;
    });
  }

  String _libelle(Client c) =>
      '${c.nom} ${c.prenom} — ${c.nomRegion} (${c.nomDep})';

  Future<void> _choisirDate() async {
    final d = await showDatePicker(
      context: context,
      initialDate: _date,
      firstDate: DateTime(2000),
      lastDate: DateTime(2100),
    );
    if (d != null) setState(() => _date = d);
  }

  String? _vQte(String? v) {
    final t = (v ?? '').trim();
    if (t.isEmpty) return 'Chaque quantité est obligatoire.';
    final n = int.tryParse(t);
    if (n == null) return 'Chaque quantité doit être un nombre entier.';
    if (n < 1) return 'Chaque quantité doit être au moins égale à 1.';
    return null;
  }

  String? _vNouveauQte(String? v) {
    if (_nouveauProd == null) return null; // facultatif
    final t = (v ?? '').trim();
    if (t.isEmpty) return 'Indiquez la quantité du produit ajouté.';
    final n = int.tryParse(t);
    if (n == null) {
      return 'La quantité du produit ajouté doit être un nombre entier.';
    }
    if (n < 1) {
      return 'La quantité du produit ajouté doit être au moins égale à 1.';
    }
    return null;
  }

  Future<void> _enregistrer() async {
    if (!_form.currentState!.validate()) return;
    setState(() {
      _saving = true;
      _erreur = null;
    });
    try {
      if (!_edition) {
        final id = await _repo.creer(idCli: _idCli!, date: _date);
        if (!mounted) return;
        toast(context, 'Commande créée. Ajoutez maintenant ses produits.');
        // On renvoie l'id à la liste, qui ouvrira directement le détail
        if (context.canPop()) {
          context.pop(id);
        } else {
          context.go('/commandes/$id');
        }
      } else {
        await _repo.mettreAJour(
          widget.id!,
          idCli: _idCli!,
          date: _date,
          quantites: {
            for (final e in _qtes.entries) e.key: int.parse(e.value.text.trim()),
          },
          aRetirer: _retirer,
          nouveauProd: _nouveauProd,
          nouveauQte: int.tryParse(_nouveauQte.text.trim()) ?? 1,
        );
        if (!mounted) return;
        toast(context, 'Commande mise à jour avec succès.');
        retour(context, '/commandes');
      }
    } on ErreurMetier catch (e) {
      setState(() => _erreur = e.message); // « Stock insuffisant… »
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  Future<void> _supprimer() async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        content: Text('Supprimer définitivement la commande n° ${widget.id} ?'),
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
    await _repo.supprimer(widget.id!);
    if (!mounted) return;
    toast(context, 'Commande supprimée, stock remis à jour.');
    retour(context, '/commandes');
  }

  @override
  Widget build(BuildContext context) {
    final titre =
    _edition ? 'Modifier la commande n° ${widget.id}' : 'Nouvelle commande';

    if (!_ready) {
      return AppShell(
          title: titre, child: const Center(child: CircularProgressIndicator()));
    }

    final btnRetour = BsButton('← Retour',
        small: true,
        outline: true,
        color: Bs.secondary,
        onPressed: () => retour(context, '/commandes'));

    if (_edition && _cmd == null) {
      return AppShell(
        title: titre,
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          PageHeader(title: titre, trailing: btnRetour),
          const AlertBox.danger(child: Text('Commande introuvable.')),
        ]),
      );
    }

    // Création impossible tant qu'il n'existe aucun client
    if (!_edition && _clients.isEmpty) {
      return AppShell(
        title: titre,
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          PageHeader(title: titre, trailing: btnRetour),
          AlertBox.warning(
            child: Wrap(children: [
              const Text("Aucun client n'existe encore. "),
              GestureDetector(
                onTap: () => context.push('/clients/create'),
                child: const Text("Ajoute d'abord un client",
                    style: TextStyle(
                        fontWeight: FontWeight.bold,
                        decoration: TextDecoration.underline)),
              ),
              const Text('.'),
            ]),
          ),
        ]),
      );
    }

    // --- Carte « Informations » (client + date) ---
    final infos = BsCard(
      header: _edition ? 'Informations de la commande' : null,
      child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
        DropdownButtonFormField<int>(
          value: _idCli,
          isExpanded: true,
          decoration: const InputDecoration(labelText: 'Client'),
          hint: const Text('-- Choisir un client --'),
          items: [
            for (final c in _clients)
              DropdownMenuItem(
                  value: c.id,
                  child: Text(_libelle(c), overflow: TextOverflow.ellipsis)),
          ],
          onChanged: (v) => setState(() => _idCli = v),
          validator: (v) => v == null ? 'Veuillez choisir un client.' : null,
        ),
        const SizedBox(height: 16),
        TextFormField(
          key: ValueKey(_date), // force l'affichage de la nouvelle date
          readOnly: true,
          initialValue: dateFr(_date),
          onTap: _choisirDate,
          decoration: const InputDecoration(
            labelText: 'Date de la commande',
            suffixIcon: Icon(Icons.calendar_today, size: 18),
          ),
        ),
      ]),
    );

    // --- Mode création : un seul bloc ---
    if (!_edition) {
      return AppShell(
        title: titre,
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          PageHeader(title: titre, trailing: btnRetour),
          Align(
            alignment: Alignment.centerLeft,
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 600),
              child: Form(
                key: _form,
                child:
                Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  infos,
                  const SizedBox(height: 16),
                  BsButton('Créer la commande',
                      color: Bs.success,
                      onPressed: _saving ? null : _enregistrer),
                  const SizedBox(height: 8),
                  const Text(
                      'Tu pourras ensuite ajouter les produits de cette commande.',
                      style: TextStyle(color: Bs.muted, fontSize: 13)),
                ]),
              ),
            ),
          ),
        ]),
      );
    }

    // --- Mode modification : produits existants ---
    final lignes = BsCard(
      header: 'Produits de la commande',
      padding: EdgeInsets.zero,
      child: _cmd!.lignes.isEmpty
          ? const Padding(
          padding: EdgeInsets.all(16),
          child: Center(
              child: Text("Aucun produit dans cette commande pour l'instant.",
                  style: TextStyle(color: Bs.muted))))
          : Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: DataTable(
            dataRowMinHeight: 56,
            dataRowMaxHeight: 84,
            columns: const [
              DataColumn(label: Text('Produit')),
              DataColumn(label: Text('Prix unitaire'), numeric: true),
              DataColumn(label: Text('Quantité')),
              DataColumn(label: Text('Retirer')),
            ],
            rows: [
              for (final l in _cmd!.lignes)
                DataRow(cells: [
                  DataCell(Text(l.design)),
                  DataCell(Text(fcfa(l.prix))),
                  DataCell(SizedBox(
                    width: 110,
                    child: TextFormField(
                      controller: _qtes[l.idProd],
                      keyboardType: TextInputType.number,
                      validator: _vQte,
                    ),
                  )),
                  DataCell(Checkbox(
                    value: _retirer.contains(l.idProd),
                    onChanged: (v) => setState(() {
                      v == true
                          ? _retirer.add(l.idProd)
                          : _retirer.remove(l.idProd);
                    }),
                  )),
                ]),
            ],
          ),
        ),
        Container(
          padding: const EdgeInsets.all(12),
          decoration: const BoxDecoration(
            color: Color(0x08000000),
            border: Border(top: BorderSide(color: Bs.border)),
          ),
          child: const Text(
            'Coche « Retirer » pour supprimer un produit de la commande. '
                'Les quantités retirées retournent en stock.',
            style: TextStyle(color: Bs.muted, fontSize: 13),
          ),
        ),
      ]),
    );

    // --- Ajout facultatif d'un produit ---
    final largeur = MediaQuery.sizeOf(context).width >= 768; // col-md-8 / col-md-4
    final choixProduit = DropdownButtonFormField<int?>(
      value: _nouveauProd,
      isExpanded: true,
      decoration: const InputDecoration(labelText: 'Produit'),
      items: [
        const DropdownMenuItem<int?>(
            value: null, child: Text('-- Ne pas ajouter de produit --')),
        for (final p in _dispos)
          DropdownMenuItem<int?>(
              value: p.id,
              child: Text('${p.design} (stock : ${p.stock})',
                  overflow: TextOverflow.ellipsis)),
      ],
      onChanged: (v) => setState(() => _nouveauProd = v),
    );
    final choixQte = TextFormField(
      controller: _nouveauQte,
      keyboardType: TextInputType.number,
      decoration: const InputDecoration(labelText: 'Quantité'),
      validator: _vNouveauQte,
    );
    final ajout = BsCard(
      header: 'Ajouter un produit (facultatif)',
      child: _dispos.isEmpty
          ? const Text(
          'Aucun produit disponible (tous sont déjà dans la commande ou en rupture de stock).',
          style: TextStyle(color: Bs.muted))
          : (largeur
          ? Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Expanded(flex: 8, child: choixProduit),
        const SizedBox(width: 16),
        Expanded(flex: 4, child: choixQte),
      ])
          : Column(children: [
        choixProduit,
        const SizedBox(height: 16),
        choixQte,
      ])),
    );

    // --- Carte de suppression (bordure rouge) ---
    final suppression = Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: Bs.danger),
      ),
      child: Wrap(
        alignment: WrapAlignment.spaceBetween,
        crossAxisAlignment: WrapCrossAlignment.center,
        runSpacing: 12,
        spacing: 12,
        children: [
          const Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text('Supprimer cette commande',
                style: TextStyle(fontWeight: FontWeight.bold)),
            Text('Toutes ses quantités seront remises en stock.',
                style: TextStyle(color: Bs.muted, fontSize: 13)),
          ]),
          BsButton('Supprimer la commande',
              color: Bs.danger, onPressed: _supprimer),
        ],
      ),
    );

    return AppShell(
      title: titre,
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        PageHeader(title: titre, trailing: btnRetour),
        Align(
          alignment: Alignment.centerLeft,
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 800),
            child:
            Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
              Form(
                key: _form,
                child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      if (_erreur != null) ...[
                        AlertBox.danger(child: Text(_erreur!)),
                        const SizedBox(height: 16),
                      ],
                      infos,
                      const SizedBox(height: 24),
                      lignes,
                      const SizedBox(height: 24),
                      ajout,
                      const SizedBox(height: 24),
                      Wrap(spacing: 8, children: [
                        BsButton('Mettre à jour',
                            color: Bs.success,
                            onPressed: _saving ? null : _enregistrer),
                        BsButton('Annuler',
                            color: Bs.secondary,
                            onPressed: () => retour(context, '/commandes')),
                      ]),
                    ]),
              ),
              const SizedBox(height: 24),
              suppression,
            ]),
          ),
        ),
      ]),
    );
  }
}