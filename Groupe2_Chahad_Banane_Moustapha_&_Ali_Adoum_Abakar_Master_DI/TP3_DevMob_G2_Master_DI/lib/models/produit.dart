class Produit {
  final int? id;
  final String design;
  final int stock;
  final double prix;

  const Produit({
    this.id,
    required this.design,
    required this.stock,
    required this.prix,
  });

  factory Produit.fromMap(Map<String, Object?> m) => Produit(
    id: m['idProd'] as int,
    design: m['designProd'] as String,
    stock: m['qteStock'] as int,
    prix: (m['prixUProd'] as num).toDouble(),
  );

  Map<String, Object?> toMap() =>
      {'designProd': design, 'qteStock': stock, 'prixUProd': prix};
}