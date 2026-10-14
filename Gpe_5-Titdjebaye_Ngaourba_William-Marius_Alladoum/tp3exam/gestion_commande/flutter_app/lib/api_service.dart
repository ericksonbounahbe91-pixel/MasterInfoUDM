import 'dart:convert';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:http/http.dart' as http;

class ApiService {
  // Navigateur (Flutter web) : localhost.
  // Émulateur Android : 10.0.2.2 pointe vers le PC hôte.
  // Téléphone réel : remplacez par l'adresse IP locale du PC (ex. 192.168.1.20).
  static const String _mobileHost = '10.0.2.2';
  static String get baseUrl => kIsWeb
      ? 'http://localhost/gestion_commande_api'
      : 'http://$_mobileHost/gestion_commande_api';

  Future<dynamic> _get(String endpoint) async {
    final response = await http.get(Uri.parse('$baseUrl/$endpoint'));
    return _decode(response);
  }

  Future<dynamic> _post(String endpoint, Map<String, dynamic> data) async {
    final response = await http.post(
      Uri.parse('$baseUrl/$endpoint'),
      headers: {'Content-Type': 'application/json; charset=UTF-8'},
      body: jsonEncode(data),
    );
    return _decode(response);
  }

  dynamic _decode(http.Response response) {
    final dynamic json = jsonDecode(utf8.decode(response.bodyBytes));
    if (response.statusCode < 200 ||
        response.statusCode >= 300 ||
        json['success'] != true) {
      throw Exception(json['message'] ?? 'Erreur HTTP ${response.statusCode}');
    }
    return json['data'];
  }

  Future<Map<String, dynamic>> login(String login, String password) async =>
      Map<String, dynamic>.from(
          await _post('login.php', {'login': login, 'password': password}));

  Future<List<dynamic>> clients() async =>
      List<dynamic>.from(await _get('clients.php'));
  Future<List<dynamic>> commandes() async =>
      List<dynamic>.from(await _get('commandes.php'));
  Future<List<dynamic>> produits() async =>
      List<dynamic>.from(await _get('produits.php'));
  Future<List<dynamic>> lignes(int idCmd) async =>
      List<dynamic>.from(await _get('lignes.php?idCmd=$idCmd'));
  Future<Map<String, dynamic>> stats() async =>
      Map<String, dynamic>.from(await _get('stats.php'));

  Future<void> saveProduit(Map<String, dynamic> produit) async {
    await _post('produit_save.php', produit);
  }

  Future<int> addCommande(int idCli) async {
    final r = Map<String, dynamic>.from(
        await _post('commande_add.php', {'idCli': idCli}));
    return int.parse(r['idCmd'].toString());
  }

  Future<void> addLigne(int idCmd, int idProd, int qteCmd) async {
    await _post(
        'ligne_add.php', {'idCmd': idCmd, 'idProd': idProd, 'qteCmd': qteCmd});
  }

  Future<void> deleteLigne(int idCmd, int idProd) async {
    await _post('ligne_delete.php', {'idCmd': idCmd, 'idProd': idProd});
  }
}

/// Instance partagée par toutes les pages.
final api = ApiService();
