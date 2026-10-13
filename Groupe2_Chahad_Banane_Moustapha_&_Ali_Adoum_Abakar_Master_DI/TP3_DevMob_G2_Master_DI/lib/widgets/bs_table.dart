import 'package:flutter/material.dart';
import '../core/theme.dart';

/// Tableau Bootstrap : en-tête sombre, lignes alternées, défilement horizontal
/// si l'écran est trop étroit (= .table-responsive).
class BsTable extends StatelessWidget {
  final List<DataColumn> columns;
  final List<List<DataCell>> rows;
  final String emptyText;

  const BsTable({
    super.key,
    required this.columns,
    required this.rows,
    this.emptyText = '',
  });

  @override
  Widget build(BuildContext context) {
    if (rows.isEmpty) {
      return Padding(
        padding: const EdgeInsets.all(16),
        child: Center(
            child: Text(emptyText, style: const TextStyle(color: Bs.muted))),
      );
    }
    return LayoutBuilder(
      builder: (context, c) => SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: ConstrainedBox(
          constraints: BoxConstraints(minWidth: c.maxWidth),
          child: DataTable(
            headingRowColor: WidgetStateProperty.all(Bs.dark),
            headingTextStyle:
            const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
            columns: columns,
            rows: [
              for (var i = 0; i < rows.length; i++)
                DataRow(
                  color: WidgetStateProperty.all(
                      i.isOdd ? const Color(0x0D000000) : Colors.white),
                  cells: rows[i],
                ),
            ],
          ),
        ),
      ),
    );
  }
}