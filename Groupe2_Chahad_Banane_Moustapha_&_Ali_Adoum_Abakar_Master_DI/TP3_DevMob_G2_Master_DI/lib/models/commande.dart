import 'ligne_commande.dart';

class Commande {
  final int id;
  final DateTime date;
  final int idCli;
  final String clientNom, nomRegion, nomDep;
  final List<LigneCommande> lignes;

  const Commande({
    required this.id,
    required this.date,
    required this.idCli,
    required this.clientNom,
    required this.nomRegion,
    required this.nomDep,
    this.lignes = const [],
  });

  /// Total = somme(quantité × prix), comme dans la vue Blade
  double get total => lignes.fold(0, (s, l) => s + l.sousTotal);
}