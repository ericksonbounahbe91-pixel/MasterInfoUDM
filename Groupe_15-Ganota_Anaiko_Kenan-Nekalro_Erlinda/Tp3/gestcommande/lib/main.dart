import 'package:flutter/material.dart';

void main() {
  runApp(const GestionCommandeApp());
}

final CommandStore commandStore = CommandStore();

class GestionCommandeApp extends StatelessWidget {
  const GestionCommandeApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Gestion de ligne de commande',
      theme: ThemeData(
        useMaterial3: true,
        scaffoldBackgroundColor: const Color(0xFFF5F7FF),
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF3051A0),
          brightness: Brightness.light,
        ),
      ),
      home: HomePage(store: commandStore),
    );
  }
}

class CommandStore {
  final List<Client> clients = [
    Client(
      id: 1,
      nom: 'Diallo',
      prenom: 'Amina',
      email: 'amina@shop.com',
      region: 'Nord',
      departement: 'Lille',
    ),
    Client(
      id: 2,
      nom: 'Martin',
      prenom: 'Sofiane',
      email: 'sofiane@shop.com',
      region: 'Ouest',
      departement: 'Rennes',
    ),
    Client(
      id: 3,
      nom: 'Roux',
      prenom: 'Nadia',
      email: 'nadia@shop.com',
      region: 'Sud',
      departement: 'Marseille',
    ),
  ];

  final List<Product> products = [
    Product(
      id: 1,
      name: 'Ordinateur portable',
      stock: 18,
      price: 1200,
      category: 'Informatique',
    ),
    Product(
      id: 2,
      name: 'Ecran 27',
      stock: 12,
      price: 380,
      category: 'Peripheriques',
    ),
    Product(
      id: 3,
      name: 'Clavier mecanique',
      stock: 25,
      price: 120,
      category: 'Peripheriques',
    ),
    Product(
      id: 4,
      name: 'Souris sans fil',
      stock: 30,
      price: 65,
      category: 'Peripheriques',
    ),
    Product(
      id: 5,
      name: 'Imprimante laser',
      stock: 8,
      price: 460,
      category: 'Bureautique',
    ),
  ];

  final List<Order> orders = [
    Order(
      id: 101,
      clientId: 1,
      date: DateTime(2026, 10, 1),
      lines: [
        OrderLine(productId: 1, quantity: 1),
        OrderLine(productId: 4, quantity: 2),
      ],
    ),
    Order(
      id: 102,
      clientId: 2,
      date: DateTime(2026, 10, 3),
      lines: [
        OrderLine(productId: 3, quantity: 3),
        OrderLine(productId: 2, quantity: 1),
      ],
    ),
  ];

  Client getClientById(int id) =>
      clients.firstWhere((client) => client.id == id);

  Product getProductById(int id) =>
      products.firstWhere((product) => product.id == id);

  double get totalRevenue {
    double total = 0;
    for (final order in orders) {
      for (final line in order.lines) {
        final product = getProductById(line.productId);
        total += product.price * line.quantity;
      }
    }
    return total;
  }

  int get totalProductsSold {
    int total = 0;
    for (final order in orders) {
      for (final line in order.lines) {
        total += line.quantity;
      }
    }
    return total;
  }

  int get totalOrders => orders.length;

  double get averageBasket {
    if (orders.isEmpty) return 0;
    return totalRevenue / orders.length;
  }

  String get bestProductName {
    if (products.isEmpty) return 'Aucun produit';

    Product best = products.first;
    int maxUnits = getProductSoldCount(best.id);

    for (final product in products) {
      final units = getProductSoldCount(product.id);
      if (units > maxUnits) {
        maxUnits = units;
        best = product;
      }
    }
    return best.name;
  }

  int getProductSoldCount(int productId) {
    int total = 0;
    for (final order in orders) {
      for (final line in order.lines) {
        if (line.productId == productId) {
          total += line.quantity;
        }
      }
    }
    return total;
  }

  double getOrderTotal(Order order) {
    double total = 0;
    for (final line in order.lines) {
      final product = getProductById(line.productId);
      total += product.price * line.quantity;
    }
    return total;
  }

  void addProductToOrder({
    required int orderId,
    required int productId,
    required int quantity,
  }) {
    if (quantity <= 0) return;

    final product = getProductById(productId);
    if (product.stock < quantity) {
      return;
    }

    final order = orders.firstWhere((item) => item.id == orderId);
    final existingLine = order.lines.indexWhere(
      (line) => line.productId == productId,
    );

    if (existingLine >= 0) {
      order.lines[existingLine].quantity += quantity;
    } else {
      order.lines.add(OrderLine(productId: productId, quantity: quantity));
    }

    product.stock -= quantity;
  }

  void updateOrderLineQuantity({
    required int orderId,
    required int productId,
    required int newQuantity,
  }) {
    if (newQuantity <= 0) {
      removeProductFromOrder(orderId: orderId, productId: productId);
      return;
    }

    final order = orders.firstWhere((item) => item.id == orderId);
    final line = order.lines.firstWhere((item) => item.productId == productId);
    final currentProduct = getProductById(productId);
    final current = line.quantity;
    final delta = newQuantity - current;

    if (delta > 0 && currentProduct.stock < delta) {
      return;
    }

    line.quantity = newQuantity;
    currentProduct.stock -= delta;
  }

  void removeProductFromOrder({required int orderId, required int productId}) {
    final order = orders.firstWhere((item) => item.id == orderId);
    final lineIndex = order.lines.indexWhere(
      (line) => line.productId == productId,
    );
    if (lineIndex == -1) return;

    final removedQuantity = order.lines[lineIndex].quantity;
    final product = getProductById(productId);
    product.stock += removedQuantity;
    order.lines.removeAt(lineIndex);
  }

  void addProductToCatalog({
    required String name,
    required double price,
    required int stock,
    required String category,
  }) {
    if (name.trim().isEmpty) return;
    final nextId =
        (products
            .map((product) => product.id)
            .fold(0, (max, id) => id > max ? id : max)) +
        1;
    products.add(
      Product(
        id: nextId,
        name: name.trim(),
        stock: stock,
        price: price,
        category: category,
      ),
    );
  }

  void updateProduct({
    required int id,
    required String name,
    required double price,
    required int stock,
    required String category,
  }) {
    final product = getProductById(id);
    product.name = name.trim();
    product.price = price;
    product.stock = stock;
    product.category = category;
  }

  void deleteProduct(int id) {
    products.removeWhere((product) => product.id == id);
    for (final order in orders) {
      order.lines.removeWhere((line) => line.productId == id);
    }
  }

  Map<String, double> salesByRegion() {
    final map = <String, double>{'Nord': 0, 'Ouest': 0, 'Sud': 0, 'Est': 0};

    for (final order in orders) {
      final client = getClientById(order.clientId);
      final orderTotal = getOrderTotal(order);
      map[client.region] = (map[client.region] ?? 0) + orderTotal;
    }
    return map;
  }
}

class Client {
  Client({
    required this.id,
    required this.nom,
    required this.prenom,
    required this.email,
    required this.region,
    required this.departement,
  });

  final int id;
  final String nom;
  final String prenom;
  final String email;
  final String region;
  final String departement;
}

class Product {
  Product({
    required this.id,
    required this.name,
    required this.stock,
    required this.price,
    required this.category,
  });

  final int id;
  String name;
  int stock;
  double price;
  String category;
}

class Order {
  Order({
    required this.id,
    required this.clientId,
    required this.date,
    List<OrderLine>? lines,
  }) : lines = lines ?? <OrderLine>[];

  final int id;
  final int clientId;
  final DateTime date;
  final List<OrderLine> lines;
}

class OrderLine {
  OrderLine({required this.productId, required this.quantity});

  final int productId;
  int quantity;
}

class HomePage extends StatelessWidget {
  const HomePage({super.key, required this.store});

  final CommandStore store;

  @override
  Widget build(BuildContext context) {
    final accent = const Color(0xFFFF0F5A);
    final primary = const Color(0xFF3051A0);

    return Scaffold(
      appBar: AppBar(
        backgroundColor: primary,
        foregroundColor: Colors.white,
        title: const Text('Accueil'),
        centerTitle: true,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [primary, accent],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(24),
                boxShadow: const [
                  BoxShadow(
                    color: Color(0x1A3051A0),
                    blurRadius: 18,
                    spreadRadius: 2,
                    offset: Offset(0, 10),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Gestion de ligne de commande',
                    style: TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.w800,
                      color: Colors.white,
                    ),
                  ),
                  const SizedBox(height: 10),
                  const Text(
                    'Tableau de bord centralise pour gerer les produits, les commandes, les clients et les statistiques.',
                    style: TextStyle(fontSize: 15, color: Colors.white70),
                  ),
                  const SizedBox(height: 20),
                  Row(
                    children: [
                      Expanded(
                        child: _MetricCard(
                          title: 'CA total',
                          value:
                              '${store.totalRevenue.toStringAsFixed(0)} FCFA',
                          color: Colors.white,
                          textColor: primary,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: _MetricCard(
                          title: 'Commandes',
                          value: '${store.totalOrders}',
                          color: Colors.white,
                          textColor: primary,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),
            GridView.count(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              crossAxisCount: 2,
              mainAxisSpacing: 14,
              crossAxisSpacing: 14,
              childAspectRatio: 1.12,
              children: [
                _ActionCard(
                  title: 'Ajouter produit à une commande',
                  icon: Icons.add_shopping_cart_rounded,
                  color: accent,
                  onTap: () => Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => AddProductPage(store: store),
                    ),
                  ),
                ),
                _ActionCard(
                  title: 'Produits',
                  icon: Icons.inventory_2_rounded,
                  color: primary,
                  onTap: () => Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => ProductsPage(store: store),
                    ),
                  ),
                ),
                _ActionCard(
                  title: 'Commandes',
                  icon: Icons.receipt_long_rounded,
                  color: const Color(0xFF2F80ED),
                  onTap: () => Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => OrdersPage(store: store)),
                  ),
                ),
                _ActionCard(
                  title: 'Clients',
                  icon: Icons.people_alt_rounded,
                  color: const Color(0xFF27AE60),
                  onTap: () => Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => ClientsPage(store: store),
                    ),
                  ),
                ),
                _ActionCard(
                  title: 'Modifier produit',
                  icon: Icons.edit_note_rounded,
                  color: const Color(0xFFF2C94C),
                  onTap: () => Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => EditProductPage(store: store),
                    ),
                  ),
                ),
                _ActionCard(
                  title: 'Supprimer produit dans une commande',
                  icon: Icons.delete_forever_rounded,
                  color: const Color(0xFFEB5757),
                  onTap: () => Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => RemoveProductPage(store: store),
                    ),
                  ),
                ),
                _ActionCard(
                  title: 'Statistiques',
                  icon: Icons.bar_chart_rounded,
                  color: const Color(0xFF9B51E0),
                  onTap: () => Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => DashboardPage(store: store),
                    ),
                  ),
                ),
                _ActionCard(
                  title: 'Resume',
                  icon: Icons.auto_awesome_rounded,
                  color: const Color(0xFF00A6A6),
                  onTap: () => Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => SummaryPage(store: store),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class ProductsPage extends StatefulWidget {
  const ProductsPage({super.key, required this.store});

  final CommandStore store;

  @override
  State<ProductsPage> createState() => _ProductsPageState();
}

class _ProductsPageState extends State<ProductsPage> {
  void _showEditDialog(Product product) {
    final nameController = TextEditingController(text: product.name);
    final priceController = TextEditingController(
      text: product.price.toStringAsFixed(0),
    );
    final stockController = TextEditingController(
      text: product.stock.toString(),
    );
    final categoryController = TextEditingController(text: product.category);

    showDialog(
      context: context,
      builder: (dialogContext) {
        return AlertDialog(
          title: Text('Modifier ${product.name}'),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(
                  controller: nameController,
                  decoration: const InputDecoration(
                    labelText: 'Nom du produit',
                  ),
                ),
                TextField(
                  controller: categoryController,
                  decoration: const InputDecoration(labelText: 'Categorie'),
                ),
                TextField(
                  controller: priceController,
                  decoration: const InputDecoration(
                    labelText: 'Prix unitaire (FCFA)',
                  ),
                ),
                TextField(
                  controller: stockController,
                  decoration: const InputDecoration(
                    labelText: 'Quantite en stock',
                  ),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(dialogContext),
              child: const Text('Annuler'),
            ),
            FilledButton(
              onPressed: () {
                final price =
                    double.tryParse(priceController.text) ?? product.price;
                final stock =
                    int.tryParse(stockController.text) ?? product.stock;
                widget.store.updateProduct(
                  id: product.id,
                  name: nameController.text,
                  price: price,
                  stock: stock,
                  category: categoryController.text,
                );
                setState(() {});
                Navigator.pop(dialogContext);
              },
              child: const Text('Enregistrer'),
            ),
          ],
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Produits')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: ListView.builder(
          itemCount: widget.store.products.length,
          itemBuilder: (context, index) {
            final product = widget.store.products[index];
            return Card(
              margin: const EdgeInsets.only(bottom: 12),
              child: ListTile(
                leading: CircleAvatar(
                  backgroundColor: const Color(0xFF3051A0).withOpacity(0.12),
                  child: const Icon(
                    Icons.inventory_2_rounded,
                    color: Color(0xFF3051A0),
                  ),
                ),
                title: Text(product.name),
                subtitle: Text(
                  '${product.category} • ${product.stock} unites • ${product.price.toStringAsFixed(0)} FCFA',
                ),
                trailing: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    IconButton(
                      icon: const Icon(Icons.edit, color: Color(0xFF3051A0)),
                      onPressed: () => _showEditDialog(product),
                    ),
                    IconButton(
                      icon: const Icon(Icons.delete, color: Colors.red),
                      onPressed: () {
                        widget.store.deleteProduct(product.id);
                        setState(() {});
                      },
                    ),
                  ],
                ),
              ),
            );
          },
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () {
          final nameController = TextEditingController();
          final priceController = TextEditingController();
          final stockController = TextEditingController();
          final categoryController = TextEditingController();

          showDialog(
            context: context,
            builder: (_) => AlertDialog(
              title: const Text('Nouveau produit'),
              content: SingleChildScrollView(
                child: Column(
                  children: [
                    TextField(
                      controller: nameController,
                      decoration: const InputDecoration(labelText: 'Nom'),
                    ),
                    TextField(
                      controller: categoryController,
                      decoration: const InputDecoration(labelText: 'Categorie'),
                    ),
                    TextField(
                      controller: priceController,
                      decoration: const InputDecoration(labelText: 'Prix'),
                    ),
                    TextField(
                      controller: stockController,
                      decoration: const InputDecoration(labelText: 'Stock'),
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text('Fermer'),
                ),
                FilledButton(
                  onPressed: () {
                    widget.store.addProductToCatalog(
                      name: nameController.text,
                      price: double.tryParse(priceController.text) ?? 0,
                      stock: int.tryParse(stockController.text) ?? 0,
                      category: categoryController.text,
                    );
                    setState(() {});
                    Navigator.pop(context);
                  },
                  child: const Text('Ajouter'),
                ),
              ],
            ),
          );
        },
        icon: const Icon(Icons.add),
        label: const Text('Ajouter'),
      ),
    );
  }
}

class OrdersPage extends StatefulWidget {
  const OrdersPage({super.key, required this.store});

  final CommandStore store;

  @override
  State<OrdersPage> createState() => _OrdersPageState();
}

class _OrdersPageState extends State<OrdersPage> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Commandes')),
      body: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: widget.store.orders.length,
        itemBuilder: (context, index) {
          final order = widget.store.orders[index];
          final client = widget.store.getClientById(order.clientId);
          final total = widget.store.getOrderTotal(order);

          return Card(
            margin: const EdgeInsets.only(bottom: 12),
            child: ListTile(
              onTap: () => Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) =>
                      OrderDetailPage(store: widget.store, orderId: order.id),
                ),
              ),
              leading: CircleAvatar(
                backgroundColor: const Color(0xFFFDE7F3),
                child: const Icon(
                  Icons.receipt_rounded,
                  color: Color(0xFFFF0F5A),
                ),
              ),
              title: Text('Commande N°${order.id}'),
              subtitle: Text(
                '${client.prenom} ${client.nom} • ${order.date.day}/${order.date.month}/${order.date.year}',
              ),
              trailing: Text(
                '${total.toStringAsFixed(0)} FCFA',
                style: const TextStyle(fontWeight: FontWeight.w700),
              ),
            ),
          );
        },
      ),
    );
  }
}

class ClientsPage extends StatelessWidget {
  const ClientsPage({super.key, required this.store});

  final CommandStore store;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Clients')),
      body: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: store.clients.length,
        itemBuilder: (context, index) {
          final client = store.clients[index];
          return Card(
            margin: const EdgeInsets.only(bottom: 12),
            child: ListTile(
              leading: CircleAvatar(
                backgroundColor: const Color(0xFF3051A0).withOpacity(0.12),
                child: Text(client.prenom.substring(0, 1).toUpperCase()),
              ),
              title: Text('${client.prenom} ${client.nom}'),
              subtitle: Text(
                '${client.email}\n${client.region} • ${client.departement}',
              ),
            ),
          );
        },
      ),
    );
  }
}

class OrderDetailPage extends StatefulWidget {
  const OrderDetailPage({
    super.key,
    required this.store,
    required this.orderId,
  });

  final CommandStore store;
  final int orderId;

  @override
  State<OrderDetailPage> createState() => _OrderDetailPageState();
}

class _OrderDetailPageState extends State<OrderDetailPage> {
  @override
  Widget build(BuildContext context) {
    final order = widget.store.orders.firstWhere(
      (item) => item.id == widget.orderId,
    );
    final client = widget.store.getClientById(order.clientId);
    final total = widget.store.getOrderTotal(order);

    return Scaffold(
      appBar: AppBar(title: Text('Commande N°${order.id}')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Client : ${client.prenom} ${client.nom}',
                      style: const TextStyle(fontWeight: FontWeight.w700),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      'Date : ${order.date.day}/${order.date.month}/${order.date.year}',
                    ),
                    const SizedBox(height: 6),
                    Text(
                      'Total : ${total.toStringAsFixed(0)} FCFA',
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            Expanded(
              child: ListView.builder(
                itemCount: order.lines.length,
                itemBuilder: (context, index) {
                  final line = order.lines[index];
                  final product = widget.store.getProductById(line.productId);

                  return Card(
                    margin: const EdgeInsets.only(bottom: 12),
                    child: ListTile(
                      title: Text(product.name),
                      subtitle: Text(
                        '${product.price.toStringAsFixed(0)} FCFA x ${line.quantity}',
                      ),
                      trailing: SizedBox(
                        width: 160,
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.end,
                          children: [
                            IconButton(
                              onPressed: () {
                                if (line.quantity > 1) {
                                  widget.store.updateOrderLineQuantity(
                                    orderId: order.id,
                                    productId: product.id,
                                    newQuantity: line.quantity - 1,
                                  );
                                  setState(() {});
                                }
                              },
                              icon: const Icon(
                                Icons.remove_circle_outline_rounded,
                              ),
                            ),
                            Text('${line.quantity}'),
                            IconButton(
                              onPressed: () {
                                widget.store.updateOrderLineQuantity(
                                  orderId: order.id,
                                  productId: product.id,
                                  newQuantity: line.quantity + 1,
                                );
                                setState(() {});
                              },
                              icon: const Icon(
                                Icons.add_circle_outline_rounded,
                              ),
                            ),
                            IconButton(
                              onPressed: () {
                                widget.store.removeProductFromOrder(
                                  orderId: order.id,
                                  productId: product.id,
                                );
                                setState(() {});
                              },
                              icon: const Icon(Icons.delete, color: Colors.red),
                            ),
                          ],
                        ),
                      ),
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () {
          showDialog(
            context: context,
            builder: (_) => AddProductDialog(
              store: widget.store,
              orderId: order.id,
              onAdded: () => setState(() {}),
            ),
          );
        },
        icon: const Icon(Icons.add),
        label: const Text('Ajouter produit'),
      ),
    );
  }
}

class AddProductPage extends StatefulWidget {
  const AddProductPage({super.key, required this.store});

  final CommandStore store;

  @override
  State<AddProductPage> createState() => _AddProductPageState();
}

class _AddProductPageState extends State<AddProductPage> {
  int selectedOrderId = 101;
  int selectedProductId = 1;
  int quantity = 1;

  @override
  void initState() {
    super.initState();
    if (widget.store.orders.isNotEmpty) {
      selectedOrderId = widget.store.orders.first.id;
    }
    if (widget.store.products.isNotEmpty) {
      selectedProductId = widget.store.products.first.id;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Ajouter produit à une commande')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            DropdownButtonFormField<int>(
              value: selectedOrderId,
              decoration: const InputDecoration(labelText: 'Commande'),
              items: widget.store.orders
                  .map(
                    (order) => DropdownMenuItem<int>(
                      value: order.id,
                      child: Text('Commande N°${order.id}'),
                    ),
                  )
                  .toList(),
              onChanged: (value) =>
                  setState(() => selectedOrderId = value ?? selectedOrderId),
            ),
            const SizedBox(height: 16),
            DropdownButtonFormField<int>(
              value: selectedProductId,
              decoration: const InputDecoration(labelText: 'Produit'),
              items: widget.store.products
                  .map(
                    (product) => DropdownMenuItem<int>(
                      value: product.id,
                      child: Text(product.name),
                    ),
                  )
                  .toList(),
              onChanged: (value) => setState(
                () => selectedProductId = value ?? selectedProductId,
              ),
            ),
            const SizedBox(height: 16),
            TextFormField(
              initialValue: '1',
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(labelText: 'Quantite'),
              onChanged: (value) => quantity = int.tryParse(value) ?? 1,
            ),
            const SizedBox(height: 24),
            FilledButton.icon(
              onPressed: () {
                widget.store.addProductToOrder(
                  orderId: selectedOrderId,
                  productId: selectedProductId,
                  quantity: quantity,
                );
                Navigator.pop(context);
              },
              icon: const Icon(Icons.check),
              label: const Text('Ajouter a la commande'),
            ),
          ],
        ),
      ),
    );
  }
}

class EditProductPage extends StatefulWidget {
  const EditProductPage({super.key, required this.store});

  final CommandStore store;

  @override
  State<EditProductPage> createState() => _EditProductPageState();
}

class _EditProductPageState extends State<EditProductPage> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Modifier un produit')),
      body: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: widget.store.products.length,
        itemBuilder: (context, index) {
          final product = widget.store.products[index];
          return Card(
            margin: const EdgeInsets.only(bottom: 12),
            child: ListTile(
              title: Text(product.name),
              subtitle: Text(
                '${product.stock} en stock • ${product.price.toStringAsFixed(0)} FCFA',
              ),
              trailing: IconButton(
                icon: const Icon(Icons.edit, color: Color(0xFF3051A0)),
                onPressed: () {
                  final nameController = TextEditingController(
                    text: product.name,
                  );
                  final categoryController = TextEditingController(
                    text: product.category,
                  );
                  final priceController = TextEditingController(
                    text: product.price.toString(),
                  );
                  final stockController = TextEditingController(
                    text: product.stock.toString(),
                  );

                  showDialog(
                    context: context,
                    builder: (_) => AlertDialog(
                      title: Text('Modifier ${product.name}'),
                      content: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          TextField(
                            controller: nameController,
                            decoration: const InputDecoration(labelText: 'Nom'),
                          ),
                          TextField(
                            controller: categoryController,
                            decoration: const InputDecoration(
                              labelText: 'Categorie',
                            ),
                          ),
                          TextField(
                            controller: priceController,
                            decoration: const InputDecoration(
                              labelText: 'Prix',
                            ),
                          ),
                          TextField(
                            controller: stockController,
                            decoration: const InputDecoration(
                              labelText: 'Stock',
                            ),
                          ),
                        ],
                      ),
                      actions: [
                        TextButton(
                          onPressed: () => Navigator.pop(context),
                          child: const Text('Annuler'),
                        ),
                        FilledButton(
                          onPressed: () {
                            widget.store.updateProduct(
                              id: product.id,
                              name: nameController.text,
                              price:
                                  double.tryParse(priceController.text) ??
                                  product.price,
                              stock:
                                  int.tryParse(stockController.text) ??
                                  product.stock,
                              category: categoryController.text,
                            );
                            setState(() {});
                            Navigator.pop(context);
                          },
                          child: const Text('Valider'),
                        ),
                      ],
                    ),
                  );
                },
              ),
            ),
          );
        },
      ),
    );
  }
}

class RemoveProductPage extends StatefulWidget {
  const RemoveProductPage({super.key, required this.store});

  final CommandStore store;

  @override
  State<RemoveProductPage> createState() => _RemoveProductPageState();
}

class _RemoveProductPageState extends State<RemoveProductPage> {
  int selectedOrderId = 101;

  @override
  void initState() {
    super.initState();
    if (widget.store.orders.isNotEmpty) {
      selectedOrderId = widget.store.orders.first.id;
    }
  }

  @override
  Widget build(BuildContext context) {
    final order = widget.store.orders.firstWhere(
      (item) => item.id == selectedOrderId,
    );

    return Scaffold(
      appBar: AppBar(title: const Text('Supprimer un produit d une commande')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            DropdownButtonFormField<int>(
              value: selectedOrderId,
              isExpanded: true,
              decoration: const InputDecoration(labelText: 'Commande'),
              items: widget.store.orders
                  .map(
                    (item) => DropdownMenuItem<int>(
                      value: item.id,
                      child: Text('Commande N°${item.id}'),
                    ),
                  )
                  .toList(),
              onChanged: (value) =>
                  setState(() => selectedOrderId = value ?? selectedOrderId),
            ),
            const SizedBox(height: 16),
            Expanded(
              child: ListView.builder(
                itemCount: order.lines.length,
                itemBuilder: (context, index) {
                  final line = order.lines[index];
                  final product = widget.store.getProductById(line.productId);
                  return Card(
                    margin: const EdgeInsets.only(bottom: 10),
                    child: ListTile(
                      title: Text(product.name),
                      subtitle: Text('Quantite : ${line.quantity}'),
                      trailing: IconButton(
                        onPressed: () {
                          widget.store.removeProductFromOrder(
                            orderId: order.id,
                            productId: product.id,
                          );
                          setState(() {});
                        },
                        icon: const Icon(Icons.delete, color: Colors.red),
                      ),
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class DashboardPage extends StatelessWidget {
  const DashboardPage({super.key, required this.store});

  final CommandStore store;

  @override
  Widget build(BuildContext context) {
    final salesByRegion = store.salesByRegion();
    final regionEntries = salesByRegion.entries.toList();
    final topProduct = store.bestProductName;

    return Scaffold(
      appBar: AppBar(title: const Text('Tableau de bord')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            Row(
              children: [
                Expanded(
                  child: _MetricCard(
                    title: 'Quantite vendue',
                    value: '${store.totalProductsSold}',
                    color: const Color(0xFF3051A0),
                    textColor: Colors.white,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _MetricCard(
                    title: 'Panier moyen',
                    value: '${store.averageBasket.toStringAsFixed(0)} FCFA',
                    color: const Color(0xFFFF0F5A),
                    textColor: Colors.white,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Produits les plus vendus',
                      style: TextStyle(
                        fontWeight: FontWeight.w800,
                        fontSize: 18,
                      ),
                    ),
                    const SizedBox(height: 12),
                    for (final product in store.products)
                      Padding(
                        padding: const EdgeInsets.symmetric(vertical: 6),
                        child: Row(
                          children: [
                            Expanded(child: Text(product.name)),
                            Text(
                              '${store.getProductSoldCount(product.id)} unites',
                            ),
                          ],
                        ),
                      ),
                    const SizedBox(height: 6),
                    Text(
                      'Meilleur produit : $topProduct',
                      style: const TextStyle(fontWeight: FontWeight.bold),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Repartition par region',
                      style: TextStyle(
                        fontWeight: FontWeight.w800,
                        fontSize: 18,
                      ),
                    ),
                    const SizedBox(height: 12),
                    for (final entry in regionEntries)
                      Padding(
                        padding: const EdgeInsets.only(bottom: 12),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(entry.key),
                                Text('${entry.value.toStringAsFixed(0)} FCFA'),
                              ],
                            ),
                            const SizedBox(height: 6),
                            ClipRRect(
                              borderRadius: BorderRadius.circular(999),
                              child: LinearProgressIndicator(
                                value: (entry.value == 0)
                                    ? 0
                                    : (entry.value /
                                          store.totalRevenue.clamp(
                                            1,
                                            double.infinity,
                                          )),
                                minHeight: 10,
                                backgroundColor: const Color(0xFFEAEFFB),
                                valueColor: const AlwaysStoppedAnimation<Color>(
                                  Color(0xFF3051A0),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class SummaryPage extends StatelessWidget {
  const SummaryPage({super.key, required this.store});

  final CommandStore store;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Resume')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            _MetricCard(
              title: 'Montant total',
              value: '${store.totalRevenue.toStringAsFixed(0)} FCFA',
              color: const Color(0xFF3051A0),
              textColor: Colors.white,
            ),
            const SizedBox(height: 12),
            _MetricCard(
              title: 'Produits vendus',
              value: '${store.totalProductsSold}',
              color: const Color(0xFFFF0F5A),
              textColor: Colors.white,
            ),
            const SizedBox(height: 12),
            _MetricCard(
              title: 'Produit le plus demande',
              value: store.bestProductName,
              color: const Color(0xFF2F80ED),
              textColor: Colors.white,
            ),
          ],
        ),
      ),
    );
  }
}

class AddProductDialog extends StatefulWidget {
  const AddProductDialog({
    super.key,
    required this.store,
    required this.orderId,
    required this.onAdded,
  });

  final CommandStore store;
  final int orderId;
  final VoidCallback onAdded;

  @override
  State<AddProductDialog> createState() => _AddProductDialogState();
}

class _AddProductDialogState extends State<AddProductDialog> {
  int selectedProductId = 1;
  int quantity = 1;

  @override
  void initState() {
    super.initState();
    if (widget.store.products.isNotEmpty) {
      selectedProductId = widget.store.products.first.id;
    }
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: const Text('Ajouter un produit'),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          DropdownButtonFormField<int>(
            value: selectedProductId,
            isExpanded: true,
            items: widget.store.products
                .map(
                  (product) => DropdownMenuItem<int>(
                    value: product.id,
                    child: Text(product.name),
                  ),
                )
                .toList(),
            onChanged: (value) =>
                setState(() => selectedProductId = value ?? selectedProductId),
            decoration: const InputDecoration(labelText: 'Produit'),
          ),
          const SizedBox(height: 12),
          TextFormField(
            initialValue: '1',
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(labelText: 'Quantite'),
            onChanged: (value) => quantity = int.tryParse(value) ?? 1,
          ),
        ],
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(context),
          child: const Text('Annuler'),
        ),
        FilledButton(
          onPressed: () {
            widget.store.addProductToOrder(
              orderId: widget.orderId,
              productId: selectedProductId,
              quantity: quantity,
            );
            widget.onAdded();
            Navigator.pop(context);
          },
          child: const Text('Ajouter'),
        ),
      ],
    );
  }
}

class _MetricCard extends StatelessWidget {
  const _MetricCard({
    required this.title,
    required this.value,
    required this.color,
    required this.textColor,
  });

  final String title;
  final String value;
  final Color color;
  final Color textColor;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(18),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: TextStyle(fontSize: 12, color: textColor.withOpacity(0.8)),
          ),
          const SizedBox(height: 8),
          Text(
            value,
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w800,
              color: textColor,
            ),
          ),
        ],
      ),
    );
  }
}

class _ActionCard extends StatelessWidget {
  const _ActionCard({
    required this.title,
    required this.icon,
    required this.color,
    required this.onTap,
  });

  final String title;
  final IconData icon;
  final Color color;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(20),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          boxShadow: const [
            BoxShadow(
              color: Color(0x0F3051A0),
              blurRadius: 12,
              offset: Offset(0, 6),
            ),
          ],
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: color.withOpacity(0.12),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Icon(icon, color: color, size: 28),
            ),
            const SizedBox(height: 12),
            Text(
              title,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w700,
                color: Color(0xFF1C2442),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
