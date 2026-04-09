import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:einfach_laden/core/network/api_client.dart';

final invoiceRepositoryProvider = Provider<InvoiceRepository>((ref) {
  return InvoiceRepository(ref.watch(dioProvider));
});

class InvoiceRepository {
  final Dio _dio;
  InvoiceRepository(this._dio);

  Future<Map<String, dynamic>> getInvoices({int page = 1}) async {
    final response = await _dio.get('/invoices', queryParameters: {'page': page});
    return response.data as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> getInvoice(int id) async {
    final response = await _dio.get('/invoices/$id');
    return response.data as Map<String, dynamic>;
  }
}

final invoiceListProvider = FutureProvider.family<Map<String, dynamic>, int>((ref, page) {
  final repo = ref.watch(invoiceRepositoryProvider);
  return repo.getInvoices(page: page);
});
