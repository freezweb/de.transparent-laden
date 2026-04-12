import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:dio/dio.dart';
import 'package:einfach_laden/core/network/api_client.dart';

final _notifPrefsProvider = FutureProvider.autoDispose<List<dynamic>>((ref) async {
  final dio = ref.watch(dioProvider);
  final response = await dio.get('/notifications/preferences');
  return (response.data['preferences'] as List?) ?? [];
});

class NotificationSettingsScreen extends ConsumerStatefulWidget {
  const NotificationSettingsScreen({super.key});

  @override
  ConsumerState<NotificationSettingsScreen> createState() => _NotificationSettingsScreenState();
}

class _NotificationSettingsScreenState extends ConsumerState<NotificationSettingsScreen> {
  Map<String, bool> _prefs = {};
  bool _loaded = false;
  bool _saving = false;

  static const _eventLabels = {
    'session_started': 'Ladevorgang gestartet',
    'session_completed': 'Ladevorgang abgeschlossen',
    'session_failed': 'Ladevorgang fehlgeschlagen',
    'cost_threshold': 'Kostengrenze erreicht',
    'invoice_created': 'Neue Rechnung',
    'subscription_expiring': 'Abo läuft bald ab',
    'subscription_cancelled': 'Abo gekündigt',
  };

  static const _eventDescriptions = {
    'session_started': 'Benachrichtigung wenn ein Ladevorgang beginnt',
    'session_completed': 'Benachrichtigung wenn der Ladevorgang abgeschlossen ist',
    'session_failed': 'Benachrichtigung bei Fehlern während des Ladens',
    'cost_threshold': 'Benachrichtigung bei Erreichen der eingestellten Kostengrenze',
    'invoice_created': 'Benachrichtigung wenn eine neue Rechnung verfügbar ist',
    'subscription_expiring': 'Erinnerung bevor dein Abo ausläuft',
    'subscription_cancelled': 'Bestätigung bei Abo-Kündigung',
  };

  static const _sectionMap = {
    'Ladevorgänge': ['session_started', 'session_completed', 'session_failed', 'cost_threshold'],
    'Abrechnung': ['invoice_created'],
    'Abo': ['subscription_expiring', 'subscription_cancelled'],
  };

  @override
  Widget build(BuildContext context) {
    final prefsAsync = ref.watch(_notifPrefsProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Push-Einstellungen'),
        actions: [
          if (_loaded)
            TextButton(
              onPressed: _saving ? null : _save,
              child: _saving
                  ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2))
                  : const Text('Speichern'),
            ),
        ],
      ),
      body: prefsAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.error_outline, size: 48, color: Colors.grey),
              const SizedBox(height: 8),
              Text('Fehler beim Laden', style: Theme.of(context).textTheme.bodyLarge),
              const SizedBox(height: 8),
              FilledButton(onPressed: () => ref.invalidate(_notifPrefsProvider), child: const Text('Erneut versuchen')),
            ],
          ),
        ),
        data: (list) {
          if (!_loaded) {
            _prefs = {};
            for (final item in list) {
              final m = item as Map<String, dynamic>;
              _prefs[m['event_type'] as String] = m['enabled'] == true || m['enabled'] == 1;
            }
            _loaded = true;
          }

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              for (final section in _sectionMap.entries) ...[
                Padding(
                  padding: const EdgeInsets.only(top: 8, bottom: 8),
                  child: Text(section.key, style: Theme.of(context).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.w600)),
                ),
                Card(
                  child: Column(
                    children: section.value.map((eventType) {
                      return SwitchListTile(
                        title: Text(_eventLabels[eventType] ?? eventType),
                        subtitle: Text(_eventDescriptions[eventType] ?? '', style: Theme.of(context).textTheme.bodySmall),
                        value: _prefs[eventType] ?? true,
                        onChanged: (val) => setState(() => _prefs[eventType] = val),
                      );
                    }).toList(),
                  ),
                ),
              ],
            ],
          );
        },
      ),
    );
  }

  Future<void> _save() async {
    setState(() => _saving = true);

    try {
      final dio = ref.read(dioProvider);
      await dio.put('/notifications/preferences', data: {
        'preferences': _prefs.entries.map((e) => {'event_type': e.key, 'enabled': e.value}).toList(),
      });

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Einstellungen gespeichert')));
        Navigator.of(context).pop();
      }
    } on DioException catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Fehler: ${e.message}'), backgroundColor: Theme.of(context).colorScheme.error));
      }
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }
}
