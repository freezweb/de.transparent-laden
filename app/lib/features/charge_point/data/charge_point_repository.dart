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
    double radius = 10,
  }) async {
    final response = await _dio.get('/charge-points/nearby', queryParameters: {
      'lat': lat,
      'lng': lng,
      'radius': radius,
    });
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
