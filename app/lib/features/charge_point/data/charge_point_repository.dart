import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:einfach_laden/core/network/api_client.dart';

final chargePointRepositoryProvider = Provider<ChargePointRepository>((ref) {
  return ChargePointRepository(ref.watch(dioProvider));
});

class ChargePointRepository {
  final Dio _dio;
  ChargePointRepository(this._dio);

  Future<List<Map<String, dynamic>>> getNearby({
    required double lat,
    required double lng,
    double radius = 50,
    double? minPowerKw,
    double? maxPowerKw,
    String? connectorType,
    String? currentCategory,
    bool? onlyStartable,
  }) async {
    final params = <String, dynamic>{
      'lat': lat,
      'lng': lng,
      'radius': radius,
    };
    if (minPowerKw != null) params['min_power_kw'] = minPowerKw;
    if (maxPowerKw != null) params['max_power_kw'] = maxPowerKw;
    if (connectorType != null) params['connector_type'] = connectorType;
    if (currentCategory != null) params['current_category'] = currentCategory;
    if (onlyStartable == true) params['only_startable'] = 1;
    final response = await _dio.get('/charge-points/nearby', queryParameters: params);
    return List<Map<String, dynamic>>.from(response.data['charge_points'] ?? []);
  }

  Future<List<Map<String, dynamic>>> getByBoundingBox({
    required double latMin,
    required double lngMin,
    required double latMax,
    required double lngMax,
    double? minPowerKw,
    double? maxPowerKw,
    String? connectorType,
    String? currentCategory,
    bool? onlyStartable,
  }) async {
    final params = <String, dynamic>{
      'lat_min': latMin,
      'lng_min': lngMin,
      'lat_max': latMax,
      'lng_max': lngMax,
    };
    if (minPowerKw != null) params['min_power_kw'] = minPowerKw;
    if (maxPowerKw != null) params['max_power_kw'] = maxPowerKw;
    if (connectorType != null) params['connector_type'] = connectorType;
    if (currentCategory != null) params['current_category'] = currentCategory;
    if (onlyStartable == true) params['only_startable'] = 1;
    final response = await _dio.get('/charge-points/nearby', queryParameters: params);
    return List<Map<String, dynamic>>.from(response.data['charge_points'] ?? []);
  }

  Future<Map<String, dynamic>> getDetail(int id) async {
    final response = await _dio.get('/charge-points/$id');
    return response.data as Map<String, dynamic>;
  }

  /// Fetch only dynamic status for a list of local station IDs.
  Future<Map<String, Map<String, dynamic>>> getStatus(List<int> ids) async {
    if (ids.isEmpty) return {};
    final response = await _dio.get('/charge-points/status', queryParameters: {
      'ids': ids.join(','),
    });
    final data = response.data['statuses'] as Map<String, dynamic>? ?? {};
    return data.map((k, v) => MapEntry(k, Map<String, dynamic>.from(v as Map)));
  }

  Future<Map<String, dynamic>> getPricing(int connectorId) async {
    final response = await _dio.get('/charge-points/$connectorId/pricing');
    return response.data as Map<String, dynamic>;
  }

  Future<List<Map<String, dynamic>>> getReviews(int chargePointId) async {
    final response = await _dio.get('/charge-points/$chargePointId/reviews');
    return List<Map<String, dynamic>>.from(response.data['reviews'] ?? []);
  }

  Future<Map<String, dynamic>> submitReview({
    required int chargePointId,
    required int rating,
    String? comment,
  }) async {
    final response = await _dio.post('/charge-points/$chargePointId/reviews', data: {
      'rating': rating,
      'comment': comment,
    });
    return response.data as Map<String, dynamic>;
  }

  Future<void> uploadReviewImage(int reviewId, List<int> imageBytes, String filename) async {
    final formData = FormData.fromMap({
      'image': MultipartFile.fromBytes(imageBytes, filename: filename),
    });
    await _dio.post('/reviews/$reviewId/images', data: formData);
  }

  Future<void> reportContent({
    required String entityType,
    required int entityId,
    required String reason,
  }) async {
    await _dio.post('/reports', data: {
      'entity_type': entityType,
      'entity_id': entityId,
      'reason': reason,
    });
  }

  Future<Map<String, dynamic>> logQrScan({
    required String qrContent,
    double? latitude,
    double? longitude,
  }) async {
    final response = await _dio.post('/qr-scans', data: {
      'qr_content': qrContent,
      if (latitude != null) 'latitude': latitude,
      if (longitude != null) 'longitude': longitude,
    });
    return response.data as Map<String, dynamic>;
  }
}
