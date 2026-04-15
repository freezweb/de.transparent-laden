import 'package:flutter/material.dart';

/// Compact map legend — separates status colors from speed categories.
class MapLegend extends StatelessWidget {
  const MapLegend({super.key});

  @override
  Widget build(BuildContext context) {
    return Material(
      elevation: 3,
      borderRadius: BorderRadius.circular(10),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
        decoration: BoxDecoration(
          color: Theme.of(context).colorScheme.surface.withAlpha(240),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            // Status colors
            Text('Status', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: Colors.grey[600])),
            const SizedBox(height: 3),
            const _LegendDot(color: Color(0xFF4CAF50), label: 'Frei & startbar'),
            const SizedBox(height: 2),
            const _LegendDot(color: Color(0xFFF44336), label: 'Besetzt / Defekt'),
            const SizedBox(height: 2),
            const _LegendDot(color: Color(0xFFFFB300), label: 'Extern / Nicht startbar'),
            const SizedBox(height: 2),
            const _LegendDot(color: Color(0xFF9E9E9E), label: 'Status unbekannt'),

            const SizedBox(height: 6),
            Divider(height: 1, color: Colors.grey[300]),
            const SizedBox(height: 6),

            // Speed
            Text('Leistung', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: Colors.grey[600])),
            const SizedBox(height: 3),
            const _SpeedRow(label: 'AC', range: '≤ 22 kW', icon: '⚡'),
            const SizedBox(height: 2),
            const _SpeedRow(label: 'DC', range: '50–149 kW', icon: '⚡⚡'),
            const SizedBox(height: 2),
            const _SpeedRow(label: 'HPC', range: '≥ 150 kW', icon: '⚡⚡⚡'),
          ],
        ),
      ),
    );
  }
}

class _LegendDot extends StatelessWidget {
  final Color color;
  final String label;
  const _LegendDot({required this.color, required this.label});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(width: 10, height: 10, decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
        const SizedBox(width: 6),
        Text(label, style: const TextStyle(fontSize: 11)),
      ],
    );
  }
}

class _SpeedRow extends StatelessWidget {
  final String label;
  final String range;
  final String icon;
  const _SpeedRow({required this.label, required this.range, required this.icon});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        SizedBox(width: 28, child: Text(label, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600))),
        SizedBox(width: 20, child: Text(icon, style: const TextStyle(fontSize: 9))),
        Text(range, style: TextStyle(fontSize: 10, color: Colors.grey[600])),
      ],
    );
  }
}
