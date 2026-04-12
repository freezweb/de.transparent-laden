import 'package:flutter/material.dart';
import 'package:einfach_laden/core/theme/app_theme.dart';

/// Reusable widget for transparent partner-level price breakdown.
/// Matches the website's 4-area model: Betreiber/Infrastruktur, Roaming/Betrieb,
/// Zahlungsabwicklung, Unsere Marge.
class PriceBreakdownWidget extends StatelessWidget {
  final Map<String, dynamic> pricing;
  final bool showPercentageBar;
  final bool isEstimate;
  final bool compact;

  const PriceBreakdownWidget({
    super.key,
    required this.pricing,
    this.showPercentageBar = true,
    this.isEstimate = false,
    this.compact = false,
  });

  @override
  Widget build(BuildContext context) {
    final breakdown = _extractBreakdown();
    if (breakdown == null) return const SizedBox.shrink();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Text(
              'Preisaufschlüsselung',
              style: Theme.of(context).textTheme.labelLarge?.copyWith(fontWeight: FontWeight.w600),
            ),
            if (isEstimate) ...[
              const SizedBox(width: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                  color: Color.fromRGBO(255, 193, 7, 0.2),
                  borderRadius: BorderRadius.circular(4),
                ),
                child: Text(
                  'geschätzt',
                  style: TextStyle(fontSize: 10, color: Colors.amber.shade800, fontWeight: FontWeight.w500),
                ),
              ),
            ],
          ],
        ),
        const SizedBox(height: 8),

        // Percentage bar
        if (showPercentageBar) ...[
          _PercentageBar(breakdown: breakdown),
          const SizedBox(height: 8),
        ],

        // Partner line items
        _PartnerRow(
          color: AppTheme.operatorColor,
          label: 'Betreiber / Infrastruktur',
          amount: breakdown.operatorCent,
          percent: breakdown.operatorPct,
          compact: compact,
        ),
        _PartnerRow(
          color: AppTheme.roamingColor,
          label: 'Roaming / Betrieb',
          amount: breakdown.roamingCent,
          percent: breakdown.roamingPct,
          compact: compact,
        ),
        _PartnerRow(
          color: AppTheme.paymentColor,
          label: 'Zahlungsabwicklung',
          amount: breakdown.paymentCent,
          percent: breakdown.paymentPct,
          compact: compact,
        ),
        _PartnerRow(
          color: AppTheme.marginColor,
          label: 'Unsere Marge',
          amount: breakdown.marginCent,
          percent: breakdown.marginPct,
          compact: compact,
        ),

        // Total
        const Divider(height: 16),
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text('Endpreis', style: TextStyle(fontWeight: FontWeight.bold, fontSize: compact ? 12 : 14)),
            Text(
              '${(breakdown.totalCent / 100).toStringAsFixed(2)} €',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: compact ? 12 : 14),
            ),
          ],
        ),

        // Additional fees
        if (!compact) ...[
          if (breakdown.startFeeCent > 0) ...[
            const SizedBox(height: 8),
            _ExtraRow(label: 'Startgebühr (Betreiber)', value: breakdown.startFeeCent),
          ],
          if (breakdown.timePriceCtMin > 0) ...[
            const SizedBox(height: 4),
            _ExtraRow(label: 'Zeitpreis', value: breakdown.timePriceCtMin, unit: 'ct/min'),
          ],
          if (breakdown.blockingFeeCtMin > 0) ...[
            const SizedBox(height: 4),
            _ExtraRow(label: 'Blockiergebühr', value: breakdown.blockingFeeCtMin, unit: 'ct/min'),
          ],
        ],
      ],
    );
  }

  _Breakdown? _extractBreakdown() {
    // Try new 4-area fields first
    if (pricing.containsKey('operator_infrastructure_cent') || pricing.containsKey('operator_infrastructure_pct')) {
      return _Breakdown(
        operatorCent: _num(pricing['operator_infrastructure_cent']),
        operatorPct: _num(pricing['operator_infrastructure_pct']),
        roamingCent: _num(pricing['roaming_operations_cent']),
        roamingPct: _num(pricing['roaming_operations_pct']),
        paymentCent: _num(pricing['payment_processing_cent']),
        paymentPct: _num(pricing['payment_processing_pct']),
        marginCent: _num(pricing['margin_cent']),
        marginPct: _num(pricing['margin_pct']),
        totalCent: _num(pricing['total_endprice_cent']),
        startFeeCent: _num(pricing['start_fee_cent']),
        timePriceCtMin: _num(pricing['time_price_ct_min']),
        blockingFeeCtMin: _num(pricing['blocking_fee_ct_min']),
      );
    }

    // Fallback: derive from legacy fields
    final energyCt = _num(pricing['energy_price_ct_kwh']);
    final roamingCt = _num(pricing['roaming_fee_ct_kwh']);
    final platformCt = _num(pricing['platform_fee_ct_kwh']);
    final total = energyCt + roamingCt + platformCt;
    if (total <= 0) return null;

    return _Breakdown(
      operatorCent: energyCt,
      operatorPct: total > 0 ? (energyCt / total * 100).roundToDouble() : 0,
      roamingCent: roamingCt,
      roamingPct: total > 0 ? (roamingCt / total * 100).roundToDouble() : 0,
      paymentCent: 0,
      paymentPct: 0,
      marginCent: platformCt,
      marginPct: total > 0 ? (platformCt / total * 100).roundToDouble() : 0,
      totalCent: total,
      startFeeCent: 0,
      timePriceCtMin: _num(pricing['time_price_ct_min']),
      blockingFeeCtMin: _num(pricing['blocking_fee_ct_min']),
    );
  }

  static double _num(dynamic v) {
    if (v == null) return 0;
    if (v is num) return v.toDouble();
    return double.tryParse(v.toString()) ?? 0;
  }
}

class _Breakdown {
  final double operatorCent, operatorPct;
  final double roamingCent, roamingPct;
  final double paymentCent, paymentPct;
  final double marginCent, marginPct;
  final double totalCent;
  final double startFeeCent, timePriceCtMin, blockingFeeCtMin;

  _Breakdown({
    required this.operatorCent,
    required this.operatorPct,
    required this.roamingCent,
    required this.roamingPct,
    required this.paymentCent,
    required this.paymentPct,
    required this.marginCent,
    required this.marginPct,
    required this.totalCent,
    required this.startFeeCent,
    required this.timePriceCtMin,
    required this.blockingFeeCtMin,
  });

  double get totalPct {
    final sum = operatorPct + roamingPct + paymentPct + marginPct;
    return sum > 0 ? sum : 100;
  }
}

class _PercentageBar extends StatelessWidget {
  final _Breakdown breakdown;
  const _PercentageBar({required this.breakdown});

  @override
  Widget build(BuildContext context) {
    final total = breakdown.totalPct;
    return ClipRRect(
      borderRadius: BorderRadius.circular(6),
      child: SizedBox(
        height: 12,
        child: Row(
          children: [
            if (breakdown.operatorPct > 0)
              Flexible(
                flex: (breakdown.operatorPct / total * 1000).round(),
                child: Container(color: AppTheme.operatorColor),
              ),
            if (breakdown.roamingPct > 0)
              Flexible(
                flex: (breakdown.roamingPct / total * 1000).round(),
                child: Container(color: AppTheme.roamingColor),
              ),
            if (breakdown.paymentPct > 0)
              Flexible(
                flex: (breakdown.paymentPct / total * 1000).round(),
                child: Container(color: AppTheme.paymentColor),
              ),
            if (breakdown.marginPct > 0)
              Flexible(
                flex: (breakdown.marginPct / total * 1000).round(),
                child: Container(color: AppTheme.marginColor),
              ),
          ],
        ),
      ),
    );
  }
}

class _PartnerRow extends StatelessWidget {
  final Color color;
  final String label;
  final double amount;
  final double percent;
  final bool compact;

  const _PartnerRow({
    required this.color,
    required this.label,
    required this.amount,
    required this.percent,
    this.compact = false,
  });

  @override
  Widget build(BuildContext context) {
    final fs = compact ? 11.0 : 12.0;
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Row(
        children: [
          Container(
            width: 10,
            height: 10,
            decoration: BoxDecoration(color: color, shape: BoxShape.circle),
          ),
          const SizedBox(width: 8),
          Expanded(child: Text(label, style: TextStyle(fontSize: fs))),
          if (amount > 0) ...[
            Text('${(amount / 100).toStringAsFixed(2)} €', style: TextStyle(fontSize: fs)),
            const SizedBox(width: 8),
          ],
          Text('~${percent.round()} %',
              style: TextStyle(fontSize: fs, fontWeight: FontWeight.w500, color: color)),
        ],
      ),
    );
  }
}

class _ExtraRow extends StatelessWidget {
  final String label;
  final double value;
  final String unit;

  const _ExtraRow({required this.label, required this.value, this.unit = '€'});

  @override
  Widget build(BuildContext context) {
    String formatted;
    if (unit == '€') {
      formatted = '${(value / 100).toStringAsFixed(2)} €';
    } else {
      formatted = '${value.toStringAsFixed(1)} $unit';
    }
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: const TextStyle(fontSize: 11, color: Colors.grey)),
        Text(formatted, style: const TextStyle(fontSize: 11, color: Colors.grey)),
      ],
    );
  }
}

/// Compact legend for percentage bar (used inline)
class PriceBreakdownLegend extends StatelessWidget {
  const PriceBreakdownLegend({super.key});

  @override
  Widget build(BuildContext context) {
    return Wrap(
      spacing: 12,
      runSpacing: 4,
      children: [
        _LegendItem(color: AppTheme.operatorColor, label: 'Betreiber'),
        _LegendItem(color: AppTheme.roamingColor, label: 'Roaming'),
        _LegendItem(color: AppTheme.paymentColor, label: 'Zahlung'),
        _LegendItem(color: AppTheme.marginColor, label: 'Marge'),
      ],
    );
  }
}

class _LegendItem extends StatelessWidget {
  final Color color;
  final String label;
  const _LegendItem({required this.color, required this.label});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(width: 8, height: 8, decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
        const SizedBox(width: 4),
        Text(label, style: const TextStyle(fontSize: 10)),
      ],
    );
  }
}
