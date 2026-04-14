import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:einfach_laden/features/charge_point/data/charge_point_repository.dart';

final nearbyChargePointsProvider =
    FutureProvider.family<List<Map<String, dynamic>>, ({double lat, double lng, double radius, double? minPowerKw, double? maxPowerKw, String? connectorType, String? currentCategory, bool? onlyStartable})>(
  (ref, params) async {
    final repo = ref.watch(chargePointRepositoryProvider);
    return repo.getNearby(
      lat: params.lat,
      lng: params.lng,
      radius: params.radius,
      minPowerKw: params.minPowerKw,
      maxPowerKw: params.maxPowerKw,
      connectorType: params.connectorType,
      currentCategory: params.currentCategory,
      onlyStartable: params.onlyStartable,
    );
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

final chargePointReviewsProvider = FutureProvider.family<List<Map<String, dynamic>>, int>(
  (ref, chargePointId) {
    final repo = ref.watch(chargePointRepositoryProvider);
    return repo.getReviews(chargePointId);
  },
);
