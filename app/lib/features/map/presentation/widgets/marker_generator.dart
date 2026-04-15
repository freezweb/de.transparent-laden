import 'dart:ui' as ui;
import 'package:flutter/material.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';

/// Generates custom map markers - EnBW-inspired design.
///
/// Color logic:
/// - Green (#4CAF50) = free + startable through us
/// - Red (#F44336)   = occupied or out of service
/// - Amber (#FFB300) = external station / not startable through us
/// - Grey (#9E9E9E)  = status unknown / error
///
/// Marker shows kW number (e.g. "22", "50", "150") for instant power recognition.
/// Larger circle than default for better readability.
class MarkerGenerator {
  static final Map<String, BitmapDescriptor> _cache = {};

  /// Speed category label for display.
  static String speedLabel(double maxPowerKw) {
    if (maxPowerKw >= 150) return 'HPC';
    if (maxPowerKw >= 43) return 'DC';
    return 'AC';
  }

  /// Determine marker color from station state.
  static MarkerColor markerColor({
    required bool isStartable,
    required bool isExternal,
    required bool statusKnown,
    required int available,
    required int total,
  }) {
    // External / not startable → amber
    if (isExternal || !isStartable) return MarkerColor.amber;
    // Status unknown
    if (!statusKnown || total == 0) return MarkerColor.grey;
    // At least 1 free → green
    if (available > 0) return MarkerColor.green;
    // All occupied / out of service → red
    return MarkerColor.red;
  }

  /// Get or create a cached marker icon.
  static Future<BitmapDescriptor> getMarker({
    required double maxPowerKw,
    required MarkerColor color,
  }) async {
    final kwLabel = maxPowerKw >= 100 ? '${maxPowerKw.toInt()}' : '${maxPowerKw.toInt()}';
    final key = '${color.name}_$kwLabel';

    if (_cache.containsKey(key)) return _cache[key]!;

    final descriptor = await _paintMarker(kwLabel: kwLabel, color: color);
    _cache[key] = descriptor;
    return descriptor;
  }

  static Future<BitmapDescriptor> _paintMarker({
    required String kwLabel,
    required MarkerColor color,
  }) async {
    const double circleSize = 96;
    const double pinHeight = 120;
    const double circleRadius = 40;
    const center = Offset(circleSize / 2, circleRadius + 6);

    final recorder = ui.PictureRecorder();
    final canvas = Canvas(recorder, Rect.fromLTWH(0, 0, circleSize, pinHeight));

    final Color fillColor = switch (color) {
      MarkerColor.green => const Color(0xFF4CAF50),
      MarkerColor.red => const Color(0xFFF44336),
      MarkerColor.amber => const Color(0xFFFFB300),
      MarkerColor.grey => const Color(0xFF9E9E9E),
    };

    final Color borderColor = switch (color) {
      MarkerColor.green => const Color(0xFF2E7D32),
      MarkerColor.red => const Color(0xFFC62828),
      MarkerColor.amber => const Color(0xFFFF8F00),
      MarkerColor.grey => const Color(0xFF616161),
    };

    // Shadow
    final shadowPaint = Paint()
      ..color = Colors.black.withAlpha(40)
      ..maskFilter = const MaskFilter.blur(BlurStyle.normal, 4);
    canvas.drawCircle(Offset(center.dx + 1, center.dy + 2), circleRadius, shadowPaint);

    // Pin point triangle
    final pinPath = Path()
      ..moveTo(circleSize / 2 - 12, center.dy + circleRadius - 10)
      ..lineTo(circleSize / 2, pinHeight - 2)
      ..lineTo(circleSize / 2 + 12, center.dy + circleRadius - 10)
      ..close();
    canvas.drawPath(pinPath, Paint()..color = fillColor);
    canvas.drawPath(pinPath, Paint()..color = borderColor..style = PaintingStyle.stroke..strokeWidth = 2);

    // Main circle fill
    canvas.drawCircle(center, circleRadius, Paint()..color = fillColor);

    // Circle border
    canvas.drawCircle(center, circleRadius, Paint()
      ..color = borderColor
      ..style = PaintingStyle.stroke
      ..strokeWidth = 3);

    // Inner white ring for contrast
    canvas.drawCircle(center, circleRadius - 4, Paint()
      ..color = Colors.white.withAlpha(50)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1);

    // Small bolt icon at top
    _drawSmallBolt(canvas, center.dx, center.dy - 16, 8, Paint()..color = Colors.white);

    // kW text
    final textPainter = TextPainter(
      text: TextSpan(
        text: kwLabel,
        style: TextStyle(
          color: Colors.white,
          fontSize: kwLabel.length > 2 ? 16 : 20,
          fontWeight: FontWeight.w900,
          shadows: [Shadow(color: Colors.black.withAlpha(80), blurRadius: 2)],
        ),
      ),
      textDirection: TextDirection.ltr,
    )..layout();
    textPainter.paint(canvas, Offset(center.dx - textPainter.width / 2, center.dy - 2));

    // "kW" small label below number
    final kwPainter = TextPainter(
      text: TextSpan(
        text: 'kW',
        style: TextStyle(
          color: Colors.white.withAlpha(200),
          fontSize: 9,
          fontWeight: FontWeight.w700,
        ),
      ),
      textDirection: TextDirection.ltr,
    )..layout();
    kwPainter.paint(canvas, Offset(center.dx - kwPainter.width / 2, center.dy + 14));

    final image = await recorder.endRecording().toImage(circleSize.toInt(), pinHeight.toInt());
    final bytes = await image.toByteData(format: ui.ImageByteFormat.png);

    return BitmapDescriptor.bytes(bytes!.buffer.asUint8List(), height: 56, width: 44);
  }

  static void _drawSmallBolt(Canvas canvas, double cx, double cy, double h, Paint paint) {
    final bolt = Path()
      ..moveTo(cx + 1, cy - h * 0.5)
      ..lineTo(cx - h * 0.3, cy + h * 0.05)
      ..lineTo(cx - 0.5, cy + h * 0.05)
      ..lineTo(cx - 1, cy + h * 0.5)
      ..lineTo(cx + h * 0.3, cy - h * 0.05)
      ..lineTo(cx + 0.5, cy - h * 0.05)
      ..close();
    canvas.drawPath(bolt, paint);
  }

  /// Clear the marker cache (e.g. on theme change).
  static void clearCache() => _cache.clear();
}

enum MarkerColor { green, red, amber, grey }

