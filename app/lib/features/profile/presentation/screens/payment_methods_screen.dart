import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:dio/dio.dart';
import 'package:webview_flutter/webview_flutter.dart';
import 'package:einfach_laden/core/network/api_client.dart';
import 'package:einfach_laden/core/services/payment_service.dart';

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
            onPressed: () => _showAddSheet(context, ref),
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
                    onPressed: () => _showAddSheet(context, ref),
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
                  title: Text(m['label'] ?? _typeLabels[type] ?? type),
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
        if (context.mounted) {
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Standard-Zahlungsmethode aktualisiert')));
        }
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

  /// Bottom-Sheet mit verfügbaren Zahlungsarten.
  /// Nur plattformpassende Optionen werden angezeigt.
  void _showAddSheet(BuildContext context, WidgetRef ref) {
    final paymentService = ref.read(paymentServiceProvider);

    final options = <_PaymentOption>[
      _PaymentOption(
        type: 'credit_card',
        icon: Icons.credit_card,
        label: 'Kreditkarte',
        subtitle: 'Visa, Mastercard, American Express',
        onTap: () => _addStripeMethod(context, ref, 'credit_card'),
      ),
      _PaymentOption(
        type: 'debit_card',
        icon: Icons.credit_card,
        label: 'Debitkarte',
        subtitle: 'Girokarte, Maestro',
        onTap: () => _addStripeMethod(context, ref, 'debit_card'),
      ),
      _PaymentOption(
        type: 'sepa',
        icon: Icons.account_balance,
        label: 'SEPA-Lastschrift',
        subtitle: 'Direkte Abbuchung von deinem Bankkonto',
        onTap: () => _addSepaMethod(context, ref),
      ),
      _PaymentOption(
        type: 'paypal',
        icon: Icons.account_balance_wallet,
        label: 'PayPal',
        subtitle: 'Mit PayPal-Konto verbinden',
        onTap: () => _addPayPalMethod(context, ref),
      ),
      if (paymentService.isGooglePayAvailable)
        _PaymentOption(
          type: 'google_pay',
          icon: Icons.g_mobiledata,
          label: 'Google Pay',
          subtitle: 'Schnell bezahlen mit Google Pay',
          onTap: () => _addGooglePayMethod(context, ref),
        ),
      if (paymentService.isApplePayAvailable)
        _PaymentOption(
          type: 'apple_pay',
          icon: Icons.apple,
          label: 'Apple Pay',
          subtitle: 'Schnell bezahlen mit Apple Pay',
          onTap: () => _addApplePayMethod(context, ref),
        ),
    ];

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (ctx) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 20, 20, 8),
              child: Text('Zahlungsmethode hinzufügen', style: Theme.of(context).textTheme.titleLarge),
            ),
            ...options.map((opt) => ListTile(
              leading: Icon(opt.icon, size: 28),
              title: Text(opt.label),
              subtitle: Text(opt.subtitle, style: Theme.of(context).textTheme.bodySmall),
              trailing: const Icon(Icons.chevron_right),
              onTap: () {
                Navigator.pop(ctx);
                opt.onTap();
              },
            )),
            const SizedBox(height: 16),
          ],
        ),
      ),
    );
  }

  // ── Stripe-basiert: Kreditkarte / Debitkarte ──

  Future<void> _addStripeMethod(BuildContext context, WidgetRef ref, String type) async {
    _showLoadingDialog(context, 'Karteneingabe wird vorbereitet...');
    try {
      final paymentService = ref.read(paymentServiceProvider);
      await paymentService.setupCard(type);

      if (context.mounted) {
        Navigator.pop(context); // Loading schließen
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('${_typeLabels[type]} hinzugefügt')));
        ref.invalidate(_paymentMethodsProvider);
      }
    } catch (e) {
      if (context.mounted) {
        Navigator.pop(context); // Loading schließen
        _showError(context, e);
      }
    }
  }

  // ── SEPA ──

  Future<void> _addSepaMethod(BuildContext context, WidgetRef ref) async {
    _showLoadingDialog(context, 'SEPA-Einrichtung wird vorbereitet...');
    try {
      final paymentService = ref.read(paymentServiceProvider);
      await paymentService.setupSepa();

      if (context.mounted) {
        Navigator.pop(context);
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('SEPA-Lastschrift hinzugefügt')));
        ref.invalidate(_paymentMethodsProvider);
      }
    } catch (e) {
      if (context.mounted) {
        Navigator.pop(context);
        _showError(context, e);
      }
    }
  }

  // ── PayPal: WebView für Login ──

  Future<void> _addPayPalMethod(BuildContext context, WidgetRef ref) async {
    _showLoadingDialog(context, 'PayPal wird gestartet...');
    try {
      final paymentService = ref.read(paymentServiceProvider);
      final setupResult = await paymentService.startPayPalSetup();
      final approvalUrl = setupResult['approval_url'] as String?;
      final tokenId = setupResult['token_id'] as String?;

      if (approvalUrl == null || approvalUrl.isEmpty) {
        throw Exception('PayPal-Einrichtung fehlgeschlagen');
      }

      if (!context.mounted) return;
      Navigator.pop(context); // Loading schließen

      // WebView für PayPal-Login öffnen
      final confirmed = await Navigator.push<bool>(
        context,
        MaterialPageRoute(
          builder: (_) => _PayPalWebViewPage(
            approvalUrl: approvalUrl,
            successUrlPrefix: 'https://transparent-laden.de/app/paypal-success',
            cancelUrlPrefix: 'https://transparent-laden.de/app/paypal-cancel',
          ),
        ),
      );

      if (confirmed == true && tokenId != null && context.mounted) {
        _showLoadingDialog(context, 'PayPal-Konto wird verknüpft...');
        await paymentService.confirmPayPal(tokenId);

        if (context.mounted) {
          Navigator.pop(context);
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('PayPal-Konto verknüpft')));
          ref.invalidate(_paymentMethodsProvider);
        }
      }
    } catch (e) {
      if (context.mounted) {
        // Sicherstellen, dass ein eventuell offener Loading-Dialog geschlossen wird
        Navigator.of(context, rootNavigator: true).popUntil((route) => route is! DialogRoute);
        _showError(context, e);
      }
    }
  }

  // ── Google Pay ──

  Future<void> _addGooglePayMethod(BuildContext context, WidgetRef ref) async {
    _showLoadingDialog(context, 'Google Pay wird eingerichtet...');
    try {
      final paymentService = ref.read(paymentServiceProvider);
      await paymentService.setupGooglePay();

      if (context.mounted) {
        Navigator.pop(context);
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Google Pay hinzugefügt')));
        ref.invalidate(_paymentMethodsProvider);
      }
    } catch (e) {
      if (context.mounted) {
        Navigator.pop(context);
        _showError(context, e);
      }
    }
  }

  // ── Apple Pay ──

  Future<void> _addApplePayMethod(BuildContext context, WidgetRef ref) async {
    _showLoadingDialog(context, 'Apple Pay wird eingerichtet...');
    try {
      final paymentService = ref.read(paymentServiceProvider);
      await paymentService.setupApplePay();

      if (context.mounted) {
        Navigator.pop(context);
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Apple Pay hinzugefügt')));
        ref.invalidate(_paymentMethodsProvider);
      }
    } catch (e) {
      if (context.mounted) {
        Navigator.pop(context);
        _showError(context, e);
      }
    }
  }

  // ── Hilfsmethoden ──

  void _showLoadingDialog(BuildContext context, String message) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => PopScope(
        canPop: false,
        child: AlertDialog(
          content: Row(
            children: [
              const CircularProgressIndicator(),
              const SizedBox(width: 20),
              Expanded(child: Text(message)),
            ],
          ),
        ),
      ),
    );
  }

  void _showError(BuildContext context, Object error) {
    String message = 'Ein Fehler ist aufgetreten';
    if (error is DioException) {
      message = error.response?.data?['messages']?['error']?.toString() ?? error.message ?? message;
    } else if (error is Exception) {
      final str = error.toString();
      // Stripe StripeException cancellation — kein Fehler anzeigen
      if (str.contains('StripeException') && str.contains('cancel')) return;
      message = str.replaceFirst('Exception: ', '');
    }
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: Theme.of(context).colorScheme.error),
    );
  }
}

class _PaymentOption {
  final String type;
  final IconData icon;
  final String label;
  final String subtitle;
  final VoidCallback onTap;

  const _PaymentOption({
    required this.type,
    required this.icon,
    required this.label,
    required this.subtitle,
    required this.onTap,
  });
}

/// PayPal-Login im WebView.
/// Wartet auf Redirect zu success/cancel URL.
class _PayPalWebViewPage extends StatefulWidget {
  final String approvalUrl;
  final String successUrlPrefix;
  final String cancelUrlPrefix;

  const _PayPalWebViewPage({
    required this.approvalUrl,
    required this.successUrlPrefix,
    required this.cancelUrlPrefix,
  });

  @override
  State<_PayPalWebViewPage> createState() => _PayPalWebViewPageState();
}

class _PayPalWebViewPageState extends State<_PayPalWebViewPage> {
  late final WebViewController _controller;

  @override
  void initState() {
    super.initState();
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(
        NavigationDelegate(
          onNavigationRequest: (request) {
            if (request.url.startsWith(widget.successUrlPrefix)) {
              Navigator.pop(context, true);
              return NavigationDecision.prevent;
            }
            if (request.url.startsWith(widget.cancelUrlPrefix)) {
              Navigator.pop(context, false);
              return NavigationDecision.prevent;
            }
            return NavigationDecision.navigate;
          },
        ),
      )
      ..loadRequest(Uri.parse(widget.approvalUrl));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('PayPal-Anmeldung'),
        leading: IconButton(
          icon: const Icon(Icons.close),
          onPressed: () => Navigator.pop(context, false),
        ),
      ),
      body: WebViewWidget(controller: _controller),
    );
  }
}
