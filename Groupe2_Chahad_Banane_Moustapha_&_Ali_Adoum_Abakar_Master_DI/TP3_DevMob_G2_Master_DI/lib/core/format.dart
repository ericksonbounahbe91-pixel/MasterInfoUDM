import 'package:intl/intl.dart';

/// 12500 -> "12 500" (équivalent de number_format($v, 0, ',', ' '))
String nombre(num v) =>
    NumberFormat('#,##0', 'en_US').format(v).replaceAll(',', ' ');

/// 12500 -> "12 500 FCFA"
String fcfa(num v) => '${nombre(v)} FCFA';

/// DateTime -> "14/03/2026" (équivalent de ->format('d/m/Y'))
String dateFr(DateTime d) => DateFormat('dd/MM/yyyy').format(d);