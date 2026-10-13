class Client {
  final int? id;
  final String nom, prenom, email;
  final int? idRegion;
  final String? nomRegion, nomDep; // remplis par la jointure region -> departement

  const Client({
    this.id,
    required this.nom,
    required this.prenom,
    required this.email,
    this.idRegion,
    this.nomRegion,
    this.nomDep,
  });

  String get nomComplet => '$prenom $nom';

  factory Client.fromMap(Map<String, Object?> m) => Client(
    id: m['idCli'] as int,
    nom: m['nomCli'] as String,
    prenom: m['prenomCli'] as String,
    email: m['email'] as String,
    idRegion: m['idRegion'] as int?,
    nomRegion: m['nomRegion'] as String?,
    nomDep: m['nomDep'] as String?,
  );
}