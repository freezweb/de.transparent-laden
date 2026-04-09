import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:einfach_laden/features/auth/presentation/screens/login_screen.dart';
import 'package:einfach_laden/features/auth/presentation/screens/register_screen.dart';
import 'package:einfach_laden/features/home/presentation/screens/home_screen.dart';
import 'package:einfach_laden/features/map/presentation/screens/map_screen.dart';
import 'package:einfach_laden/features/charging/presentation/screens/charging_screen.dart';
import 'package:einfach_laden/features/charging/presentation/screens/session_detail_screen.dart';
import 'package:einfach_laden/features/profile/presentation/screens/profile_screen.dart';
import 'package:einfach_laden/features/invoices/presentation/screens/invoices_screen.dart';
import 'package:einfach_laden/features/charge_point/presentation/screens/charge_point_detail_screen.dart';
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

      // Main app with bottom nav
      ShellRoute(
        builder: (context, state, child) => AppShell(child: child),
        routes: [
          GoRoute(
            path: '/',
            builder: (context, state) => const HomeScreen(),
          ),
          GoRoute(
            path: '/map',
            builder: (context, state) => const MapScreen(),
          ),
          GoRoute(
            path: '/charging',
            builder: (context, state) => const ChargingScreen(),
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
    ],
  );
});
