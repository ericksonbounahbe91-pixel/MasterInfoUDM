import 'package:flutter/foundation.dart';
import '../repositories/auth_repository.dart';

/// État de connexion partagé par toute l'application.
class AuthProvider extends ChangeNotifier {
  final AuthRepository _repo;
  AuthProvider(this._repo);

  AppUser? user;
  bool ready = false; // false tant qu'on n'a pas relu la session au démarrage

  Future<void> init() async {
    user = await _repo.currentUser();
    ready = true;
    notifyListeners();
  }

  Future<void> login(String email, String pwd, bool remember) async {
    user = await _repo.login(email, pwd, remember: remember);
    notifyListeners();
  }

  Future<void> register(String n, String e, String p, String c) async {
    user = await _repo.register(name: n, email: e, password: p, confirmation: c);
    notifyListeners();
  }

  Future<void> logout() async {
    await _repo.logout();
    user = null;
    notifyListeners();
  }

  bool get isLoggedIn => user != null;
}