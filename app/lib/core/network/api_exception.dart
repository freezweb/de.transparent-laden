import 'package:dio/dio.dart';

class ApiException implements Exception {
  final String message;
  ApiException(this.message);

  @override
  String toString() => message;

  static ApiException fromDioException(DioException e) {
    switch (e.type) {
      case DioExceptionType.connectionTimeout:
      case DioExceptionType.sendTimeout:
      case DioExceptionType.receiveTimeout:
        return ApiException('Server nicht erreichbar. Bitte prüfe deine Internetverbindung.');
      case DioExceptionType.connectionError:
        return ApiException('Keine Verbindung zum Server. Bitte prüfe deine Internetverbindung.');
      case DioExceptionType.badResponse:
        return _fromResponse(e.response);
      case DioExceptionType.cancel:
        return ApiException('Anfrage abgebrochen.');
      default:
        return ApiException('Ein unerwarteter Fehler ist aufgetreten.');
    }
  }

  static ApiException _fromResponse(dynamic response) {
    if (response == null) {
      return ApiException('Keine Antwort vom Server.');
    }

    final statusCode = response.statusCode ?? 0;
    final data = response.data;

    // Try to extract error message from API response
    String? serverMessage;
    if (data is Map<String, dynamic>) {
      serverMessage = data['messages']?.values?.first?.toString()
          ?? data['message']?.toString()
          ?? data['error']?.toString();
    }

    switch (statusCode) {
      case 400:
        return ApiException(serverMessage ?? 'Ungültige Anfrage.');
      case 401:
        return ApiException(serverMessage ?? 'Ungültige Anmeldedaten.');
      case 409:
        return ApiException(serverMessage ?? 'Diese E-Mail ist bereits registriert.');
      case 422:
        return ApiException(serverMessage ?? 'Bitte überprüfe deine Eingaben.');
      case 429:
        return ApiException('Zu viele Anfragen. Bitte warte einen Moment.');
      case 500:
        return ApiException('Serverfehler. Bitte versuche es später erneut.');
      default:
        return ApiException(serverMessage ?? 'Fehler ($statusCode).');
    }
  }
}
