import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:einfach_laden/features/charge_point/providers/charge_point_provider.dart';
import 'package:einfach_laden/features/charging/providers/charging_provider.dart';
import 'package:einfach_laden/core/widgets/price_breakdown_widget.dart';
import 'package:einfach_laden/features/charge_point/data/charge_point_repository.dart';

class ChargePointDetailScreen extends ConsumerWidget {
  final int chargePointId;
  const ChargePointDetailScreen({super.key, required this.chargePointId});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final detail = ref.watch(chargePointDetailProvider(chargePointId));
    final reviews = ref.watch(chargePointReviewsProvider(chargePointId));

    return Scaffold(
      appBar: AppBar(title: const Text('Ladestation')),
      body: detail.when(
        data: (data) {
          final cp = data['charge_point'] as Map<String, dynamic>? ?? data;
          final connectors = List<Map<String, dynamic>>.from(cp['connectors'] ?? data['connectors'] ?? []);
          final bool isStartable = cp['is_startable'] == true || cp['is_startable'] == 1;

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              // Header card
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              cp['name'] ?? 'Ladestation',
                              style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
                            ),
                          ),
                          _StartabilityBadge(isStartable: isStartable),
                        ],
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
                      if (!isStartable) ...[
                        const SizedBox(height: 12),
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: Colors.orange.withAlpha(30),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: const Row(
                            children: [
                              Icon(Icons.info_outline, color: Colors.orange, size: 18),
                              SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  'Diese Station kann derzeit nicht über Transparent Laden gestartet werden.',
                                  style: TextStyle(fontSize: 13),
                                ),
                              ),
                            ],
                          ),
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
                    isStartable: isStartable,
                    onStartCharging: () => _startCharging(context, ref, int.parse(conn['id'].toString())),
                  )),

              // Reviews section
              const SizedBox(height: 24),
              Row(
                children: [
                  Text('Bewertungen', style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600)),
                  const Spacer(),
                  TextButton.icon(
                    onPressed: () => _showAddReviewDialog(context, ref),
                    icon: const Icon(Icons.rate_review, size: 18),
                    label: const Text('Bewerten'),
                  ),
                ],
              ),
              const SizedBox(height: 8),

              reviews.when(
                data: (reviewList) {
                  if (reviewList.isEmpty) {
                    return const Card(
                      child: Padding(
                        padding: EdgeInsets.all(24),
                        child: Center(
                          child: Text('Noch keine Bewertungen. Sei der Erste!', style: TextStyle(color: Colors.grey)),
                        ),
                      ),
                    );
                  }
                  return Column(
                    children: reviewList.map((r) => _ReviewCard(
                      review: r,
                      onReport: () => _reportContent(context, ref, 'review', r['id']),
                    )).toList(),
                  );
                },
                loading: () => const Center(child: CircularProgressIndicator()),
                error: (_, __) => const SizedBox.shrink(),
              ),
            ],
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Fehler beim Laden der Station')),
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

  void _showAddReviewDialog(BuildContext context, WidgetRef ref) {
    int rating = 4;
    final commentController = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setSheetState) => Padding(
          padding: EdgeInsets.only(
            left: 20, right: 20, top: 20,
            bottom: MediaQuery.of(ctx).viewInsets.bottom + 20,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('Station bewerten', style: Theme.of(ctx).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold)),
              const SizedBox(height: 16),
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: List.generate(5, (i) => IconButton(
                  icon: Icon(i < rating ? Icons.star : Icons.star_border, color: Colors.amber, size: 36),
                  onPressed: () => setSheetState(() => rating = i + 1),
                )),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: commentController,
                decoration: const InputDecoration(
                  labelText: 'Hinweis / Kommentar (optional)',
                  hintText: 'z.B. Schwer zu finden, hinter dem Parkhaus...',
                ),
                maxLines: 3,
                maxLength: 500,
              ),
              const SizedBox(height: 12),
              SizedBox(
                width: double.infinity,
                child: FilledButton(
                  onPressed: () async {
                    Navigator.of(ctx).pop();
                    try {
                      final repo = ref.read(chargePointRepositoryProvider);
                      await repo.submitReview(
                        chargePointId: chargePointId,
                        rating: rating,
                        comment: commentController.text.isNotEmpty ? commentController.text : null,
                      );
                      ref.invalidate(chargePointReviewsProvider(chargePointId));
                      if (context.mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Bewertung gespeichert!')),
                        );
                      }
                    } catch (e) {
                      if (context.mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(content: Text('Fehler beim Speichern')),
                        );
                      }
                    }
                  },
                  child: const Text('Bewertung abgeben'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _reportContent(BuildContext context, WidgetRef ref, String entityType, dynamic entityId) {
    final reasonController = TextEditingController();
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Inhalt melden'),
        content: TextField(
          controller: reasonController,
          decoration: const InputDecoration(
            labelText: 'Grund der Meldung',
            hintText: 'z.B. Spam, unangemessener Inhalt...',
          ),
          maxLines: 3,
        ),
        actions: [
          TextButton(onPressed: () => Navigator.of(ctx).pop(), child: const Text('Abbrechen')),
          FilledButton(
            onPressed: () async {
              Navigator.of(ctx).pop();
              try {
                final repo = ref.read(chargePointRepositoryProvider);
                await repo.reportContent(
                  entityType: entityType,
                  entityId: int.parse(entityId.toString()),
                  reason: reasonController.text,
                );
                if (context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Meldung wurde gespeichert. Vielen Dank!')),
                  );
                }
              } catch (_) {
                if (context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Fehler beim Melden')),
                  );
                }
              }
            },
            child: const Text('Melden'),
          ),
        ],
      ),
    );
  }
}

class _StartabilityBadge extends StatelessWidget {
  final bool isStartable;
  const _StartabilityBadge({required this.isStartable});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: isStartable ? Colors.green.withAlpha(30) : Colors.orange.withAlpha(30),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            isStartable ? Icons.check_circle : Icons.remove_circle_outline,
            size: 14,
            color: isStartable ? Colors.green : Colors.orange,
          ),
          const SizedBox(width: 4),
          Text(
            isStartable ? 'Startbar' : 'Nicht startbar',
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w600,
              color: isStartable ? Colors.green : Colors.orange,
            ),
          ),
        ],
      ),
    );
  }
}

class _ConnectorCard extends StatelessWidget {
  final Map<String, dynamic> connector;
  final bool isStartable;
  final VoidCallback onStartCharging;

  const _ConnectorCard({required this.connector, required this.isStartable, required this.onStartCharging});

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
            if (isAvailable && isStartable) ...[
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
        color: color.withAlpha(38),
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

class _ReviewCard extends StatelessWidget {
  final Map<String, dynamic> review;
  final VoidCallback onReport;

  const _ReviewCard({required this.review, required this.onReport});

  @override
  Widget build(BuildContext context) {
    final rating = review['rating'] ?? 0;
    final comment = review['comment'] ?? '';
    final userName = review['user_name'] ?? 'Nutzer';
    final createdAt = review['created_at'] ?? '';
    final images = List<String>.from(review['images'] ?? []);

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                ...List.generate(5, (i) => Icon(
                  i < rating ? Icons.star : Icons.star_border,
                  color: Colors.amber,
                  size: 16,
                )),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(userName, style: Theme.of(context).textTheme.bodySmall?.copyWith(fontWeight: FontWeight.w600)),
                ),
                PopupMenuButton<String>(
                  itemBuilder: (_) => [
                    const PopupMenuItem(value: 'report', child: Text('Melden')),
                  ],
                  onSelected: (v) {
                    if (v == 'report') onReport();
                  },
                  child: const Icon(Icons.more_vert, size: 18),
                ),
              ],
            ),
            if (comment.isNotEmpty) ...[
              const SizedBox(height: 6),
              Text(comment),
            ],
            if (images.isNotEmpty) ...[
              const SizedBox(height: 8),
              SizedBox(
                height: 80,
                child: ListView.separated(
                  scrollDirection: Axis.horizontal,
                  itemCount: images.length,
                  separatorBuilder: (_, __) => const SizedBox(width: 8),
                  itemBuilder: (ctx, i) => Stack(
                    children: [
                      ClipRRect(
                        borderRadius: BorderRadius.circular(8),
                        child: Image.network(
                          images[i],
                          width: 80,
                          height: 80,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => Container(
                            width: 80, height: 80,
                            color: Colors.grey[200],
                            child: const Icon(Icons.broken_image, size: 24),
                          ),
                        ),
                      ),
                      Positioned(
                        top: 2,
                        right: 2,
                        child: GestureDetector(
                          onTap: onReport,
                          child: Container(
                            padding: const EdgeInsets.all(2),
                            decoration: BoxDecoration(
                              color: Colors.black54,
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: const Icon(Icons.flag, color: Colors.white, size: 12),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
            if (createdAt.isNotEmpty) ...[
              const SizedBox(height: 4),
              Text(createdAt, style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.grey)),
            ],
          ],
        ),
      ),
    );
  }
}
