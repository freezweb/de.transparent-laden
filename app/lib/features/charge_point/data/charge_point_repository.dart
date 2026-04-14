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
  }) async {
    final params = <String, dynamic>{
      'lat': lat,
      'lng': lng,
      'radius': radius,
    };
    if (minPowerKw != null) {
      params['min_power_kw'] = minPowerKw;
    }
    final response = await _dio.get('/charge-points/nearby', queryParameters: params);
    return List<Map<String, dynamic>>.from(response.data['charge_points'] ?? []);
  }

  Future<Map<String, dynamic>> getDetail(int id) async {
    final response = await _dio.get('/charge-points/$id');
    return response.data as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> getPricing(int connectorId) async {
    final response = await _dio.get('/charge-points/$connectorId/pricing');
    return response.data as Map<String, dynamic>;
  }
}
