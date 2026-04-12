import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:einfach_laden/features/charge_point/providers/charge_point_provider.dart';
import 'package:einfach_laden/features/charging/providers/charging_provider.dart';
import 'package:einfach_laden/core/widgets/price_breakdown_widget.dart';

class ChargePointDetailScreen extends ConsumerWidget {
  final int chargePointId;
  const ChargePointDetailScreen({super.key, required this.chargePointId});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final detail = ref.watch(chargePointDetailProvider(chargePointId));

    return Scaffold(
      appBar: AppBar(title: const Text('Ladepunkt')),
      body: detail.when(
        data: (data) {
          final cp = data['charge_point'] as Map<String, dynamic>? ?? data;
          final connectors = List<Map<String, dynamic>>.from(cp['connectors'] ?? data['connectors'] ?? []);

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              // Header
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        cp['name'] ?? 'Ladepunkt',
                        style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 8),
                      if (cp['address'] != null)
                        Row(
                          children: [
                            const Icon(Icons.location_on, size: 16),
                            const SizedBox(width: 4),
                            Expanded(child: Text('${cp['address']}, ${cp['postal_code'] ?? ''} ${cp['city'] ?? ''}')),
                          ],
                        ),
                      if (cp['operator_name'] != null) ...[
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            const Icon(Icons.business, size: 16),
                            const SizedBox(width: 4),
                            Text(cp['operator_name']),
                          ],
                        ),
                      ],
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 16),
              Text('Anschlüsse', style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600)),
              const SizedBox(height: 8),

              // Connectors
              ...connectors.map((conn) => _ConnectorCard(
                    connector: conn,
                    onStartCharging: () => _startCharging(context, ref, int.parse(conn['id'].toString())),
                  )),
            ],
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Fehler: $e')),
      ),
    );
  }

  void _startCharging(BuildContext context, WidgetRef ref, int connectorId) async {
    try {
      await ref.read(chargingActionsProvider).startSession(connectorId);
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Ladevorgang wird gestartet...')),
        );
        context.go('/charging');
      }
    } catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Fehler: $e'), backgroundColor: Theme.of(context).colorScheme.error),
        );
      }
    }
  }
}

class _ConnectorCard extends StatelessWidget {
  final Map<String, dynamic> connector;
  final VoidCallback onStartCharging;

  const _ConnectorCard({required this.connector, required this.onStartCharging});

  @override
  Widget build(BuildContext context) {
    final type = connector['connector_type'] ?? 'Unknown';
    final power = connector['power_kw'] ?? 0;
    final status = connector['status'] ?? 'unknown';
    final isAvailable = status == 'available';

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(
                  Icons.ev_station,
                  color: isAvailable ? Colors.green : Colors.grey,
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(type, style: Theme.of(context).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.w600)),
                      Text('$power kW • ${_statusText(status)}',
                          style: Theme.of(context).textTheme.bodySmall),
                    ],
                  ),
                ),
                _StatusBadge(status: status),
              ],
            ),
            if (connector['pricing'] != null) ...[
              const Divider(height: 24),
              PriceBreakdownWidget(pricing: connector['pricing'] as Map<String, dynamic>),
            ],
            if (isAvailable) ...[
              const SizedBox(height: 12),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: onStartCharging,
                  icon: const Icon(Icons.play_arrow),
                  label: const Text('Laden starten'),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  String _statusText(String status) {
    switch (status) {
      case 'available':
        return 'Verfügbar';
      case 'occupied':
        return 'Besetzt';
      case 'out_of_service':
        return 'Außer Betrieb';
      default:
        return 'Unbekannt';
    }
  }
}

class _StatusBadge extends StatelessWidget {
  final String status;
  const _StatusBadge({required this.status});

  @override
  Widget build(BuildContext context) {
    Color color;
    switch (status) {
      case 'available':
        color = Colors.green;
      case 'occupied':
        color = Colors.orange;
      default:
        color = Colors.grey;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: Color.fromRGBO(color.red, color.green, color.blue, 0.15),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(
        status.toUpperCase(),
        style: TextStyle(color: color, fontSize: 10, fontWeight: FontWeight.bold),
      ),
    );
  }
}

// PricingInfo replaced by PriceBreakdownWidget from core/widgets
