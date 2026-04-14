import 'dart:ui' as ui;
import 'package:flutter/material.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';

/// Generates custom map markers in EV charging app standard:
/// - 1 bolt = AC (≤22 kW)
/// - 2 bolts = DC (50–149 kW)
/// - 3 bolts = HPC (≥150 kW)
/// - Green = free + startable through us
/// - Grey = not startable / unknown
/// - Blue outline = external (OSM)
class MarkerGenerator {
  static final Map<String, BitmapDescriptor> _cache = {};

  /// Returns the number of lightning bolts for a given max power.
  static int boltCount(double maxPowerKw) {
    if (maxPowerKw >= 150) return 3;
    if (maxPowerKw >= 43) return 2;
    return 1;
  }

  /// Get or create a cached marker icon.
  static Future<BitmapDescriptor> getMarker({
    required double maxPowerKw,
    required bool isStartable,
    required bool hasFreeSpot,
    bool isExternal = false,
  }) async {
    final bolts = boltCount(maxPowerKw);
    final green = isStartable && hasFreeSpot;
    final key = '${bolts}_${green}_$isExternal';

    if (_cache.containsKey(key)) return _cache[key]!;

    final descriptor = await _paintMarker(
      bolts: bolts,
      isGreen: green,
      isExternal: isExternal,
    );
    _cache[key] = descriptor;
    return descriptor;
  }

  static Future<BitmapDescriptor> _paintMarker({
    required int bolts,
    required bool isGreen,
    required bool isExternal,
  }) async {
    const double size = 80;
    const double pinHeight = 100;

    final recorder = ui.PictureRecorder();
    final canvas = Canvas(recorder, Rect.fromLTWH(0, 0, size, pinHeight));

    // Pin body color
    final Color bodyColor = isGreen
        ? const Color(0xFF2E7D32) // dark green
        : const Color(0xFF616161); // grey

    final Color borderColor = isExternal
        ? const Color(0xFF1565C0) // blue border for OSM
        : bodyColor;

    // Draw pin shape (circle + triangle point)
    final pinPaint = Paint()..color = bodyColor..style = PaintingStyle.fill;
    final borderPaint = Paint()
      ..color = borderColor
      ..style = PaintingStyle.stroke
      ..strokeWidth = 3;

    // Circle
    const center = Offset(size / 2, size / 2 - 4);
    const radius = size / 2 - 4;
    canvas.drawCircle(center, radius, pinPaint);
    canvas.drawCircle(center, radius, borderPaint);

    // Pin point triangle
    final path = Path()
      ..moveTo(size / 2 - 14, size / 2 + 20)
      ..lineTo(size / 2, pinHeight - 4)
      ..lineTo(size / 2 + 14, size / 2 + 20)
      ..close();
    canvas.drawPath(path, pinPaint);

    // Draw lightning bolt(s)
    _drawBolts(canvas, bolts, size, isGreen);

    final image = await recorder.endRecording().toImage(size.toInt(), pinHeight.toInt());
    final bytes = await image.toByteData(format: ui.ImageByteFormat.png);

    return BitmapDescriptor.bytes(bytes!.buffer.asUint8List(), height: 48, width: 38);
  }

  static void _drawBolts(Canvas canvas, int count, double size, bool isGreen) {
    final boltColor = isGreen
        ? const Color(0xFFFFFF00) // yellow on green
        : const Color(0xFFFFD600); // amber on grey

    final paint = Paint()
      ..color = boltColor
      ..style = PaintingStyle.fill;

    final strokePaint = Paint()
      ..color = Colors.black.withAlpha(80)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 0.8;

    if (count == 1) {
      _drawSingleBolt(canvas, size / 2, size / 2 - 6, 14, paint, strokePaint);
    } else if (count == 2) {
      _drawSingleBolt(canvas, size / 2 - 9, size / 2 - 6, 11, paint, strokePaint);
      _drawSingleBolt(canvas, size / 2 + 9, size / 2 - 6, 11, paint, strokePaint);
    } else {
      _drawSingleBolt(canvas, size / 2 - 14, size / 2 - 6, 10, paint, strokePaint);
      _drawSingleBolt(canvas, size / 2, size / 2 - 6, 10, paint, strokePaint);
      _drawSingleBolt(canvas, size / 2 + 14, size / 2 - 6, 10, paint, strokePaint);
    }
  }

  /// Draw a single lightning bolt at (cx, cy) with given scale.
  static void _drawSingleBolt(Canvas canvas, double cx, double cy, double h, Paint fill, Paint stroke) {
    final bolt = Path()
      ..moveTo(cx + 1, cy - h * 0.55)
      ..lineTo(cx - h * 0.35, cy + h * 0.05)
      ..lineTo(cx - 1, cy + h * 0.05)
      ..lineTo(cx - 1, cy + h * 0.55)
      ..lineTo(cx + h * 0.35, cy - h * 0.05)
      ..lineTo(cx + 1, cy - h * 0.05)
      ..close();
    canvas.drawPath(bolt, fill);
    canvas.drawPath(bolt, stroke);
  }

  /// Clear the marker cache (e.g. on theme change).
  static void clearCache() => _cache.clear();
}
