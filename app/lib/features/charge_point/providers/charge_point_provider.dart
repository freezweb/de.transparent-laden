import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:einfach_laden/features/charge_point/data/charge_point_repository.dart';
import 'package:einfach_laden/core/data/demo_data.dart';

final nearbyChargePointsProvider =
    FutureProvider.family<List<Map<String, dynamic>>, ({double lat, double lng, double radius})>(
  (ref, params) async {
    try {
      final repo = ref.watch(chargePointRepositoryProvider);
      final points = await repo.getNearby(lat: params.lat, lng: params.lng, radius: params.radius);
      if (points.isNotEmpty) return points;
    } catch (_) {}
    return DemoData.chargePoints();
  },
);

final chargePointDetailProvider = FutureProvider.family<Map<String, dynamic>, int>(
  (ref, id) {
    final repo = ref.watch(chargePointRepositoryProvider);
    return repo.getDetail(id);
  },
);

final chargePointPricingProvider = FutureProvider.family<Map<String, dynamic>, int>(
  (ref, connectorId) {
    final repo = ref.watch(chargePointRepositoryProvider);
    return repo.getPricing(connectorId);
  },
);
