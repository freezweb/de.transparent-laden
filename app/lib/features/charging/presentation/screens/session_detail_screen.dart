import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:einfach_laden/features/charging/providers/charging_provider.dart';

class SessionDetailScreen extends ConsumerStatefulWidget {
  final int sessionId;
  const SessionDetailScreen({super.key, required this.sessionId});

  @override
  ConsumerState<SessionDetailScreen> createState() => _SessionDetailScreenState();
}

class _SessionDetailScreenState extends ConsumerState<SessionDetailScreen> {
  Timer? _pollTimer;

  @override
  void initState() {
    super.initState();
    _pollTimer = Timer.periodic(const Duration(seconds: 10), (_) {
      ref.invalidate(sessionLiveProvider(widget.sessionId));
    });
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final sessionAsync = ref.watch(sessionStatusProvider(widget.sessionId));
    final liveAsync = ref.watch(sessionLiveProvider(widget.sessionId));

    return Scaffold(
      appBar: AppBar(title: const Text('Ladevorgang')),
      body: sessionAsync.when(
        data: (data) {
          final session = data['session'] as Map<String, dynamic>? ?? data;
          final isActive = session['status'] == 'active' || session['status'] == 'starting';

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              // Status Header
              Card(
                color: isActive
                    ? Theme.of(context).colorScheme.primaryContainer
                    : Theme.of(context).colorScheme.surfaceContainerHighest,
                child: Padding(
                  padding: const EdgeInsets.all(24),
                  child: Column(
                    children: [
                      Icon(
                        isActive ? Icons.bolt : Icons.check_circle,
                        size: 48,
                        color: isActive ? Colors.amber : Colors.green,
                      ),
                      const SizedBox(height: 12),
                      Text(
                        isActive ? 'Lade aktiv...' : _statusText(session['status']),
                        style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 16),

              // Live Data
              if (isActive)
                liveAsync.when(
                  data: (live) => _buildLiveData(context, live),
                  loading: () => const Card(child: Padding(padding: EdgeInsets.all(16), child: LinearProgressIndicator())),
                  error: (_, __) => const SizedBox.shrink(),
                ),

              // Session Details
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Details', style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600)),
                      const SizedBox(height: 12),
                      _detailRow('Energie', '${session['energy_kwh'] ?? 0} kWh'),
                      _detailRow('Dauer', _formatDuration(session['duration_seconds'] ?? 0)),
                      _detailRow('Status', _statusText(session['status'])),
                      if (session['started_at'] != null) _detailRow('Gestartet', session['started_at']),
                      if (session['ended_at'] != null) _detailRow('Beendet', session['ended_at']),
                    ],
                  ),
                ),
              ),

              // Cost Breakdown
              if (session['total_price_cent'] != null && session['total_price_cent'] > 0) ...[
                const SizedBox(height: 16),
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Kosten', style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600)),
                        const SizedBox(height: 12),
                        _detailRow('Gesamt', '${(session['total_price_cent'] / 100).toStringAsFixed(2)} €',
                            bold: true),
                      ],
                    ),
                  ),
                ),
              ],

              // Stop Button
              if (isActive) ...[
                const SizedBox(height: 24),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: () async {
                      await ref.read(chargingActionsProvider).stopSession(widget.sessionId);
                      ref.invalidate(sessionStatusProvider(widget.sessionId));
                    },
                    icon: const Icon(Icons.stop, color: Colors.white),
                    label: const Text('Ladevorgang beenden', style: TextStyle(color: Colors.white)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.red,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                    ),
                  ),
                ),
              ],
            ],
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Fehler: $e')),
      ),
    );
  }

  Widget _buildLiveData(BuildContext context, Map<String, dynamic> live) {
    return Card(
      color: Theme.of(context).colorScheme.tertiaryContainer,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceAround,
          children: [
            _LiveDataItem(label: 'Energie', value: '${live['energy_kwh'] ?? 0}', unit: 'kWh'),
            _LiveDataItem(label: 'Leistung', value: '${live['power_kw'] ?? 0}', unit: 'kW'),
            _LiveDataItem(
              label: 'Kosten',
              value: ((live['live_costs']?['total_price_cent'] ?? 0) / 100).toStringAsFixed(2),
              unit: '€',
            ),
          ],
        ),
      ),
    );
  }

  Widget _detailRow(String label, String value, {bool bold = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label),
          Text(value, style: bold ? const TextStyle(fontWeight: FontWeight.bold, fontSize: 16) : null),
        ],
      ),
    );
  }

  String _statusText(String? status) {
    switch (status) {
      case 'active':
        return 'Aktiv';
      case 'starting':
        return 'Wird gestartet';
      case 'stopping':
        return 'Wird beendet';
      case 'completed':
        return 'Abgeschlossen';
      case 'failed':
        return 'Fehlgeschlagen';
      case 'cancelled':
        return 'Abgebrochen';
      default:
        return 'Unbekannt';
    }
  }

  String _formatDuration(dynamic seconds) {
    final s = seconds is int ? seconds : int.tryParse(seconds.toString()) ?? 0;
    final h = s ~/ 3600;
    final m = (s % 3600) ~/ 60;
    if (h > 0) return '${h}h ${m}min';
    return '$m min';
  }
}

class _LiveDataItem extends StatelessWidget {
  final String label;
  final String value;
  final String unit;
  const _LiveDataItem({required this.label, required this.value, required this.unit});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text(value, style: Theme.of(context).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.bold)),
        Text(unit, style: Theme.of(context).textTheme.bodySmall),
        Text(label, style: Theme.of(context).textTheme.labelSmall),
      ],
    );
  }
}
