import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:einfach_laden/features/charging/providers/charging_provider.dart';
import 'package:einfach_laden/core/widgets/price_breakdown_widget.dart';

class ChargingScreen extends ConsumerWidget {
  const ChargingScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final activeSession = ref.watch(activeSessionProvider);
    final history = ref.watch(chargingHistoryProvider(1));

    return Scaffold(
      appBar: AppBar(title: const Text('Ladevorgänge')),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(activeSessionProvider);
          ref.invalidate(chargingHistoryProvider(1));
        },
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // Active Session
            activeSession.when(
              data: (session) {
                if (session == null || session['session'] == null) return const SizedBox.shrink();
                final s = session['session'] as Map<String, dynamic>;
                return Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Aktiver Ladevorgang',
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600)),
                    const SizedBox(height: 8),
                    _ActiveSessionCard(session: s),
                    const SizedBox(height: 24),
                  ],
                );
              },
              loading: () => const Padding(
                padding: EdgeInsets.all(8),
                child: LinearProgressIndicator(),
              ),
              error: (_, __) => const SizedBox.shrink(),
            ),

            // History
            Text('Verlauf', style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600)),
            const SizedBox(height: 8),
            history.when(
              data: (data) {
                final sessions = List<Map<String, dynamic>>.from(data['sessions'] ?? []);
                if (sessions.isEmpty) {
                  return const Padding(
                    padding: EdgeInsets.all(32),
                    child: Center(child: Text('Noch keine Ladevorgänge')),
                  );
                }
                return Column(
                  children: sessions.map((s) => _HistorySessionCard(session: s)).toList(),
                );
              },
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (e, _) => Center(child: Text('Fehler: $e')),
            ),
          ],
        ),
      ),
    );
  }
}

class _ActiveSessionCard extends ConsumerWidget {
  final Map<String, dynamic> session;
  const _ActiveSessionCard({required this.session});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Card(
      color: Theme.of(context).colorScheme.primaryContainer,
      child: InkWell(
        onTap: () => context.push('/session/${session['id']}'),
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  const Icon(Icons.bolt, color: Colors.amber),
                  const SizedBox(width: 8),
                  Text('Lade aktiv...', style: Theme.of(context).textTheme.titleSmall),
                  const Spacer(),
                  ElevatedButton(
                    onPressed: () async {
                      await ref.read(chargingActionsProvider).stopSession(int.parse(session['id'].toString()));
                    },
                    style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
                    child: const Text('Stoppen', style: TextStyle(color: Colors.white)),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceAround,
                children: [
                  _InfoChip(label: 'Energie', value: '${session['energy_kwh'] ?? 0} kWh'),
                  _InfoChip(label: 'Kosten', value: '${((session['current_cost_cent'] ?? 0) / 100).toStringAsFixed(2)} €'),
                ],
              ),
              if (session['pricing'] != null) ...[
                const SizedBox(height: 12),
                PriceBreakdownWidget(
                  pricing: session['pricing'] as Map<String, dynamic>,
                  isEstimate: true,
                  compact: true,
                  showPercentageBar: true,
                ),
              ] else ...[
                const SizedBox(height: 8),
                const PriceBreakdownLegend(),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

class _HistorySessionCard extends StatelessWidget {
  final Map<String, dynamic> session;
  const _HistorySessionCard({required this.session});

  @override
  Widget build(BuildContext context) {
    final status = session['status'] ?? 'unknown';
    final energy = session['energy_kwh'] ?? 0;
    final cost = ((session['total_price_cent'] ?? 0) as num) / 100;

    return Card(
      child: ListTile(
        onTap: () => context.push('/session/${session['id']}'),
        leading: Icon(
          status == 'completed' ? Icons.check_circle : Icons.cancel,
          color: status == 'completed' ? Colors.green : Colors.red,
        ),
        title: Text('$energy kWh • ${cost.toStringAsFixed(2)} €'),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(session['created_at'] ?? ''),
            const SizedBox(height: 4),
            const Text('Partner-Aufteilung ansehen →', style: TextStyle(fontSize: 11, color: Colors.grey)),
          ],
        ),
        trailing: const Icon(Icons.chevron_right),
      ),
    );
  }
}

class _InfoChip extends StatelessWidget {
  final String label;
  final String value;
  const _InfoChip({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text(value, style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)),
        Text(label, style: Theme.of(context).textTheme.bodySmall),
      ],
    );
  }
}
