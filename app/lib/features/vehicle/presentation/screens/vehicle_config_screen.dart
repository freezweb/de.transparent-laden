import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:einfach_laden/features/vehicle/data/vehicle_data.dart';

class VehicleConfigScreen extends ConsumerStatefulWidget {
  const VehicleConfigScreen({super.key});

  @override
  ConsumerState<VehicleConfigScreen> createState() => _VehicleConfigScreenState();
}

class _VehicleConfigScreenState extends ConsumerState<VehicleConfigScreen> {
  String _search = '';

  Future<void> _showCustomVehicleDialog() async {
    final batteryController = TextEditingController();
    final dcController = TextEditingController();
    final nameController = TextEditingController();
    final selected = ref.read(selectedVehicleProvider);
    if (selected != null && selected.isCustom) {
      nameController.text = selected.manufacturer;
      batteryController.text = selected.batteryCapacityKwh.toStringAsFixed(0);
      dcController.text = selected.maxDcKw.toStringAsFixed(0);
    }

    final formKey = GlobalKey<FormState>();

    final result = await showDialog<EvVehicle>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Fahrzeug manuell eingeben'),
        content: Form(
          key: formKey,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextFormField(
                controller: nameController,
                decoration: const InputDecoration(
                  labelText: 'Name (optional)',
                  hintText: 'z.B. Polestar 4',
                  prefixIcon: Icon(Icons.label_outline),
                ),
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: batteryController,
                decoration: const InputDecoration(
                  labelText: 'Akkukapazität (kWh)',
                  hintText: 'z.B. 100',
                  prefixIcon: Icon(Icons.battery_full),
                ),
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                validator: (v) {
                  if (v == null || v.isEmpty) return 'Bitte eingeben';
                  final n = double.tryParse(v.replaceAll(',', '.'));
                  if (n == null || n <= 0 || n > 300) return 'Ungültiger Wert';
                  return null;
                },
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: dcController,
                decoration: const InputDecoration(
                  labelText: 'Max. Ladeleistung DC (kW)',
                  hintText: 'z.B. 200',
                  prefixIcon: Icon(Icons.bolt),
                ),
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                validator: (v) {
                  if (v == null || v.isEmpty) return 'Bitte eingeben';
                  final n = double.tryParse(v.replaceAll(',', '.'));
                  if (n == null || n <= 0 || n > 500) return 'Ungültiger Wert';
                  return null;
                },
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Abbrechen'),
          ),
          FilledButton(
            onPressed: () {
              if (!formKey.currentState!.validate()) return;
              final battery = double.parse(batteryController.text.replaceAll(',', '.'));
              final dc = double.parse(dcController.text.replaceAll(',', '.'));
              final name = nameController.text.trim();
              Navigator.pop(ctx, EvVehicle.custom(
                batteryCapacityKwh: battery,
                maxDcKw: dc,
                name: name.isNotEmpty ? name : null,
              ));
            },
            child: const Text('Speichern'),
          ),
        ],
      ),
    );

    if (result != null) {
      ref.read(selectedVehicleProvider.notifier).select(result);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('${result.displayName} gespeichert'), duration: const Duration(seconds: 1)),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final selected = ref.watch(selectedVehicleProvider);

    // Group vehicles by manufacturer
    final filtered = evVehicleCatalog.where((v) {
      if (_search.isEmpty) return true;
      final q = _search.toLowerCase();
      return v.manufacturer.toLowerCase().contains(q) ||
          v.model.toLowerCase().contains(q) ||
          v.displayName.toLowerCase().contains(q);
    }).toList();

    final grouped = <String, List<EvVehicle>>{};
    for (final v in filtered) {
      grouped.putIfAbsent(v.manufacturer, () => []).add(v);
    }
    final manufacturers = grouped.keys.toList()..sort();

    return Scaffold(
      appBar: AppBar(title: const Text('Fahrzeug wählen')),
      body: Column(
        children: [
          // Current selection
          if (selected != null)
            Container(
              width: double.infinity,
              margin: const EdgeInsets.all(12),
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: Theme.of(context).colorScheme.primaryContainer,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Row(
                children: [
                  const Icon(Icons.electric_car, size: 28),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(selected.displayName, style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)),
                        Text('${selected.usableCapacityKwh.toStringAsFixed(0)} kWh nutzbar • DC max ${selected.maxDcKw.toStringAsFixed(0)} kW',
                            style: Theme.of(context).textTheme.bodySmall),
                        Text('10%→80%: ${selected.energyFor10To80.toStringAsFixed(1)} kWh',
                            style: Theme.of(context).textTheme.bodySmall?.copyWith(fontWeight: FontWeight.w600)),
                        if (selected.isCustom)
                          Text('Manuell konfiguriert',
                              style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Theme.of(context).colorScheme.tertiary)),
                      ],
                    ),
                  ),
                  if (selected.isCustom)
                    IconButton(
                      icon: const Icon(Icons.edit),
                      onPressed: _showCustomVehicleDialog,
                      tooltip: 'Bearbeiten',
                    ),
                  IconButton(
                    icon: const Icon(Icons.close),
                    onPressed: () => ref.read(selectedVehicleProvider.notifier).select(null),
                    tooltip: 'Fahrzeug entfernen',
                  ),
                ],
              ),
            ),

          // Search
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12),
            child: TextField(
              decoration: InputDecoration(
                hintText: 'Fahrzeug suchen...',
                prefixIcon: const Icon(Icons.search),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                isDense: true,
              ),
              onChanged: (v) => setState(() => _search = v),
            ),
          ),

          const SizedBox(height: 8),

          // Manual entry button
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12),
            child: OutlinedButton.icon(
              onPressed: _showCustomVehicleDialog,
              icon: const Icon(Icons.edit),
              label: const Text('Fahrzeug nicht dabei? Manuell eingeben'),
              style: OutlinedButton.styleFrom(
                minimumSize: const Size(double.infinity, 44),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ),

          const SizedBox(height: 8),
          Expanded(
            child: ListView.builder(
              itemCount: manufacturers.length,
              itemBuilder: (context, index) {
                final mfr = manufacturers[index];
                final vehicles = grouped[mfr]!;
                return Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Padding(
                      padding: const EdgeInsets.only(left: 16, top: 12, bottom: 4),
                      child: Text(mfr, style: Theme.of(context).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.bold, color: Theme.of(context).colorScheme.primary)),
                    ),
                    ...vehicles.map((v) {
                      final isSelected = selected?.id == v.id;
                      return ListTile(
                        leading: CircleAvatar(
                          backgroundColor: isSelected
                              ? Theme.of(context).colorScheme.primary
                              : Theme.of(context).colorScheme.surfaceContainerHighest,
                          child: Icon(Icons.electric_car, size: 20,
                              color: isSelected ? Colors.white : null),
                        ),
                        title: Text(v.model),
                        subtitle: Text('${v.usableCapacityKwh.toStringAsFixed(0)} kWh • AC ${v.maxAcKw.toStringAsFixed(0)} kW • DC ${v.maxDcKw.toStringAsFixed(0)} kW'),
                        trailing: isSelected ? Icon(Icons.check_circle, color: Theme.of(context).colorScheme.primary) : null,
                        selected: isSelected,
                        onTap: () {
                          ref.read(selectedVehicleProvider.notifier).select(v);
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(content: Text('${v.displayName} ausgewählt'), duration: const Duration(seconds: 1)),
                          );
                        },
                      );
                    }),
                  ],
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}
