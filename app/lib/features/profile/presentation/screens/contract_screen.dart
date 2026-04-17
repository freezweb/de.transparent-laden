import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:dio/dio.dart';
import 'package:einfach_laden/core/network/api_client.dart';

final _contractStatusProvider = FutureProvider.autoDispose<Map<String, dynamic>>((ref) async {
  final dio = ref.watch(dioProvider);
  final response = await dio.get('/contract/status');
  return response.data as Map<String, dynamic>;
});

final _termsProvider = FutureProvider.autoDispose<Map<String, dynamic>>((ref) async {
  final dio = ref.watch(dioProvider);
  final response = await dio.get('/contract/terms');
  return response.data as Map<String, dynamic>;
});

class ContractScreen extends ConsumerStatefulWidget {
  const ContractScreen({super.key});

  @override
  ConsumerState<ContractScreen> createState() => _ContractScreenState();
}

class _ContractScreenState extends ConsumerState<ContractScreen> {
  bool _termsRead = false;
  bool _accepting = false;
  bool _waiving = false;

  @override
  Widget build(BuildContext context) {
    final status = ref.watch(_contractStatusProvider);
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(title: const Text('Vertrag & AGB')),
      body: status.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.error_outline, size: 48, color: Colors.red),
              const SizedBox(height: 8),
              Text('Fehler: $e', textAlign: TextAlign.center),
              const SizedBox(height: 16),
              OutlinedButton(
                onPressed: () => ref.invalidate(_contractStatusProvider),
                child: const Text('Erneut versuchen'),
              ),
            ],
          ),
        ),
        data: (contractStatus) {
          final termsAccepted = contractStatus['terms_accepted'] == true;
          final withdrawalWaived = contractStatus['withdrawal_waived'] == true;
          final canCharge = contractStatus['can_charge'] == true;

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              // Status Overview
              _StatusCard(
                termsAccepted: termsAccepted,
                withdrawalWaived: withdrawalWaived,
                canCharge: canCharge,
              ),
              const SizedBox(height: 16),

              // Step 1: AGB
              _buildStep(
                context: context,
                number: 1,
                title: 'Allgemeine Geschäftsbedingungen',
                subtitle: termsAccepted
                    ? 'Akzeptiert am ${_formatDate(contractStatus['terms_accepted_at'])} (v${contractStatus['terms_version']})'
                    : 'Bitte lesen und akzeptieren',
                completed: termsAccepted,
                child: termsAccepted
                    ? _buildViewTermsButton()
                    : _buildAcceptTermsSection(),
              ),
              const SizedBox(height: 12),

              // Step 2: Widerrufsverzicht
              _buildStep(
                context: context,
                number: 2,
                title: 'Widerrufsverzicht',
                subtitle: withdrawalWaived
                    ? 'Erklärt am ${_formatDate(contractStatus['withdrawal_waived_at'])}'
                    : termsAccepted
                        ? 'Erforderlich um sofort laden zu können'
                        : 'Erst nach AGB-Akzeptanz verfügbar',
                completed: withdrawalWaived,
                enabled: termsAccepted,
                child: withdrawalWaived
                    ? _buildWithdrawalInfo()
                    : termsAccepted
                        ? _buildWithdrawalWaiverSection()
                        : null,
              ),
              const SizedBox(height: 12),

              // Step 3: Ready
              _buildStep(
                context: context,
                number: 3,
                title: 'Bereit zum Laden',
                subtitle: canCharge
                    ? 'Alle Voraussetzungen erfüllt ⚡'
                    : 'Bitte Schritte 1 & 2 abschließen',
                completed: canCharge,
                enabled: canCharge,
              ),

              const SizedBox(height: 24),

              // Legal info footer
              Card(
                color: theme.colorScheme.surfaceContainerHighest,
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Icon(Icons.info_outline, size: 18, color: theme.colorScheme.onSurfaceVariant),
                          const SizedBox(width: 8),
                          Text('Rechtliche Hinweise', style: theme.textTheme.titleSmall),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'Alle Vertragsunterlagen werden per E-Mail an Ihre hinterlegte Adresse gesendet. '
                        'Sie können Ihren Account jederzeit unter Profil → Konto löschen kündigen. '
                        'Bei Fragen: support@transparent-laden.de',
                        style: theme.textTheme.bodySmall?.copyWith(color: theme.colorScheme.onSurfaceVariant),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          );
        },
      ),
    );
  }

  Widget _buildStep({
    required BuildContext context,
    required int number,
    required String title,
    required String subtitle,
    required bool completed,
    bool enabled = true,
    Widget? child,
  }) {
    final theme = Theme.of(context);
    return Card(
      elevation: completed ? 0 : 1,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: completed
            ? BorderSide(color: Colors.green.shade300)
            : enabled
                ? BorderSide(color: theme.colorScheme.outline.withValues(alpha: 0.3))
                : BorderSide.none,
      ),
      child: Opacity(
        opacity: enabled ? 1.0 : 0.5,
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  CircleAvatar(
                    radius: 16,
                    backgroundColor: completed ? Colors.green : theme.colorScheme.primaryContainer,
                    child: completed
                        ? const Icon(Icons.check, size: 18, color: Colors.white)
                        : Text('$number', style: TextStyle(
                            fontWeight: FontWeight.bold,
                            color: theme.colorScheme.onPrimaryContainer,
                          )),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(title, style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600)),
                        const SizedBox(height: 2),
                        Text(subtitle, style: theme.textTheme.bodySmall?.copyWith(
                          color: completed ? Colors.green.shade700 : theme.colorScheme.onSurfaceVariant,
                        )),
                      ],
                    ),
                  ),
                ],
              ),
              if (child != null) ...[
                const SizedBox(height: 16),
                child,
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildViewTermsButton() {
    return OutlinedButton.icon(
      onPressed: () => _showTermsSheet(),
      icon: const Icon(Icons.description),
      label: const Text('AGB erneut lesen'),
    );
  }

  Widget _buildAcceptTermsSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        OutlinedButton.icon(
          onPressed: () => _showTermsSheet(),
          icon: const Icon(Icons.description),
          label: const Text('AGB lesen'),
        ),
        const SizedBox(height: 12),
        CheckboxListTile(
          value: _termsRead,
          onChanged: (v) => setState(() => _termsRead = v ?? false),
          title: const Text(
            'Ich habe die AGB gelesen und akzeptiere diese.',
            style: TextStyle(fontSize: 14),
          ),
          controlAffinity: ListTileControlAffinity.leading,
          contentPadding: EdgeInsets.zero,
          dense: true,
        ),
        const SizedBox(height: 8),
        FilledButton(
          onPressed: _termsRead && !_accepting ? _acceptTerms : null,
          child: _accepting
              ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
              : const Text('AGB akzeptieren'),
        ),
      ],
    );
  }

  Widget _buildWithdrawalWaiverSection() {
    final theme = Theme.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: Colors.orange.shade50,
            borderRadius: BorderRadius.circular(8),
            border: Border.all(color: Colors.orange.shade200),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Icon(Icons.warning_amber, color: Colors.orange.shade700, size: 20),
                  const SizedBox(width: 8),
                  Text('Widerrufsbelehrung', style: theme.textTheme.titleSmall?.copyWith(
                    color: Colors.orange.shade900,
                    fontWeight: FontWeight.bold,
                  )),
                ],
              ),
              const SizedBox(height: 8),
              Text(
                'Sie haben das Recht, binnen 14 Tagen ohne Angabe von Gründen diesen Vertrag zu widerrufen.\n\n'
                'Wenn Sie jedoch sofort laden möchten, können Sie ausdrücklich verlangen, dass wir mit der '
                'Dienstleistung vor Ablauf der Widerrufsfrist beginnen.\n\n'
                'Gemäß § 356 Abs. 4 BGB erlischt Ihr Widerrufsrecht bei einem erbrachten Ladevorgang, wenn '
                'der Ladevorgang vollständig abgeschlossen ist.\n\n'
                'Für zukünftige, noch nicht erbrachte Ladevorgänge behalten Sie selbstverständlich Ihre '
                'Verbraucherrechte. Sie können Ihren Account jederzeit kündigen.',
                style: theme.textTheme.bodySmall?.copyWith(color: Colors.orange.shade900, height: 1.4),
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),
        FilledButton(
          onPressed: !_waiving ? _waiveWithdrawal : null,
          style: FilledButton.styleFrom(backgroundColor: Colors.orange.shade700),
          child: _waiving
              ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
              : const Text('Ich möchte sofort laden können — Widerrufsverzicht erklären'),
        ),
        const SizedBox(height: 4),
        Text(
          'Ihre Zustimmung wird mit IP-Adresse und Zeitstempel protokolliert.',
          style: theme.textTheme.bodySmall?.copyWith(color: theme.colorScheme.onSurfaceVariant),
          textAlign: TextAlign.center,
        ),
      ],
    );
  }

  Widget _buildWithdrawalInfo() {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.green.shade50,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          Icon(Icons.check_circle, color: Colors.green.shade700),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              'Sie haben den Widerrufsverzicht erklärt. Für jeden einzelnen Ladevorgang erlischt das '
              'Widerrufsrecht nach vollständiger Erbringung. Ihr Account kann jederzeit gekündigt werden.',
              style: TextStyle(fontSize: 13, color: Colors.green.shade900),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _showTermsSheet() async {
    final terms = await ref.read(_termsProvider.future);
    if (!mounted) return;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      useSafeArea: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.9,
        minChildSize: 0.5,
        maxChildSize: 0.95,
        expand: false,
        builder: (context, scrollController) => Column(
          children: [
            Container(
              width: 40, height: 4,
              margin: const EdgeInsets.symmetric(vertical: 12),
              decoration: BoxDecoration(
                color: Colors.grey.shade300,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Row(
                children: [
                  const Icon(Icons.gavel, size: 24),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      terms['title'] ?? 'AGB',
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
                    ),
                  ),
                  Text('v${terms['version']}', style: Theme.of(context).textTheme.bodySmall),
                ],
              ),
            ),
            const Divider(),
            Expanded(
              child: SingleChildScrollView(
                controller: scrollController,
                padding: const EdgeInsets.all(16),
                child: _buildTermsContent(terms['content'] ?? ''),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTermsContent(String content) {
    // Simple markdown-like renderer for the AGB
    final lines = content.split('\n');
    final widgets = <Widget>[];
    final theme = Theme.of(context);

    for (final line in lines) {
      final trimmed = line.trim();
      if (trimmed.isEmpty) {
        widgets.add(const SizedBox(height: 8));
      } else if (trimmed.startsWith('## ')) {
        widgets.add(Padding(
          padding: const EdgeInsets.only(top: 16, bottom: 8),
          child: Text(
            trimmed.substring(3),
            style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
          ),
        ));
      } else if (trimmed.startsWith('**') && trimmed.endsWith('**')) {
        widgets.add(Padding(
          padding: const EdgeInsets.symmetric(vertical: 4),
          child: Text(
            trimmed.substring(2, trimmed.length - 2),
            style: theme.textTheme.bodyMedium?.copyWith(fontWeight: FontWeight.bold),
          ),
        ));
      } else {
        // Handle inline bold markers
        widgets.add(Padding(
          padding: const EdgeInsets.symmetric(vertical: 2),
          child: _buildRichText(trimmed, theme),
        ));
      }
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: widgets,
    );
  }

  Widget _buildRichText(String text, ThemeData theme) {
    final parts = text.split(RegExp(r'\*\*'));
    if (parts.length <= 1) {
      return Text(text, style: theme.textTheme.bodyMedium?.copyWith(height: 1.5));
    }
    final spans = <TextSpan>[];
    for (int i = 0; i < parts.length; i++) {
      spans.add(TextSpan(
        text: parts[i],
        style: i.isOdd
            ? const TextStyle(fontWeight: FontWeight.bold)
            : null,
      ));
    }
    return RichText(
      text: TextSpan(
        style: theme.textTheme.bodyMedium?.copyWith(height: 1.5),
        children: spans,
      ),
    );
  }

  Future<void> _acceptTerms() async {
    setState(() => _accepting = true);
    try {
      final dio = ref.read(dioProvider);
      final terms = await ref.read(_termsProvider.future);
      await dio.post('/contract/accept', data: {'version': terms['version']});
      ref.invalidate(_contractStatusProvider);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('AGB akzeptiert ✓ — Bestätigung per E-Mail gesendet'),
            backgroundColor: Colors.green,
          ),
        );
      }
    } on DioException catch (e) {
      if (mounted) {
        final msg = (e.response?.data as Map?)?['message'] ?? e.message ?? 'Fehler';
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg.toString()), backgroundColor: Colors.red));
      }
    } finally {
      if (mounted) setState(() => _accepting = false);
    }
  }

  Future<void> _waiveWithdrawal() async {
    // Double-confirm with dialog
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Widerrufsverzicht bestätigen'),
        content: const Text(
          'Ich verlange ausdrücklich, dass die Dienstleistung (Laden meines Elektrofahrzeugs) sofort '
          'beginnt, bevor die 14-tägige Widerrufsfrist abgelaufen ist.\n\n'
          'Mir ist bekannt, dass mein Widerrufsrecht für jeden vollständig erbrachten Ladevorgang '
          'gemäß § 356 Abs. 4 BGB erlischt.',
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Abbrechen')),
          FilledButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: FilledButton.styleFrom(backgroundColor: Colors.orange.shade700),
            child: const Text('Ja, bestätigen'),
          ),
        ],
      ),
    );

    if (confirmed != true || !mounted) return;

    setState(() => _waiving = true);
    try {
      final dio = ref.read(dioProvider);
      await dio.post('/contract/waive-withdrawal', data: {'confirmed': true});
      ref.invalidate(_contractStatusProvider);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Widerrufsverzicht erklärt ✓ — Sie können jetzt laden!'),
            backgroundColor: Colors.green,
          ),
        );
      }
    } on DioException catch (e) {
      if (mounted) {
        final msg = (e.response?.data as Map?)?['message'] ?? e.message ?? 'Fehler';
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg.toString()), backgroundColor: Colors.red));
      }
    } finally {
      if (mounted) setState(() => _waiving = false);
    }
  }

  String _formatDate(dynamic dateStr) {
    if (dateStr == null) return '—';
    try {
      final dt = DateTime.parse(dateStr.toString());
      return '${dt.day.toString().padLeft(2, '0')}.${dt.month.toString().padLeft(2, '0')}.${dt.year} '
             '${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';
    } catch (_) {
      return dateStr.toString();
    }
  }
}

class _StatusCard extends StatelessWidget {
  final bool termsAccepted;
  final bool withdrawalWaived;
  final bool canCharge;

  const _StatusCard({
    required this.termsAccepted,
    required this.withdrawalWaived,
    required this.canCharge,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final Color bg;
    final Color fg;
    final IconData icon;
    final String label;

    if (canCharge) {
      bg = Colors.green.shade50;
      fg = Colors.green.shade800;
      icon = Icons.check_circle;
      label = 'Vertrag vollständig — Sie können laden';
    } else if (termsAccepted) {
      bg = Colors.orange.shade50;
      fg = Colors.orange.shade800;
      icon = Icons.pending;
      label = 'Noch nicht abgeschlossen — Widerrufsverzicht ausstehend';
    } else {
      bg = Colors.red.shade50;
      fg = Colors.red.shade800;
      icon = Icons.warning;
      label = 'Vertrag noch nicht akzeptiert — Laden nicht möglich';
    }

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        children: [
          Icon(icon, color: fg, size: 32),
          const SizedBox(width: 12),
          Expanded(
            child: Text(label, style: theme.textTheme.bodyMedium?.copyWith(color: fg, fontWeight: FontWeight.w600)),
          ),
        ],
      ),
    );
  }
}
