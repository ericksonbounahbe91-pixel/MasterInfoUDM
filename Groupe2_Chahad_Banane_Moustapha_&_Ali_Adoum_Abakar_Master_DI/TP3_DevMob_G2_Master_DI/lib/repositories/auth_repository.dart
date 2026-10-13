import 'dart:convert';
import 'dart:math';
import 'package:crypto/crypto.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../core/database.dart';
import 'client_repository.dart' show ErreurMetier;

class AppUser {
  final int id;
  final String name, email;
  const AppUser(this.id, this.name, this.email);
}

/// Authentification locale : table users + mot de passe salé/haché + session sécurisée.
class AuthRepository {
  final _storage = const FlutterSecureStorage();
  static const _kSession = 'session_user_id';

  // Sel aléatoire + SHA-256 (équivalent du cast 'hashed' de Laravel)
  String _hash(String password, String salt) =>
      sha256.convert(utf8.encode('$salt:$password')).toString();

  String _newSalt() {
    final r = Random.secure();
    return base64Url.encode(List<int>.generate(16, (_) => r.nextInt(256)));
  }

  /// Inscription (≈ AuthController::register)
  Future<AppUser> register({
    required String name,
    required String email,
    required String password,
    required String confirmation,
  }) async {
    email = email.trim().toLowerCase();
    if (password != confirmation) {
      throw ErreurMetier('La confirmation du mot de passe ne correspond pas.');
    }
    final db = await AppDatabase.instance;
    final exists =
    await db.query('users', where: 'email = ?', whereArgs: [email]);
    if (exists.isNotEmpty) {
      throw ErreurMetier('Un compte existe déjà avec cette adresse e-mail.');
    }
    final salt = _newSalt();
    final id = await db.insert('users', {
      'name': name.trim(),
      'email': email,
      'password': '$salt:${_hash(password, salt)}',
      'created_at': DateTime.now().toIso8601String(),
    });
    await _storage.write(key: _kSession, value: '$id'); // connexion directe
    return AppUser(id, name.trim(), email);
  }

  /// Connexion (≈ Auth::attempt). Même message pour e-mail inconnu / mauvais mot de passe.
  Future<AppUser> login(String email, String password,
      {bool remember = false}) async {
    email = email.trim().toLowerCase();
    final db = await AppDatabase.instance;
    final rows =
    await db.query('users', where: 'email = ?', whereArgs: [email]);

    const generique = 'Adresse e-mail ou mot de passe incorrect.';
    if (rows.isEmpty) throw ErreurMetier(generique);

    final stored = (rows.first['password'] as String).split(':');
    if (_hash(password, stored[0]) != stored[1]) throw ErreurMetier(generique);

    final id = rows.first['id'] as int;
    // « Se souvenir de moi » : on ne garde la session que si coché
    if (remember) await _storage.write(key: _kSession, value: '$id');
    return AppUser(id, rows.first['name'] as String, email);
  }

  /// Au démarrage : retrouver l'utilisateur déjà connecté
  Future<AppUser?> currentUser() async {
    final id = await _storage.read(key: _kSession);
    if (id == null) return null;
    final db = await AppDatabase.instance;
    final r =
    await db.query('users', where: 'id = ?', whereArgs: [int.parse(id)]);
    if (r.isEmpty) return null;
    return AppUser(
        r.first['id'] as int, r.first['name'] as String, r.first['email'] as String);
  }

  Future<void> logout() => _storage.delete(key: _kSession);
}