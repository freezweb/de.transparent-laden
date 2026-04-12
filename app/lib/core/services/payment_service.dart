import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_stripe/flutter_stripe.dart';
import 'package:einfach_laden/core/network/api_client.dart';

/// Provider für den PaymentService — einmalige Initialisierung.
final paymentServiceProvider = Provider<PaymentService>((ref) {
  return PaymentService(ref);
});

/// Provider für die Payment-Konfiguration vom Backend.
final paymentConfigProvider = FutureProvider<Map<String, dynamic>>((ref) async {
  final dio = ref.watch(dioProvider);
  final response = await dio.get('/payment-methods/config');
  return response.data as Map<String, dynamic>;
});

class PaymentService {
  final Ref _ref;
  bool _initialized = false;

  PaymentService(this._ref);

  /// Stripe SDK initialisieren mit Publishable Key vom Backend.
  Future<void> ensureInitialized() async {
    if (_initialized) return;

    final config = await _ref.read(paymentConfigProvider.future);
    final publishableKey = config['stripe_publishable_key'] as String? ?? '';

    if (publishableKey.isNotEmpty) {
      Stripe.publishableKey = publishableKey;
      Stripe.merchantIdentifier = 'merchant.de.einfachladen';
      await Stripe.instance.applySettings();
      _initialized = true;
    }
  }

  /// Kreditkarte / Debitkarte über Stripe SetupIntent hinzufügen.
  /// Gibt die PaymentMethod-ID zurück, wenn erfolgreich.
  Future<Map<String, dynamic>> setupCard(String type) async {
    await ensureInitialized();
    final dio = _ref.read(dioProvider);

    // 1. SetupIntent vom Backend holen
    final intentResponse = await dio.post('/payment-methods/setup-intent', data: {'type': type});
    final clientSecret = intentResponse.data['client_secret'] as String;

    // 2. Stripe Payment Sheet anzeigen
    await Stripe.instance.initPaymentSheet(
      paymentSheetParameters: SetupPaymentSheetParameters(
        setupIntentClientSecret: clientSecret,
        merchantDisplayName: 'Einfach Laden',
        style: ThemeMode.system,
      ),
    );

    await Stripe.instance.presentPaymentSheet();

    // 3. Backend bestätigen
    final confirmResponse = await dio.post('/payment-methods/confirm-stripe', data: {
      'setup_intent_id': intentResponse.data['setup_intent_id'],
      'type': type,
    });

    return confirmResponse.data as Map<String, dynamic>;
  }

  /// SEPA-Lastschrift über Stripe hinzufügen.
  Future<Map<String, dynamic>> setupSepa() async {
    await ensureInitialized();
    final dio = _ref.read(dioProvider);

    final intentResponse = await dio.post('/payment-methods/setup-intent', data: {'type': 'sepa'});
    final clientSecret = intentResponse.data['client_secret'] as String;

    await Stripe.instance.initPaymentSheet(
      paymentSheetParameters: SetupPaymentSheetParameters(
        setupIntentClientSecret: clientSecret,
        merchantDisplayName: 'Einfach Laden',
        style: ThemeMode.system,
      ),
    );

    await Stripe.instance.presentPaymentSheet();

    final confirmResponse = await dio.post('/payment-methods/confirm-stripe', data: {
      'setup_intent_id': intentResponse.data['setup_intent_id'],
      'type': 'sepa',
    });

    return confirmResponse.data as Map<String, dynamic>;
  }

  /// Google Pay über Stripe (nur Android).
  Future<Map<String, dynamic>> setupGooglePay() async {
    if (!Platform.isAndroid) throw Exception('Google Pay ist nur auf Android verfügbar');
    await ensureInitialized();
    final dio = _ref.read(dioProvider);

    final intentResponse = await dio.post('/payment-methods/setup-intent', data: {'type': 'google_pay'});
    final clientSecret = intentResponse.data['client_secret'] as String;

    await Stripe.instance.initPaymentSheet(
      paymentSheetParameters: SetupPaymentSheetParameters(
        setupIntentClientSecret: clientSecret,
        merchantDisplayName: 'Einfach Laden',
        googlePay: const PaymentSheetGooglePay(
          merchantCountryCode: 'DE',
          currencyCode: 'EUR',
          testEnv: true,
        ),
        style: ThemeMode.system,
      ),
    );

    await Stripe.instance.presentPaymentSheet();

    final confirmResponse = await dio.post('/payment-methods/confirm-stripe', data: {
      'setup_intent_id': intentResponse.data['setup_intent_id'],
      'type': 'google_pay',
    });

    return confirmResponse.data as Map<String, dynamic>;
  }

  /// Apple Pay über Stripe (nur iOS).
  Future<Map<String, dynamic>> setupApplePay() async {
    if (!Platform.isIOS) throw Exception('Apple Pay ist nur auf iOS verfügbar');
    await ensureInitialized();
    final dio = _ref.read(dioProvider);

    final intentResponse = await dio.post('/payment-methods/setup-intent', data: {'type': 'apple_pay'});
    final clientSecret = intentResponse.data['client_secret'] as String;

    await Stripe.instance.initPaymentSheet(
      paymentSheetParameters: SetupPaymentSheetParameters(
        setupIntentClientSecret: clientSecret,
        merchantDisplayName: 'Einfach Laden',
        applePay: const PaymentSheetApplePay(
          merchantCountryCode: 'DE',
        ),
        style: ThemeMode.system,
      ),
    );

    await Stripe.instance.presentPaymentSheet();

    final confirmResponse = await dio.post('/payment-methods/confirm-stripe', data: {
      'setup_intent_id': intentResponse.data['setup_intent_id'],
      'type': 'apple_pay',
    });

    return confirmResponse.data as Map<String, dynamic>;
  }

  /// PayPal-Konto verknüpfen. Gibt die Approval-URL zurück, die im WebView geöffnet wird.
  Future<Map<String, dynamic>> startPayPalSetup() async {
    final dio = _ref.read(dioProvider);
    final response = await dio.post('/payment-methods/paypal/setup', data: {
      'return_url': 'https://transparent-laden.de/app/paypal-success',
      'cancel_url': 'https://transparent-laden.de/app/paypal-cancel',
    });
    return response.data as Map<String, dynamic>;
  }

  /// PayPal-Billing-Agreement nach User-Bestätigung abschließen.
  Future<Map<String, dynamic>> confirmPayPal(String tokenId) async {
    final dio = _ref.read(dioProvider);
    final response = await dio.post('/payment-methods/paypal/confirm', data: {
      'token_id': tokenId,
    });
    return response.data as Map<String, dynamic>;
  }

  /// Prüfe ob Google Pay auf diesem Gerät verfügbar ist.
  bool get isGooglePayAvailable => Platform.isAndroid;

  /// Prüfe ob Apple Pay auf diesem Gerät verfügbar ist.
  bool get isApplePayAvailable => Platform.isIOS;
}
