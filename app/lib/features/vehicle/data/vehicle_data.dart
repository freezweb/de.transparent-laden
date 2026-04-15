import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

class EvVehicle {
  final String id;
  final String manufacturer;
  final String model;
  final double batteryCapacityKwh;
  final double usableCapacityKwh;
  final double maxAcKw;
  final double maxDcKw;

  const EvVehicle({
    required this.id,
    required this.manufacturer,
    required this.model,
    required this.batteryCapacityKwh,
    required this.usableCapacityKwh,
    required this.maxAcKw,
    required this.maxDcKw,
  });

  String get displayName => '$manufacturer $model';

  /// Energy needed for 10% → 80% charge.
  double get energyFor10To80 => usableCapacityKwh * 0.7;

  /// Estimated charging time in minutes for 10%→80% at given station power.
  double estimatedMinutes(double stationPowerKw) {
    if (stationPowerKw <= 0) return 0;
    final effectivePower = stationPowerKw.clamp(0, maxDcKw > 0 ? maxDcKw : maxAcKw);
    if (effectivePower <= 0) return 0;
    // Simplified: average power is ~80% of max due to charging curve
    final avgPower = effectivePower * 0.8;
    return (energyFor10To80 / avgPower) * 60;
  }

  /// Estimated cost in cents for 10%→80%.
  double estimatedCostCent({
    required double energyPricePerKwhCent,
    double timePricePerMinCent = 0,
    double stationPowerKw = 50,
  }) {
    final energy = energyFor10To80;
    final minutes = estimatedMinutes(stationPowerKw);
    return energy * energyPricePerKwhCent + minutes * timePricePerMinCent;
  }

  Map<String, dynamic> toJson() => {
    'id': id, 'manufacturer': manufacturer, 'model': model,
    'battery_capacity_kwh': batteryCapacityKwh,
    'usable_capacity_kwh': usableCapacityKwh,
    'max_ac_kw': maxAcKw, 'max_dc_kw': maxDcKw,
  };
}

/// Predefined catalog of popular EVs.
const List<EvVehicle> evVehicleCatalog = [
  EvVehicle(id: 'tesla_m3_sr', manufacturer: 'Tesla', model: 'Model 3 Standard', batteryCapacityKwh: 60, usableCapacityKwh: 57.5, maxAcKw: 11, maxDcKw: 170),
  EvVehicle(id: 'tesla_m3_lr', manufacturer: 'Tesla', model: 'Model 3 Long Range', batteryCapacityKwh: 82, usableCapacityKwh: 78, maxAcKw: 11, maxDcKw: 250),
  EvVehicle(id: 'tesla_my_sr', manufacturer: 'Tesla', model: 'Model Y Standard', batteryCapacityKwh: 60, usableCapacityKwh: 57.5, maxAcKw: 11, maxDcKw: 170),
  EvVehicle(id: 'tesla_my_lr', manufacturer: 'Tesla', model: 'Model Y Long Range', batteryCapacityKwh: 82, usableCapacityKwh: 78, maxAcKw: 11, maxDcKw: 250),
  EvVehicle(id: 'vw_id3_pro', manufacturer: 'VW', model: 'ID.3 Pro', batteryCapacityKwh: 62, usableCapacityKwh: 58, maxAcKw: 11, maxDcKw: 120),
  EvVehicle(id: 'vw_id3_pro_s', manufacturer: 'VW', model: 'ID.3 Pro S', batteryCapacityKwh: 82, usableCapacityKwh: 77, maxAcKw: 11, maxDcKw: 170),
  EvVehicle(id: 'vw_id4_pro', manufacturer: 'VW', model: 'ID.4 Pro', batteryCapacityKwh: 77, usableCapacityKwh: 72, maxAcKw: 11, maxDcKw: 135),
  EvVehicle(id: 'vw_id5_gtx', manufacturer: 'VW', model: 'ID.5 GTX', batteryCapacityKwh: 77, usableCapacityKwh: 72, maxAcKw: 11, maxDcKw: 150),
  EvVehicle(id: 'vw_id7_pro_s', manufacturer: 'VW', model: 'ID.7 Pro S', batteryCapacityKwh: 86, usableCapacityKwh: 82, maxAcKw: 11, maxDcKw: 200),
  EvVehicle(id: 'hyundai_ioniq5_lr', manufacturer: 'Hyundai', model: 'Ioniq 5 Long Range', batteryCapacityKwh: 77.4, usableCapacityKwh: 74, maxAcKw: 11, maxDcKw: 220),
  EvVehicle(id: 'hyundai_ioniq6_lr', manufacturer: 'Hyundai', model: 'Ioniq 6 Long Range', batteryCapacityKwh: 77.4, usableCapacityKwh: 74, maxAcKw: 11, maxDcKw: 220),
  EvVehicle(id: 'kia_ev6_lr', manufacturer: 'Kia', model: 'EV6 Long Range', batteryCapacityKwh: 77.4, usableCapacityKwh: 74, maxAcKw: 11, maxDcKw: 240),
  EvVehicle(id: 'kia_ev9', manufacturer: 'Kia', model: 'EV9', batteryCapacityKwh: 99.8, usableCapacityKwh: 96, maxAcKw: 11, maxDcKw: 250),
  EvVehicle(id: 'bmw_ix1', manufacturer: 'BMW', model: 'iX1 xDrive30', batteryCapacityKwh: 64.7, usableCapacityKwh: 61.7, maxAcKw: 11, maxDcKw: 130),
  EvVehicle(id: 'bmw_i4', manufacturer: 'BMW', model: 'i4 eDrive40', batteryCapacityKwh: 83.9, usableCapacityKwh: 80.7, maxAcKw: 11, maxDcKw: 200),
  EvVehicle(id: 'bmw_ix_40', manufacturer: 'BMW', model: 'iX xDrive40', batteryCapacityKwh: 76.6, usableCapacityKwh: 71, maxAcKw: 11, maxDcKw: 150),
  EvVehicle(id: 'mercedes_eqa', manufacturer: 'Mercedes', model: 'EQA 250+', batteryCapacityKwh: 70.5, usableCapacityKwh: 66.5, maxAcKw: 11, maxDcKw: 100),
  EvVehicle(id: 'mercedes_eqb', manufacturer: 'Mercedes', model: 'EQB 250+', batteryCapacityKwh: 70.5, usableCapacityKwh: 66.5, maxAcKw: 11, maxDcKw: 100),
  EvVehicle(id: 'mercedes_eqe', manufacturer: 'Mercedes', model: 'EQE 300', batteryCapacityKwh: 96.12, usableCapacityKwh: 89, maxAcKw: 11, maxDcKw: 170),
  EvVehicle(id: 'audi_q4_45', manufacturer: 'Audi', model: 'Q4 e-tron 45', batteryCapacityKwh: 82, usableCapacityKwh: 77, maxAcKw: 11, maxDcKw: 175),
  EvVehicle(id: 'porsche_taycan', manufacturer: 'Porsche', model: 'Taycan', batteryCapacityKwh: 93.4, usableCapacityKwh: 83.7, maxAcKw: 11, maxDcKw: 270),
  EvVehicle(id: 'polestar_2_lr', manufacturer: 'Polestar', model: '2 Long Range', batteryCapacityKwh: 82, usableCapacityKwh: 79, maxAcKw: 11, maxDcKw: 205),
  EvVehicle(id: 'renault_megane', manufacturer: 'Renault', model: 'Megane E-Tech EV60', batteryCapacityKwh: 60, usableCapacityKwh: 57, maxAcKw: 22, maxDcKw: 130),
  EvVehicle(id: 'skoda_enyaq_80', manufacturer: 'Škoda', model: 'Enyaq iV 80', batteryCapacityKwh: 82, usableCapacityKwh: 77, maxAcKw: 11, maxDcKw: 135),
  EvVehicle(id: 'cupra_born', manufacturer: 'Cupra', model: 'Born 77 kWh', batteryCapacityKwh: 82, usableCapacityKwh: 77, maxAcKw: 11, maxDcKw: 170),
  EvVehicle(id: 'fiat_500e', manufacturer: 'Fiat', model: '500e 42 kWh', batteryCapacityKwh: 42, usableCapacityKwh: 37.3, maxAcKw: 11, maxDcKw: 85),
  EvVehicle(id: 'opel_corsa_e', manufacturer: 'Opel', model: 'Corsa Electric 54 kWh', batteryCapacityKwh: 54, usableCapacityKwh: 51, maxAcKw: 11, maxDcKw: 100),
  EvVehicle(id: 'mg4_standard', manufacturer: 'MG', model: 'MG4 Standard', batteryCapacityKwh: 51, usableCapacityKwh: 49, maxAcKw: 6.6, maxDcKw: 117),
  EvVehicle(id: 'mg4_long', manufacturer: 'MG', model: 'MG4 Long Range', batteryCapacityKwh: 64, usableCapacityKwh: 61.7, maxAcKw: 11, maxDcKw: 140),
  EvVehicle(id: 'smart_1', manufacturer: 'smart', model: '#1 Pro+', batteryCapacityKwh: 66, usableCapacityKwh: 62, maxAcKw: 22, maxDcKw: 150),
  EvVehicle(id: 'volvo_ex30', manufacturer: 'Volvo', model: 'EX30 Single Motor', batteryCapacityKwh: 51, usableCapacityKwh: 49, maxAcKw: 11, maxDcKw: 134),
  EvVehicle(id: 'byd_atto3', manufacturer: 'BYD', model: 'Atto 3', batteryCapacityKwh: 60.5, usableCapacityKwh: 58, maxAcKw: 7.4, maxDcKw: 88),
];

/// Provider for the currently selected vehicle.
final selectedVehicleProvider = StateNotifierProvider<SelectedVehicleNotifier, EvVehicle?>((ref) {
  return SelectedVehicleNotifier();
});

class SelectedVehicleNotifier extends StateNotifier<EvVehicle?> {
  static const _storageKey = 'selected_vehicle_id';

  SelectedVehicleNotifier() : super(null) {
    _load();
  }

  Future<void> _load() async {
    final prefs = await SharedPreferences.getInstance();
    final id = prefs.getString(_storageKey);
    if (id != null) {
      try {
        state = evVehicleCatalog.firstWhere((v) => v.id == id);
      } catch (_) {
        // Vehicle not found in catalog
      }
    }
  }

  Future<void> select(EvVehicle? vehicle) async {
    state = vehicle;
    final prefs = await SharedPreferences.getInstance();
    if (vehicle != null) {
      await prefs.setString(_storageKey, vehicle.id);
    } else {
      await prefs.remove(_storageKey);
    }
  }
}
