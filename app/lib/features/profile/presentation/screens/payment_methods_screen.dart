import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:dio/dio.dart';
import 'package:einfach_laden/core/network/api_client.dart';

final _paymentMethodsProvider = FutureProvider.autoDispose<List<dynamic>>((ref) async {
  final dio = ref.watch(dioProvider);
  final response = await dio.get('/payment-methods');
  return (response.data['payment_methods'] as List?) ?? [];
});

class PaymentMethodsScreen extends ConsumerWidget {
  const PaymentMethodsScreen({super.key});

  static const _typeLabels = {
    'credit_card': 'Kreditkarte',
    'debit_card': 'Debitkarte',
    'paypal': 'PayPal',
    'sepa': 'SEPA-Lastschrift',
    'apple_pay': 'Apple Pay',
    'google_pay': 'Google Pay',
  };

  static const _typeIcons = {
    'credit_card': Icons.credit_card,
    'debit_card': Icons.credit_card,
    'paypal': Icons.account_balance_wallet,
    'sepa': Icons.account_balance,
    'apple_pay': Icons.apple,
    'google_pay': Icons.g_mobiledata,
  };

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final methods = ref.watch(_paymentMethodsProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Zahlungsmethoden'),
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () => _showAddDialog(context, ref),
          ),
        ],
      ),
      body: methods.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.error_outline, size: 48, color: Colors.grey),
              const SizedBox(height: 8),
              Text('Fehler beim Laden', style: Theme.of(context).textTheme.bodyLarge),
              const SizedBox(height: 8),
              FilledButton(onPressed: () => ref.invalidate(_paymentMethodsProvider), child: const Text('Erneut versuchen')),
            ],
          ),
        ),
        data: (list) {
          if (list.isEmpty) {
            return Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.credit_card_off, size: 64, color: Colors.grey.shade400),
                  const SizedBox(height: 16),
                  Text('Keine Zahlungsmethoden', style: Theme.of(context).textTheme.titleMedium),
                  const SizedBox(height: 8),
                  const Text('Füge eine Zahlungsmethode hinzu,\num Ladevorgänge zu bezahlen.', textAlign: TextAlign.center),
                  const SizedBox(height: 24),
                  FilledButton.icon(
                    onPressed: () => _showAddDialog(context, ref),
                    icon: const Icon(Icons.add),
                    label: const Text('Zahlungsmethode hinzufügen'),
                  ),
                ],
              ),
            );
          }

          return ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: list.length,
            itemBuilder: (context, index) {
              final m = list[index] as Map<String, dynamic>;
              final type = m['type'] as String? ?? 'credit_card';
              final isDefault = m['is_default'] == 1 || m['is_default'] == true;

              return Card(
                child: ListTile(
                  leading: Icon(_typeIcons[type] ?? Icons.payment, size: 32),
                  title: Text(m['display_name'] ?? _typeLabels[type] ?? type),
                  subtitle: Text(_typeLabels[type] ?? type),
                  trailing: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      if (isDefault)
                        Chip(
                          label: const Text('Standard', style: TextStyle(fontSize: 12)),
                          backgroundColor: Theme.of(context).colorScheme.primaryContainer,
                          padding: EdgeInsets.zero,
                          visualDensity: VisualDensity.compact,
                        ),
                      PopupMenuButton<String>(
                        onSelected: (action) => _handleAction(context, ref, action, m),
                        itemBuilder: (_) => [
                          if (!isDefault)
                            const PopupMenuItem(value: 'default', child: Text('Als Standard setzen')),
                          if (!isDefault)
                            const PopupMenuItem(value: 'delete', child: Text('Entfernen', style: TextStyle(color: Colors.red))),
                        ],
                      ),
                    ],
                  ),
                ),
              );
            },
          );
        },
      ),
    );
  }

  Future<void> _handleAction(BuildContext context, WidgetRef ref, String action, Map<String, dynamic> method) async {
    final dio = ref.read(dioProvider);
    final id = method['id'];

    try {
      if (action == 'default') {
        await dio.put('/payment-methods/$id/default');
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Standard-Zahlungsmethode aktualisiert')));
      } else if (action == 'delete') {
        final confirm = await showDialog<bool>(
          context: context,
          builder: (ctx) => AlertDialog(
            title: const Text('Entfernen?'),
            content: const Text('Möchtest du diese Zahlungsmethode wirklich entfernen?'),
            actions: [
              TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Abbrechen')),
              TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Entfernen', style: TextStyle(color: Colors.red))),
            ],
          ),
        );
        if (confirm == true) {
          await dio.delete('/payment-methods/$id');
          if (context.mounted) {
            ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Zahlungsmethode entfernt')));
          }
        }
      }
      ref.invalidate(_paymentMethodsProvider);
    } on DioException catch (e) {
      if (context.mounted) {
        final msg = e.response?.data?['messages']?['error'] ?? 'Aktion fehlgeschlagen';
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg.toString()), backgroundColor: Theme.of(context).colorScheme.error));
      }
    }
  }

  void _showAddDialog(BuildContext context, WidgetRef ref) {
    final types = ['credit_card', 'debit_card', 'paypal', 'sepa', 'apple_pay', 'google_pay'];
    String selectedType = 'credit_card';
    final nameController = TextEditingController();

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setState) => AlertDialog(
          title: const Text('Zahlungsmethode hinzufügen'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              DropdownButtonFormField<String>(
                value: selectedType,
                decoration: const InputDecoration(labelText: 'Typ', border: OutlineInputBorder()),
                items: types.map((t) => DropdownMenuItem(value: t, child: Text(_typeLabels[t] ?? t))).toList(),
                onChanged: (v) => setState(() => selectedType = v!),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: nameController,
                decoration: const InputDecoration(labelText: 'Bezeichnung (optional)', border: OutlineInputBorder(), hintText: 'z.B. Meine Visa'),
              ),
            ],
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Abbrechen')),
            FilledButton(
              onPressed: () async {
                Navigator.pop(ctx);
                try {
                  final dio = ref.read(dioProvider);
                  await dio.post('/payment-methods', data: {
                    'type': selectedType,
                    'token_reference': 'tok_${DateTime.now().millisecondsSinceEpoch}',
                    'display_name': nameController.text.trim().isEmpty ? null : nameController.text.trim(),
                    'is_default': '0',
                  });
                  ref.invalidate(_paymentMethodsProvider);
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Zahlungsmethode hinzugefügt')));
                  }
                } on DioException catch (e) {
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Fehler: ${e.message}'), backgroundColor: Theme.of(context).colorScheme.error));
                  }
                }
              },
              child: const Text('Hinzufügen'),
            ),
          ],
        ),
      ),
    );
  }
}
