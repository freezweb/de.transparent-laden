import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:geolocator/geolocator.dart';
import 'package:einfach_laden/features/charge_point/data/charge_point_repository.dart';

class QrScanScreen extends ConsumerStatefulWidget {
  const QrScanScreen({super.key});

  @override
  ConsumerState<QrScanScreen> createState() => _QrScanScreenState();
}

class _QrScanScreenState extends ConsumerState<QrScanScreen> {
  final MobileScannerController _controller = MobileScannerController();
  bool _isProcessing = false;
  String? _lastResult;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('QR-Code scannen'),
        actions: [
          IconButton(
            icon: const Icon(Icons.flash_on),
            onPressed: () => _controller.toggleTorch(),
          ),
          IconButton(
            icon: const Icon(Icons.flip_camera_android),
            onPressed: () => _controller.switchCamera(),
          ),
        ],
      ),
      body: Stack(
        children: [
          MobileScanner(
            controller: _controller,
            onDetect: _onDetect,
          ),
          // Scan overlay
          Center(
            child: Container(
              width: 250,
              height: 250,
              decoration: BoxDecoration(
                border: Border.all(color: Theme.of(context).colorScheme.primary, width: 3),
                borderRadius: BorderRadius.circular(16),
              ),
            ),
          ),
          // Instructions
          Positioned(
            bottom: 80,
            left: 20,
            right: 20,
            child: Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.qr_code_scanner, size: 32, color: Theme.of(context).colorScheme.primary),
                    const SizedBox(height: 8),
                    const Text(
                      'Richte die Kamera auf den QR-Code an der Ladestation',
                      textAlign: TextAlign.center,
                      style: TextStyle(fontSize: 14),
                    ),
                    if (_isProcessing) ...[
                      const SizedBox(height: 12),
                      const LinearProgressIndicator(),
                      const SizedBox(height: 4),
                      const Text('Wird verarbeitet...', style: TextStyle(fontSize: 12)),
                    ],
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _onDetect(BarcodeCapture capture) async {
    if (_isProcessing) return;
    final barcode = capture.barcodes.firstOrNull;
    if (barcode == null || barcode.rawValue == null) return;

    final qrContent = barcode.rawValue!;
    if (qrContent == _lastResult) return;

    setState(() {
      _isProcessing = true;
      _lastResult = qrContent;
    });

    try {
      // Get current location for logging
      Position? position;
      try {
        position = await Geolocator.getCurrentPosition(
          locationSettings: const LocationSettings(accuracy: LocationAccuracy.medium),
        );
      } catch (_) {
        // Location not available is fine
      }

      final repo = ref.read(chargePointRepositoryProvider);
      final result = await repo.logQrScan(
        qrContent: qrContent,
        latitude: position?.latitude,
        longitude: position?.longitude,
      );

      if (!mounted) return;

      final recognized = result['recognized'] == true;
      final startable = result['startable'] == true;
      final chargePointId = result['charge_point_id'];

      if (recognized && startable && chargePointId != null) {
        // Station found and startable — navigate to detail
        context.push('/charge-point/$chargePointId');
      } else if (recognized && !startable) {
        // Station recognized but not startable
        _showResultDialog(
          icon: Icons.info_outline,
          color: Colors.orange,
          title: 'Station erkannt',
          message: 'Diese Ladestation ist bekannt, kann aber derzeit nicht über Transparent Laden gestartet werden.',
          chargePointId: chargePointId,
        );
      } else {
        // Not recognized
        _showResultDialog(
          icon: Icons.help_outline,
          color: Colors.red,
          title: 'Nicht erkannt',
          message: 'Dieser QR-Code konnte keiner bekannten Ladestation zugeordnet werden. Der Scan wurde für eine spätere Auswertung gespeichert.',
        );
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Fehler beim Verarbeiten des QR-Codes: $e')),
      );
    } finally {
      if (mounted) {
        setState(() => _isProcessing = false);
      }
    }
  }

  void _showResultDialog({
    required IconData icon,
    required Color color,
    required String title,
    required String message,
    int? chargePointId,
  }) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        icon: Icon(icon, color: color, size: 48),
        title: Text(title),
        content: Text(message),
        actions: [
          if (chargePointId != null)
            TextButton(
              onPressed: () {
                Navigator.of(ctx).pop();
                context.push('/charge-point/$chargePointId');
              },
              child: const Text('Station ansehen'),
            ),
          TextButton(
            onPressed: () {
              Navigator.of(ctx).pop();
              setState(() => _lastResult = null);
            },
            child: const Text('Erneut scannen'),
          ),
        ],
      ),
    );
  }
}
