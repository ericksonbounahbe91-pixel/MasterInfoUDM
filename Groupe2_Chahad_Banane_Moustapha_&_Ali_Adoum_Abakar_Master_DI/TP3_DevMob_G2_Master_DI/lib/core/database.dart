import 'package:path/path.dart';
import 'package:sqflite/sqflite.dart';

/// Base SQLite locale (remplace MySQL + les migrations Laravel).
class AppDatabase {
  static Database? _db;

  /// Réservé aux tests : permet d'utiliser une base en mémoire.
  static String? overridePath;

  /// À incrémenter à chaque évolution du schéma (= nouvelle migration).
  static const int _version = 1;

  static Future<Database> get instance async {
    if (_db != null) return _db!;
    final path =
        overridePath ?? join(await getDatabasesPath(), 'ligne_commandes.db');
    _db = await openDatabase(
      path,
      version: _version,
      // SQLite n'applique les clés étrangères que si on les active
      onConfigure: (db) => db.execute('PRAGMA foreign_keys = ON'),
      onCreate: (db, version) async {
        await _createSchema(db);
        if (overridePath == null) await _seed(db); // pas de seed pendant les tests
      },
      onUpgrade: _upgrade,
    );
    return _db!;
  }

  /// Ferme la base (utilisé par les tests).
  static Future<void> reset() async {
    await _db?.close();
    _db = null;
  }

  static Future<void> _createSchema(Database db) async {
    await db.execute('''
      CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        created_at TEXT NOT NULL
      )''');
    await db.execute('''
      CREATE TABLE departement (
        idDep INTEGER PRIMARY KEY AUTOINCREMENT,
        nomDep TEXT NOT NULL
      )''');
    await db.execute('''
      CREATE TABLE region (
        idRegion INTEGER PRIMARY KEY AUTOINCREMENT,
        nomRegion TEXT NOT NULL,
        idDep INTEGER NOT NULL,
        FOREIGN KEY (idDep) REFERENCES departement(idDep) ON DELETE RESTRICT
      )''');
    await db.execute('''
      CREATE TABLE client (
        idCli INTEGER PRIMARY KEY AUTOINCREMENT,
        nomCli TEXT NOT NULL,
        prenomCli TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        photo TEXT,
        idRegion INTEGER NOT NULL,
        FOREIGN KEY (idRegion) REFERENCES region(idRegion) ON DELETE RESTRICT
      )''');
    await db.execute('''
      CREATE TABLE produit (
        idProd INTEGER PRIMARY KEY AUTOINCREMENT,
        designProd TEXT NOT NULL,
        qteStock INTEGER NOT NULL DEFAULT 0 CHECK (qteStock >= 0),
        prixUProd REAL NOT NULL,
        photoProd TEXT
      )''');
    await db.execute('''
      CREATE TABLE commande (
        idCmd INTEGER PRIMARY KEY AUTOINCREMENT,
        dateCmd TEXT NOT NULL,
        idCli INTEGER NOT NULL,
        FOREIGN KEY (idCli) REFERENCES client(idCli) ON DELETE RESTRICT
      )''');
    await db.execute('CREATE INDEX idx_commande_date ON commande(dateCmd)');
    await db.execute('''
      CREATE TABLE ligneCommande (
        idCmd INTEGER NOT NULL,
        idProd INTEGER NOT NULL,
        qteCmd INTEGER NOT NULL CHECK (qteCmd >= 1),
        PRIMARY KEY (idCmd, idProd),
        FOREIGN KEY (idCmd) REFERENCES commande(idCmd) ON DELETE CASCADE,
        FOREIGN KEY (idProd) REFERENCES produit(idProd) ON DELETE RESTRICT
      )''');
  }

  /// Jeu de données de démonstration (= php artisan migrate --seed)
  static Future<void> _seed(Database db) async {
    final dep = await db.insert('departement', {'nomDep': 'Chari-Baguirmi'});
    final reg =
    await db.insert('region', {'nomRegion': "N'Djamena", 'idDep': dep});
    await db.insert('client', {
      'nomCli': 'Adoum',
      'prenomCli': 'Ali',
      'email': 'ali@example.com',
      'idRegion': reg,
    });
    await db.insert('produit',
        {'designProd': 'Riz 25 kg', 'qteStock': 50, 'prixUProd': 18000});
    await db.insert('produit',
        {'designProd': 'Huile 5 L', 'qteStock': 15, 'prixUProd': 7500});
    await db.insert('produit',
        {'designProd': 'Sucre 10 kg', 'qteStock': 30, 'prixUProd': 9500});
  }

  /// Migrations : on n'édite jamais une migration déjà livrée, on en ajoute une.
  static Future<void> _upgrade(
      Database db, int oldVersion, int newVersion) async {
    // Exemple pour une future version 2 :
    // if (oldVersion < 2) {
    //   await db.execute('ALTER TABLE produit ADD COLUMN categorie TEXT');
    // }
  }
}