import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:einfach_laden/core/constants/app_constants.dart';
import 'package:einfach_laden/features/auth/data/auth_repository.dart';
import 'package:einfach_laden/features/auth/domain/models/user.dart';

final authStateProvider = AsyncNotifierProvider<AuthNotifier, AuthState>(AuthNotifier.new);

class AuthNotifier extends AsyncNotifier<AuthState> {
  static const _storage = FlutterSecureStorage();

  @override
  Future<AuthState> build() async {
    final token = await _storage.read(key: StorageKeys.accessToken);
    if (token == null) return const AuthState.initial();

    try {
      final repo = ref.read(authRepositoryProvider);
      final data = await repo.getProfile();
      final user = User.fromJson(data['user'] as Map<String, dynamic>);
      return AuthState(user: user, accessToken: token, isAuthenticated: true);
    } catch (_) {
      await _storage.deleteAll();
      return const AuthState.initial();
    }
  }

  Future<void> login(String email, String password) async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(() async {
      final repo = ref.read(authRepositoryProvider);
      final data = await repo.login(email, password);

      await _storage.write(key: StorageKeys.accessToken, value: data['access_token']);
      await _storage.write(key: StorageKeys.refreshToken, value: data['refresh_token']);

      final profileData = await repo.getProfile();
      final user = User.fromJson(profileData['user'] as Map<String, dynamic>);

      return AuthState(user: user, accessToken: data['access_token'], isAuthenticated: true);
    });
  }

  Future<void> register({
    required String email,
    required String password,
    String? firstName,
    String? lastName,
  }) async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(() async {
      final repo = ref.read(authRepositoryProvider);
      final data = await repo.register(
        email: email,
        password: password,
        firstName: firstName,
        lastName: lastName,
      );

      await _storage.write(key: StorageKeys.accessToken, value: data['access_token']);
      await _storage.write(key: StorageKeys.refreshToken, value: data['refresh_token']);

      final profileData = await repo.getProfile();
      final user = User.fromJson(profileData['user'] as Map<String, dynamic>);

      return AuthState(user: user, accessToken: data['access_token'], isAuthenticated: true);
    });
  }

  Future<void> logout() async {
    try {
      final repo = ref.read(authRepositoryProvider);
      await repo.logout();
    } catch (_) {}

    await _storage.deleteAll();
    state = const AsyncData(AuthState.initial());
  }
}
