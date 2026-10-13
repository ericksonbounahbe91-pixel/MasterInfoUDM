import 'package:flutter_test/flutter_test.dart';
import 'package:gestcommande/main.dart';

void main() {
  testWidgets('L’application charge l’écran d’accueil de gestion de commande', (
    tester,
  ) async {
    await tester.pumpWidget(const GestionCommandeApp());

    expect(find.text('Gestion de ligne de commande'), findsOneWidget);
    expect(find.text('Accueil'), findsOneWidget);
    expect(find.text('Ajouter produit à une commande'), findsOneWidget);
  });
}
