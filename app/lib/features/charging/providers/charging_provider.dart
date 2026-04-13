import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:einfach_laden/features/charging/data/charging_repository.dart';
import 'package:einfach_laden/core/data/demo_data.dart';

final activeSessionProvider = FutureProvider<Map<String, dynamic>?>((ref) {
  final repo = ref.watch(chargingRepositoryProvider);
  return repo.getActiveSession();
});

final chargingHistoryProvider = FutureProvider.family<Map<String, dynamic>, int>((ref, page) async {
  try {
    final repo = ref.watch(chargingRepositoryProvider);
    final data = await repo.getHistory(page: page);
    final sessions = List.from(data['sessions'] ?? []);
    if (sessions.isNotEmpty) return data;
  } catch (_) {}
  return DemoData.chargingHistory();
});

final sessionStatusProvider = FutureProvider.family<Map<String, dynamic>, int>((ref, sessionId) {
  final repo = ref.watch(chargingRepositoryProvider);
  return repo.getSessionStatus(sessionId);
});

final sessionLiveProvider = FutureProvider.family<Map<String, dynamic>, int>((ref, sessionId) {
  final repo = ref.watch(chargingRepositoryProvider);
  return repo.getLiveStatus(sessionId);
});

final chargingActionsProvider = Provider<ChargingActions>((ref) {
  return ChargingActions(ref);
});

class ChargingActions {
  final Ref _ref;
  ChargingActions(this._ref);

  Future<Map<String, dynamic>> startSession(int connectorId, {int? paymentMethodId}) async {
    final repo = _ref.read(chargingRepositoryProvider);
    final result = await repo.startSession(connectorId, paymentMethodId: paymentMethodId);
    _ref.invalidate(activeSessionProvider);
    return result;
  }

  Future<Map<String, dynamic>> stopSession(int sessionId) async {
    final repo = _ref.read(chargingRepositoryProvider);
    final result = await repo.stopSession(sessionId);
    _ref.invalidate(activeSessionProvider);
    return result;
  }
}
