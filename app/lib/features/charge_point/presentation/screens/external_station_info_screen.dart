import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

/// Detail screen for external (OSM) stations not startable through our system.
class ExternalStationInfoScreen extends StatelessWidget {
  final Map<String, dynamic> station;
  const ExternalStationInfoScreen({super.key, required this.station});

  @override
  Widget build(BuildContext context) {
    final name = station['name']?.toString() ?? 'Ladestation';
    final operator = station['operator_name']?.toString() ?? '';
    final address = _buildAddress();
    final maxPower = _toDouble(station['max_power_kw']);
    final connectors = List<Map<String, dynamic>>.from(station['connectors'] ?? []);
    final openingHours = station['opening_hours']?.toString() ?? '';
    final fee = station['fee']?.toString() ?? '';
    final network = station['network']?.toString() ?? '';

    return Scaffold(
      appBar: AppBar(title: const Text('Stationsdetails')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Info banner
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.amber.withAlpha(30),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: Colors.amber.withAlpha(80)),
            ),
            child: Row(
              children: [
                const Icon(Icons.info_outline, color: Colors.amber, size: 24),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'Diese Station ist nicht über Transparent Laden startbar. '
                    'Die Daten stammen aus öffentlichen Quellen (OpenStreetMap).',
                    style: TextStyle(fontSize: 13, color: Colors.amber[900]),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Name + Operator
          Text(name, style: Theme.of(context).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.bold)),
          if (operator.isNotEmpty) ...[
            const SizedBox(height: 4),
            Text(operator, style: Theme.of(context).textTheme.bodyLarge?.copyWith(color: Colors.grey[600])),
          ],
          if (address.isNotEmpty) ...[
            const SizedBox(height: 4),
            Row(
              children: [
                const Icon(Icons.location_on, size: 16, color: Colors.grey),
                const SizedBox(width: 4),
                Expanded(child: Text(address, style: Theme.of(context).textTheme.bodyMedium)),
              ],
            ),
          ],
          if (network.isNotEmpty) ...[
            const SizedBox(height: 4),
            Row(
              children: [
                const Icon(Icons.hub, size: 16, color: Colors.grey),
                const SizedBox(width: 4),
                Text('Netzwerk: $network', style: Theme.of(context).textTheme.bodyMedium),
              ],
            ),
          ],

          const SizedBox(height: 20),

          // Power
          _SectionCard(
            title: 'Ladeleistung',
            icon: Icons.bolt,
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: _powerColor(maxPower).withAlpha(20),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: _powerColor(maxPower).withAlpha(80)),
                  ),
                  child: Text(
                    '${maxPower.toInt()} kW max',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: _powerColor(maxPower)),
                  ),
                ),
                const SizedBox(width: 12),
                Text(
                  maxPower >= 150 ? 'HPC' : maxPower >= 43 ? 'DC' : 'AC',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600, color: Colors.grey[600]),
                ),
              ],
            ),
          ),

          const SizedBox(height: 12),

          // Connectors
          if (connectors.isNotEmpty)
            _SectionCard(
              title: 'Anschlüsse',
              icon: Icons.electrical_services,
              child: Column(
                children: connectors.map((c) {
                  final type = c['connector_type']?.toString() ?? 'Unbekannt';
                  final power = _toDouble(c['power_kw']);
                  final current = c['current_type']?.toString() ?? '';
                  final count = c['count'] is num ? (c['count'] as num).toInt() : 1;
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 8),
                    child: Row(
                      children: [
                        Container(
                          width: 36, height: 36,
                          decoration: BoxDecoration(
                            color: Colors.grey[100],
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: const Icon(Icons.power, size: 20),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(type, style: const TextStyle(fontWeight: FontWeight.w600)),
                              Text(
                                '${power.toInt()} kW${current.isNotEmpty ? ' • $current' : ''}${count > 1 ? ' • ${count}x' : ''}',
                                style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  );
                }).toList(),
              ),
            ),

          if (openingHours.isNotEmpty) ...[
            const SizedBox(height: 12),
            _SectionCard(
              title: 'Öffnungszeiten',
              icon: Icons.access_time,
              child: Text(openingHours),
            ),
          ],

          if (fee.isNotEmpty) ...[
            const SizedBox(height: 12),
            _SectionCard(
              title: 'Gebühren',
              icon: Icons.euro,
              child: Text(fee),
            ),
          ],

          const SizedBox(height: 20),

          // Navigation button
          FilledButton.icon(
            onPressed: () {
              final lat = station['latitude'];
              final lng = station['longitude'];
              if (lat != null && lng != null) {
                launchUrl(
                  Uri.parse('https://www.google.com/maps/dir/?api=1&destination=$lat,$lng'),
                  mode: LaunchMode.externalApplication,
                );
              }
            },
            icon: const Icon(Icons.directions),
            label: const Text('Navigation starten'),
            style: FilledButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 14),
              minimumSize: const Size(double.infinity, 48),
            ),
          ),

          const SizedBox(height: 32),
        ],
      ),
    );
  }

  String _buildAddress() {
    final parts = <String>[];
    final addr = station['address']?.toString() ?? '';
    final city = station['city']?.toString() ?? '';
    final postal = station['postal_code']?.toString() ?? '';
    if (addr.isNotEmpty) parts.add(addr);
    if (postal.isNotEmpty || city.isNotEmpty) parts.add('$postal $city'.trim());
    return parts.join(', ');
  }

  Color _powerColor(double kw) {
    if (kw >= 150) return Colors.deepPurple;
    if (kw >= 43) return Colors.blue;
    return Colors.teal;
  }

  double _toDouble(dynamic v) => v is num ? v.toDouble() : double.tryParse(v?.toString() ?? '') ?? 0;
}

class _SectionCard extends StatelessWidget {
  final String title;
  final IconData icon;
  final Widget child;
  const _SectionCard({required this.title, required this.icon, required this.child});

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(icon, size: 18, color: Colors.grey[600]),
                const SizedBox(width: 6),
                Text(title, style: Theme.of(context).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.w600)),
              ],
            ),
            const SizedBox(height: 10),
            child,
          ],
        ),
      ),
    );
  }
}
