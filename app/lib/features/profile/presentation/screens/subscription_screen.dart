import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:dio/dio.dart';
import 'package:einfach_laden/core/network/api_client.dart';

final _plansProvider = FutureProvider.autoDispose<List<dynamic>>((ref) async {
  final dio = ref.watch(dioProvider);
  final response = await dio.get('/subscriptions/plans');
  return (response.data['plans'] as List?) ?? [];
});

final _currentSubProvider = FutureProvider.autoDispose<Map<String, dynamic>?>((ref) async {
  final dio = ref.watch(dioProvider);
  final response = await dio.get('/subscriptions/current');
  return response.data['subscription'] as Map<String, dynamic>?;
});

class SubscriptionScreen extends ConsumerWidget {
  const SubscriptionScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final plans = ref.watch(_plansProvider);
    final currentSub = ref.watch(_currentSubProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Abo verwalten')),
      body: plans.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.error_outline, size: 48, color: Colors.grey),
              const SizedBox(height: 8),
              Text('Fehler beim Laden', style: Theme.of(context).textTheme.bodyLarge),
              const SizedBox(height: 8),
              FilledButton(onPressed: () { ref.invalidate(_plansProvider); ref.invalidate(_currentSubProvider); }, child: const Text('Erneut versuchen')),
            ],
          ),
        ),
        data: (planList) {
          final sub = currentSub.valueOrNull;

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              // Current subscription status
              _CurrentSubscriptionCard(sub: sub, onCancel: () => _cancelSubscription(context, ref)),
              const SizedBox(height: 24),
              Text('Verfügbare Tarife', style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)),
              const SizedBox(height: 12),
              ...planList.map((plan) {
                final p = plan as Map<String, dynamic>;
                final version = p['current_version'] as Map<String, dynamic>?;
                final features = (p['features'] as List?) ?? [];
                final isCurrentPlan = sub != null && sub['plan']?['slug'] == p['slug'];

                return Card(
                  clipBehavior: Clip.antiAlias,
                  margin: const EdgeInsets.only(bottom: 12),
                  shape: isCurrentPlan
                      ? RoundedRectangleBorder(side: BorderSide(color: Theme.of(context).colorScheme.primary, width: 2), borderRadius: BorderRadius.circular(12))
                      : null,
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(p['name']?.toString() ?? '', style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold)),
                            ),
                            if (isCurrentPlan)
                              Chip(
                                label: const Text('Aktiv', style: TextStyle(fontSize: 12, color: Colors.white)),
                                backgroundColor: Theme.of(context).colorScheme.primary,
                                padding: EdgeInsets.zero,
                                visualDensity: VisualDensity.compact,
                              ),
                          ],
                        ),
                        if (p['description'] != null) ...[
                          const SizedBox(height: 4),
                          Text(p['description'].toString(), style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.grey.shade600)),
                        ],
                        const SizedBox(height: 12),
                        if (version != null) ...[
                          Row(
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              Text(
                                '${_formatPrice(version['monthly_base_fee_cents'])} €',
                                style: Theme.of(context).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.bold),
                              ),
                              const Text(' / Monat'),
                            ],
                          ),
                          const SizedBox(height: 4),
                          Text('${_formatPrice(version['kwh_price_cents'])} ct/kWh', style: Theme.of(context).textTheme.bodyMedium),
                        ],
                        if (features.isNotEmpty) ...[
                          const SizedBox(height: 12),
                          const Divider(),
                          const SizedBox(height: 8),
                          ...features.map((f) => Padding(
                            padding: const EdgeInsets.only(bottom: 4),
                            child: Row(
                              children: [
                                Icon(Icons.check_circle, size: 18, color: Theme.of(context).colorScheme.primary),
                                const SizedBox(width: 8),
                                Expanded(child: Text(f.toString())),
                              ],
                            ),
                          )),
                        ],
                        if (!isCurrentPlan) ...[
                          const SizedBox(height: 16),
                          SizedBox(
                            width: double.infinity,
                            child: FilledButton(
                              onPressed: () => _subscribe(context, ref, p['slug']?.toString() ?? ''),
                              child: const Text('Auswählen'),
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                );
              }),
            ],
          );
        },
      ),
    );
  }

  String _formatPrice(dynamic cents) {
    if (cents == null) return '0,00';
    final value = cents is int ? cents : int.tryParse(cents.toString()) ?? 0;
    return (value / 100).toStringAsFixed(2).replaceAll('.', ',');
  }

  Future<void> _subscribe(BuildContext context, WidgetRef ref, String planSlug) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Tarif wechseln'),
        content: const Text('Möchtest du zu diesem Tarif wechseln? Ein bestehender Tarif wird dabei gekündigt.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Abbrechen')),
          FilledButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Wechseln')),
        ],
      ),
    );

    if (confirm != true) return;

    try {
      final dio = ref.read(dioProvider);
      await dio.post('/subscriptions/subscribe', data: {'plan_slug': planSlug, 'billing_cycle': 'monthly'});
      ref.invalidate(_currentSubProvider);
      ref.invalidate(_plansProvider);
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Tarif erfolgreich gewechselt')));
      }
    } on DioException catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Fehler: ${e.response?.data?['messages']?['error'] ?? e.message}'), backgroundColor: Theme.of(context).colorScheme.error));
      }
    }
  }

  Future<void> _cancelSubscription(BuildContext context, WidgetRef ref) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Abo kündigen'),
        content: const Text('Möchtest du dein Abo wirklich kündigen? Du kannst den Dienst bis zum Ende der aktuellen Laufzeit weiter nutzen.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Behalten')),
          TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Kündigen', style: TextStyle(color: Colors.red))),
        ],
      ),
    );

    if (confirm != true) return;

    try {
      final dio = ref.read(dioProvider);
      await dio.post('/subscriptions/cancel');
      ref.invalidate(_currentSubProvider);
      ref.invalidate(_plansProvider);
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Abo gekündigt')));
      }
    } on DioException catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Fehler: ${e.message}'), backgroundColor: Theme.of(context).colorScheme.error));
      }
    }
  }
}

class _CurrentSubscriptionCard extends StatelessWidget {
  final Map<String, dynamic>? sub;
  final VoidCallback onCancel;
  const _CurrentSubscriptionCard({required this.sub, required this.onCancel});

  @override
  Widget build(BuildContext context) {
    if (sub == null) {
      return Card(
        color: Theme.of(context).colorScheme.surfaceContainerHighest,
        child: const Padding(
          padding: EdgeInsets.all(16),
          child: Row(
            children: [
              Icon(Icons.info_outline),
              SizedBox(width: 12),
              Expanded(child: Text('Du hast aktuell kein aktives Abo. Wähle einen Tarif um loszulegen.')),
            ],
          ),
        ),
      );
    }

    final plan = sub!['plan'] as Map<String, dynamic>?;
    final periodEnd = sub!['current_period_end']?.toString() ?? '';

    return Card(
      color: Theme.of(context).colorScheme.primaryContainer,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Icon(Icons.card_membership),
                const SizedBox(width: 8),
                Text('Aktuelles Abo', style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)),
              ],
            ),
            const SizedBox(height: 8),
            Text(plan?['name']?.toString() ?? 'Unbekannter Tarif', style: Theme.of(context).textTheme.titleLarge),
            const SizedBox(height: 4),
            Text('Laufzeit bis: ${_formatDate(periodEnd)}'),
            Text('Abrechnungszyklus: ${sub!['billing_cycle'] == 'yearly' ? 'Jährlich' : 'Monatlich'}'),
            const SizedBox(height: 12),
            Align(
              alignment: Alignment.centerRight,
              child: TextButton(
                onPressed: onCancel,
                child: const Text('Abo kündigen', style: TextStyle(color: Colors.red)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _formatDate(String dateStr) {
    if (dateStr.isEmpty) return '-';
    try {
      final dt = DateTime.parse(dateStr);
      return '${dt.day.toString().padLeft(2, '0')}.${dt.month.toString().padLeft(2, '0')}.${dt.year}';
    } catch (_) {
      return dateStr;
    }
  }
}
