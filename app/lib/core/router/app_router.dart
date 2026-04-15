import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:einfach_laden/features/auth/presentation/screens/login_screen.dart';
import 'package:einfach_laden/features/auth/presentation/screens/register_screen.dart';
import 'package:einfach_laden/features/home/presentation/screens/home_screen.dart';
import 'package:einfach_laden/features/map/presentation/screens/map_screen.dart';
import 'package:einfach_laden/features/map/presentation/screens/qr_scan_screen.dart';
import 'package:einfach_laden/features/charging/presentation/screens/charging_screen.dart';
import 'package:einfach_laden/features/charging/presentation/screens/session_detail_screen.dart';
import 'package:einfach_laden/features/profile/presentation/screens/profile_screen.dart';
import 'package:einfach_laden/features/profile/presentation/screens/edit_profile_screen.dart';
import 'package:einfach_laden/features/profile/presentation/screens/change_password_screen.dart';
import 'package:einfach_laden/features/invoices/presentation/screens/invoices_screen.dart';
import 'package:einfach_laden/features/charge_point/presentation/screens/charge_point_detail_screen.dart';
import 'package:einfach_laden/features/profile/presentation/screens/payment_methods_screen.dart';
import 'package:einfach_laden/features/profile/presentation/screens/subscription_screen.dart';
import 'package:einfach_laden/features/profile/presentation/screens/notification_settings_screen.dart';
import 'package:einfach_laden/features/vehicle/presentation/screens/vehicle_config_screen.dart';
import 'package:einfach_laden/features/charge_point/presentation/screens/external_station_info_screen.dart';
import 'package:einfach_laden/features/auth/providers/auth_provider.dart';
import 'package:einfach_laden/core/widgets/app_shell.dart';

final appRouterProvider = Provider<GoRouter>((ref) {
  final authState = ref.watch(authStateProvider);

  return GoRouter(
    initialLocation: '/',
    redirect: (context, state) {
      final isLoggedIn = authState.valueOrNull?.isAuthenticated ?? false;
      final isAuthRoute = state.matchedLocation.startsWith('/auth');

      if (!isLoggedIn && !isAuthRoute) return '/auth/login';
      if (isLoggedIn && isAuthRoute) return '/';
      return null;
    },
    routes: [
      // Auth routes
      GoRoute(
        path: '/auth/login',
        builder: (context, state) => const LoginScreen(),
      ),
      GoRoute(
        path: '/auth/register',
        builder: (context, state) => const RegisterScreen(),
      ),

      // Main app with bottom nav — Map is start screen
      ShellRoute(
        builder: (context, state, child) => AppShell(child: child),
        routes: [
          GoRoute(
            path: '/',
            builder: (context, state) => const MapScreen(),
          ),
          GoRoute(
            path: '/scan',
            builder: (context, state) => const QrScanScreen(),
          ),
          GoRoute(
            path: '/charging',
            builder: (context, state) => const ChargingScreen(),
          ),
          GoRoute(
            path: '/dashboard',
            builder: (context, state) => const HomeScreen(),
          ),
          GoRoute(
            path: '/profile',
            builder: (context, state) => const ProfileScreen(),
          ),
        ],
      ),

      // Detail routes
      GoRoute(
        path: '/charge-point/:id',
        builder: (context, state) => ChargePointDetailScreen(
          chargePointId: int.parse(state.pathParameters['id']!),
        ),
      ),
      GoRoute(
        path: '/session/:id',
        builder: (context, state) => SessionDetailScreen(
          sessionId: int.parse(state.pathParameters['id']!),
        ),
      ),
      GoRoute(
        path: '/invoices',
        builder: (context, state) => const InvoicesScreen(),
      ),
      GoRoute(
        path: '/profile/edit',
        builder: (context, state) => const EditProfileScreen(),
      ),
      GoRoute(
        path: '/profile/password',
        builder: (context, state) => const ChangePasswordScreen(),
      ),
      GoRoute(
        path: '/profile/payment-methods',
        builder: (context, state) => const PaymentMethodsScreen(),
      ),
      GoRoute(
        path: '/profile/subscription',
        builder: (context, state) => const SubscriptionScreen(),
      ),
      GoRoute(
        path: '/profile/notifications',
        builder: (context, state) => const NotificationSettingsScreen(),
      ),
      GoRoute(
        path: '/profile/vehicle',
        builder: (context, state) => const VehicleConfigScreen(),
      ),
      GoRoute(
        path: '/station-info/:id',
        builder: (context, state) {
          final station = state.extra as Map<String, dynamic>? ?? {};
          return ExternalStationInfoScreen(station: station);
        },
      ),
    ],
  );
});
