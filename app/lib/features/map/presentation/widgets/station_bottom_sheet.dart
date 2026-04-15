import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:einfach_laden/features/vehicle/data/vehicle_data.dart';

/// Bottom sheet with quick station info — shown on marker tap.
class StationBottomSheet extends ConsumerWidget {
  final Map<String, dynamic> station;
  const StationBottomSheet({super.key, required this.station});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final vehicle = ref.watch(selectedVehicleProvider);

    final name = station['name']?.toString() ?? 'Ladestation';
    final operator = station['operator_name']?.toString() ?? '';
    final address = _buildAddress();
    final maxPower = _toDouble(station['max_power_kw']);
    final connectors = List<Map<String, dynamic>>.from(station['connectors'] ?? []);
    final isStartable = station['is_startable'] == true || station['is_startable'] == 1;
    final isExternal = station['source'] == 'osm';
    final statusKnown = station['status_known'] != false;
    final available = _toInt(station['available_connectors']);
    final total = _toInt(station['total_connectors']);

    // Determine overall station status
    final stationStatus = _getStationStatus(isStartable, isExternal, statusKnown, available, total);

    // Connector type summary
    final connectorTypes = _connectorTypeSummary(connectors);

    // Price estimation
    final priceEstimate = _estimatePrice(vehicle, maxPower, connectors);

    return Container(
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surface,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
        boxShadow: [BoxShadow(color: Colors.black.withAlpha(30), blurRadius: 16, offset: const Offset(0, -4))],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Drag handle
          Container(
            margin: const EdgeInsets.only(top: 8),
            width: 40, height: 4,
            decoration: BoxDecoration(color: Colors.grey[400], borderRadius: BorderRadius.circular(2)),
          ),

          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Row: Name + Status badge
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(name, style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)),
                          if (operator.isNotEmpty)
                            Text(operator, style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.grey[600])),
                          if (address.isNotEmpty)
                            Text(address, style: Theme.of(context).textTheme.bodySmall),
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),
                    _StatusChip(status: stationStatus),
                  ],
                ),

                const SizedBox(height: 12),

                // Info row: power + connectors + availability
                Row(
                  children: [
                    _InfoPill(icon: Icons.bolt, label: '${maxPower.toInt()} kW', color: _powerColor(maxPower)),
                    const SizedBox(width: 8),
                    _InfoPill(icon: Icons.electrical_services, label: connectorTypes),
                    if (!isExternal && total > 0) ...[
                      const SizedBox(width: 8),
                      _InfoPill(
                        icon: available > 0 ? Icons.check_circle_outline : Icons.block,
                        label: '$available / $total frei',
                        color: available > 0 ? Colors.green : Colors.red,
                      ),
                    ],
                  ],
                ),

                const SizedBox(height: 10),

                // Startability info
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  decoration: BoxDecoration(
                    color: isStartable ? Colors.green.withAlpha(20) : Colors.amber.withAlpha(20),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        isStartable ? Icons.play_circle_fill : Icons.info_outline,
                        size: 16,
                        color: isStartable ? Colors.green[700] : Colors.amber[800],
                      ),
                      const SizedBox(width: 6),
                      Text(
                        isStartable ? 'Über Transparent Laden startbar' : 'Nicht über Transparent Laden startbar',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: isStartable ? Colors.green[700] : Colors.amber[800],
                        ),
                      ),
                    ],
                  ),
                ),

                // Price estimation
                if (priceEstimate != null) ...[
                  const SizedBox(height: 10),
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: Theme.of(context).colorScheme.surfaceContainerHighest,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.calculate_outlined, size: 18),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Geschätzt 10% → 80% (${vehicle!.displayName})',
                                style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600),
                              ),
                              Text(priceEstimate, style: const TextStyle(fontSize: 12)),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ] else if (vehicle == null) ...[
                  const SizedBox(height: 10),
                  InkWell(
                    onTap: () {
                      Navigator.of(context).pop();
                      context.push('/profile/vehicle');
                    },
                    child: Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: Theme.of(context).colorScheme.surfaceContainerHighest,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Theme.of(context).colorScheme.outline.withAlpha(50)),
                      ),
                      child: const Row(
                        children: [
                          Icon(Icons.electric_car, size: 18, color: Colors.grey),
                          SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              'Fahrzeug hinterlegen für Preisschätzung 10%→80%',
                              style: TextStyle(fontSize: 12, color: Colors.grey),
                            ),
                          ),
                          Icon(Icons.arrow_forward_ios, size: 14, color: Colors.grey),
                        ],
                      ),
                    ),
                  ),
                ],

                const SizedBox(height: 14),

                // Action buttons
                Row(
                  children: [
                    Expanded(
                      child: FilledButton.icon(
                        onPressed: () {
                          Navigator.of(context).pop();
                          final id = station['id']?.toString() ?? station['osm_id']?.toString();
                          if (id != null) {
                            if (isExternal) {
                              context.push('/station-info/$id', extra: station);
                            } else {
                              context.push('/charge-point/$id');
                            }
                          }
                        },
                        icon: const Icon(Icons.info_outline, size: 18),
                        label: const Text('Details'),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () => _openNavigation(context),
                        icon: const Icon(Icons.directions, size: 18),
                        label: const Text('Navigation'),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
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

  String _connectorTypeSummary(List<Map<String, dynamic>> connectors) {
    final types = <String>{};
    for (final c in connectors) {
      final t = c['connector_type']?.toString() ?? '';
      if (t.isNotEmpty) types.add(t);
    }
    if (types.isEmpty) return 'Unbekannt';
    return types.join(', ');
  }

  _StationStatus _getStationStatus(bool isStartable, bool isExternal, bool statusKnown, int available, int total) {
    if (isExternal || !statusKnown) return _StationStatus.external;
    if (!isStartable) return _StationStatus.notStartable;
    if (total == 0) return _StationStatus.unknown;
    if (available > 0) return _StationStatus.available;
    return _StationStatus.occupied;
  }

  String? _estimatePrice(EvVehicle? vehicle, double maxPower, List<Map<String, dynamic>> connectors) {
    if (vehicle == null) return null;
    if (connectors.isEmpty) return null;

    // Find first connector with pricing
    for (final c in connectors) {
      final pricing = c['pricing'] as Map<String, dynamic>?;
      if (pricing != null) {
        final energyPrice = _toDouble(pricing['energy_price_per_kwh_cent']);
        final timePrice = _toDouble(pricing['time_price_per_min_cent']);
        if (energyPrice > 0) {
          final costCent = vehicle.estimatedCostCent(
            energyPricePerKwhCent: energyPrice,
            timePricePerMinCent: timePrice,
            stationPowerKw: maxPower,
          );
          final minutes = vehicle.estimatedMinutes(maxPower);
          return '~${(costCent / 100).toStringAsFixed(2)} € • ~${minutes.toInt()} Min. • ${vehicle.energyFor10To80.toStringAsFixed(1)} kWh';
        }
      }
    }

    // No pricing available — show only time estimate
    final minutes = vehicle.estimatedMinutes(maxPower);
    return '~${minutes.toInt()} Min. • ${vehicle.energyFor10To80.toStringAsFixed(1)} kWh (kein Preis verfügbar)';
  }

  Color _powerColor(double kw) {
    if (kw >= 150) return Colors.deepPurple;
    if (kw >= 43) return Colors.blue;
    return Colors.teal;
  }

  void _openNavigation(BuildContext context) {
    final lat = station['latitude'];
    final lng = station['longitude'];
    if (lat != null && lng != null) {
      launchUrl(Uri.parse('https://www.google.com/maps/dir/?api=1&destination=$lat,$lng'), mode: LaunchMode.externalApplication);
    }
  }

  double _toDouble(dynamic v) => v is num ? v.toDouble() : double.tryParse(v?.toString() ?? '') ?? 0;
  int _toInt(dynamic v) => v is num ? v.toInt() : int.tryParse(v?.toString() ?? '') ?? 0;
}

enum _StationStatus { available, occupied, notStartable, external, unknown }

class _StatusChip extends StatelessWidget {
  final _StationStatus status;
  const _StatusChip({required this.status});

  @override
  Widget build(BuildContext context) {
    final (Color color, String label) = switch (status) {
      _StationStatus.available => (Colors.green, 'Frei'),
      _StationStatus.occupied => (Colors.red, 'Besetzt'),
      _StationStatus.notStartable => (const Color(0xFFFFB300), 'Sichtbar'),
      _StationStatus.external => (const Color(0xFFFFB300), 'Extern'),
      _StationStatus.unknown => (Colors.grey, 'Unbekannt'),
    };

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: color.withAlpha(25),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: color.withAlpha(80)),
      ),
      child: Text(label, style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: color)),
    );
  }
}

class _InfoPill extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color? color;
  const _InfoPill({required this.icon, required this.label, this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: (color ?? Colors.grey).withAlpha(15),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: color ?? Colors.grey[700]),
          const SizedBox(width: 4),
          Text(label, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: color ?? Colors.grey[700])),
        ],
      ),
    );
  }
}
