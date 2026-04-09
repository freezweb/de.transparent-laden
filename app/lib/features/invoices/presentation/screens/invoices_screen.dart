import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:einfach_laden/features/invoices/data/invoice_repository.dart';

class InvoicesScreen extends ConsumerWidget {
  const InvoicesScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final invoices = ref.watch(invoiceListProvider(1));

    return Scaffold(
      appBar: AppBar(title: const Text('Rechnungen')),
      body: invoices.when(
        data: (data) {
          final items = List<Map<String, dynamic>>.from(data['invoices'] ?? []);
          if (items.isEmpty) {
            return const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.receipt_long, size: 64, color: Colors.grey),
                  SizedBox(height: 16),
                  Text('Noch keine Rechnungen'),
                ],
              ),
            );
          }

          return ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: items.length,
            itemBuilder: (context, index) {
              final invoice = items[index];
              final amount = ((invoice['total_gross_cent'] ?? 0) as num) / 100;

              return Card(
                child: ListTile(
                  leading: const Icon(Icons.receipt),
                  title: Text(invoice['invoice_number'] ?? '#${invoice['id']}'),
                  subtitle: Text('${invoice['type'] ?? 'charging'} • ${invoice['created_at'] ?? ''}'),
                  trailing: Text(
                    '${amount.toStringAsFixed(2)} €',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
                  ),
                ),
              );
            },
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Fehler: $e')),
      ),
    );
  }
}
