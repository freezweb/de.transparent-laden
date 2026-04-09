import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:einfach_laden/core/network/api_client.dart';

final chargingRepositoryProvider = Provider<ChargingRepository>((ref) {
  return ChargingRepository(ref.watch(dioProvider));
});

class ChargingRepository {
  final Dio _dio;
  ChargingRepository(this._dio);

  Future<Map<String, dynamic>> startSession(int connectorId, {int? paymentMethodId}) async {
    final response = await _dio.post('/charging/start', data: {
      'connector_id': connectorId,
      if (paymentMethodId != null) 'payment_method_id': paymentMethodId,
    });
    return response.data as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> stopSession(int sessionId) async {
    final response = await _dio.post('/charging/$sessionId/stop');
    return response.data as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> getSessionStatus(int sessionId) async {
    final response = await _dio.get('/charging/$sessionId/status');
    return response.data as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> getLiveStatus(int sessionId) async {
    final response = await _dio.get('/charging/$sessionId/live');
    return response.data as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>?> getActiveSession() async {
    final response = await _dio.get('/charging/active');
    return response.data as Map<String, dynamic>?;
  }

  Future<Map<String, dynamic>> getHistory({int page = 1}) async {
    final response = await _dio.get('/charging/history', queryParameters: {'page': page});
    return response.data as Map<String, dynamic>;
  }
}
