class LigneCommande {
  final int idProd;
  final String design;
  final double prix;
  final int qte;
  final int stockActuel; // stock restant du produit

  const LigneCommande({
    required this.idProd,
    required this.design,
    required this.prix,
    required this.qte,
    required this.stockActuel,
  });

  double get sousTotal => qte * prix;

  /// Quantité maximale possible = stock disponible + quantité déjà réservée
  int get qteMax => stockActuel + qte;
}