import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:package_info_plus/package_info_plus.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:einfach_laden/features/auth/providers/auth_provider.dart';

class ProfileScreen extends ConsumerWidget {
  const ProfileScreen({super.key});

  void _showComingSoon(BuildContext context, String feature) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('$feature kommt bald!')),
    );
  }

  Future<void> _showAbout(BuildContext context) async {
    final info = await PackageInfo.fromPlatform();
    if (!context.mounted) return;
    showAboutDialog(
      context: context,
      applicationName: 'Einfach Laden',
      applicationVersion: 'Version ${info.version} (Build ${info.buildNumber})',
      applicationIcon: const Icon(Icons.ev_station, size: 48, color: Colors.green),
      children: [
        const Text('Transparentes Laden für Elektrofahrzeuge.\n\nFaire Preise, volle Transparenz.'),
      ],
    );
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authStateProvider);
    final user = authState.valueOrNull?.user;

    return Scaffold(
      appBar: AppBar(title: const Text('Profil')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // User Info
          Card(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                children: [
                  CircleAvatar(
                    radius: 40,
                    backgroundColor: Theme.of(context).colorScheme.primaryContainer,
                    child: Text(
                      user?.displayName.substring(0, 1).toUpperCase() ?? '?',
                      style: Theme.of(context).textTheme.headlineMedium,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    user?.displayName ?? '',
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
                  ),
                  Text(user?.email ?? '', style: Theme.of(context).textTheme.bodyMedium),
                ],
              ),
            ),
          ),

          const SizedBox(height: 16),

          // Menu Items
          _MenuSection(
            title: 'Konto',
            items: [
              _MenuItem(icon: Icons.person, label: 'Profil bearbeiten', onTap: () => context.push('/profile/edit')),
              _MenuItem(icon: Icons.lock, label: 'Passwort ändern', onTap: () => context.push('/profile/password')),
              _MenuItem(icon: Icons.payment, label: 'Zahlungsmethoden', onTap: () => context.push('/profile/payment-methods')),
            ],
          ),

          _MenuSection(
            title: 'Abo',
            items: [
              _MenuItem(icon: Icons.card_membership, label: 'Abo verwalten', onTap: () => context.push('/profile/subscription')),
            ],
          ),

          _MenuSection(
            title: 'Benachrichtigungen',
            items: [
              _MenuItem(icon: Icons.notifications, label: 'Push-Einstellungen', onTap: () => context.push('/profile/notifications')),
            ],
          ),

          _MenuSection(
            title: 'Sonstiges',
            items: [
              _MenuItem(icon: Icons.receipt_long, label: 'Rechnungen', onTap: () => context.push('/invoices')),
              _MenuItem(icon: Icons.info, label: 'Über die App', onTap: () => _showAbout(context)),
              _MenuItem(
                icon: Icons.description,
                label: 'Datenschutz',
                onTap: () => launchUrl(Uri.parse('https://transparent-laden.de/datenschutz'), mode: LaunchMode.externalApplication),
              ),
            ],
          ),

          const SizedBox(height: 16),

          // Logout
          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: () => ref.read(authStateProvider.notifier).logout(),
              icon: const Icon(Icons.logout, color: Colors.red),
              label: const Text('Abmelden', style: TextStyle(color: Colors.red)),
              style: OutlinedButton.styleFrom(
                side: const BorderSide(color: Colors.red),
                padding: const EdgeInsets.symmetric(vertical: 14),
              ),
            ),
          ),

          const SizedBox(height: 32),
          FutureBuilder<PackageInfo>(
            future: PackageInfo.fromPlatform(),
            builder: (context, snapshot) {
              final version = snapshot.data?.version ?? '...';
              final build = snapshot.data?.buildNumber ?? '';
              return Center(
                child: Text('Version $version ($build)', style: Theme.of(context).textTheme.bodySmall),
              );
            },
          ),
        ],
      ),
    );
  }
}

class _MenuSection extends StatelessWidget {
  final String title;
  final List<_MenuItem> items;
  const _MenuSection({required this.title, required this.items});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(vertical: 8),
          child: Text(title, style: Theme.of(context).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.w600)),
        ),
        Card(
          child: Column(
            children: items.map((item) {
              return ListTile(
                leading: Icon(item.icon),
                title: Text(item.label),
                trailing: const Icon(Icons.chevron_right),
                onTap: item.onTap,
              );
            }).toList(),
          ),
        ),
      ],
    );
  }
}

class _MenuItem {
  final IconData icon;
  final String label;
  final VoidCallback onTap;
  const _MenuItem({required this.icon, required this.label, required this.onTap});
}
