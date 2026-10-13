import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';

class ApiException implements Exception {
  final String message;
  ApiException(this.message);
  @override
  String toString() => message;
}

class Api {
  static const _headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  };

  static dynamic _decode(http.Response r) {
    dynamic body;
    try {
      body = r.body.isEmpty ? null : jsonDecode(r.body);
    } catch (_) {
      body = null;
    }
    if (r.statusCode >= 200 && r.statusCode < 300) return body;

    // Erreurs de validation Laravel (422) : on affiche tous les messages
    if (body is Map && body['errors'] is Map) {
      final msgs = (body['errors'] as Map)
          .values
          .expand((v) => v is List ? v : [v])
          .join('\n');
      throw ApiException(msgs);
    }
    if (body is Map && body['message'] != null) {
      throw ApiException(body['message'].toString());
    }
    throw ApiException('Erreur serveur (${r.statusCode})');
  }

  static Future<dynamic> _send(Future<http.Response> Function() call) async {
    http.Response r;
    try {
      r = await call().timeout(const Duration(seconds: 15));
    } catch (_) {
      throw ApiException(
          'Connexion impossible au serveur.\nVérifiez baseUrl (config.dart) et que le serveur Laravel est lancé.');
    }
    return _decode(r);
  }

  static Uri _u(String path) => Uri.parse('$baseUrl$path');

  static Future<dynamic> get(String path) =>
      _send(() => http.get(_u(path), headers: _headers));

  static Future<dynamic> post(String path, Map body) =>
      _send(() => http.post(_u(path), headers: _headers, body: jsonEncode(body)));

  static Future<dynamic> put(String path, Map body) =>
      _send(() => http.put(_u(path), headers: _headers, body: jsonEncode(body)));

  static Future<dynamic> delete(String path) =>
      _send(() => http.delete(_u(path), headers: _headers));
}
