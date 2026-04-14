import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:geolocator/geolocator.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:einfach_laden/features/charge_point/providers/charge_point_provider.dart';
import 'package:einfach_laden/features/charge_point/data/charge_point_repository.dart';

final userLocationProvider = FutureProvider<Position>((ref) async {
  final permission = await Geolocator.checkPermission();
  if (permission == LocationPermission.denied) {
    final requested = await Geolocator.requestPermission();
    if (requested == LocationPermission.denied || requested == LocationPermission.deniedForever) {
      throw Exception('Standortzugriff verweigert');
    }
  }
  return Geolocator.getCurrentPosition(
    locationSettings: const LocationSettings(accuracy: LocationAccuracy.high),
  );
});

class MapScreen extends ConsumerStatefulWidget {
  const MapScreen({super.key});

  @override
  ConsumerState<MapScreen> createState() => _MapScreenState();
}

class _MapScreenState extends ConsumerState<MapScreen> {
  GoogleMapController? _mapController;

  // Filter state
  double? _minPowerKw;
  double? _maxPowerKw;
  String? _connectorType;
  bool? _onlyStartable;
  String? _currentCategory;
  bool _showFilterSheet = false;

  // Current visible bounds for bbox loading
  LatLngBounds? _currentBounds;
  List<Map<String, dynamic>> _stations = [];
  bool _loading = false;

  bool get _hasActiveFilters =>
      _minPowerKw != null || _maxPowerKw != null || _connectorType != null ||
      _onlyStartable == true || _currentCategory != null;

  @override
  void dispose() {
    _mapController?.dispose();
    super.dispose();
  }

  Future<void> _loadStationsForBounds(LatLngBounds bounds) async {
    if (_loading) return;
    setState(() => _loading = true);

    try {
      final repo = ref.read(chargePointRepositoryProvider);
      final data = await repo.getByBoundingBox(
        latMin: bounds.southwest.latitude,
        lngMin: bounds.southwest.longitude,
        latMax: bounds.northeast.latitude,
        lngMax: bounds.northeast.longitude,
        minPowerKw: _minPowerKw,
        maxPowerKw: _maxPowerKw,
        connectorType: _connectorType,
        currentCategory: _currentCategory,
        onlyStartable: _onlyStartable,
      );
      if (mounted) {
        setState(() {
          _stations = data;
          _currentBounds = bounds;
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _onCameraIdle() async {
    final controller = _mapController;
    if (controller == null) return;
    final bounds = await controller.getVisibleRegion();
    // Only reload if bounds changed significantly
    if (_currentBounds == null || _boundsChangedSignificantly(bounds)) {
      _loadStationsForBounds(bounds);
    }
  }

  bool _boundsChangedSignificantly(LatLngBounds newBounds) {
    if (_currentBounds == null) return true;
    final old = _currentBounds!;
    const threshold = 0.005; // ~500m
    return (old.southwest.latitude - newBounds.southwest.latitude).abs() > threshold ||
        (old.southwest.longitude - newBounds.southwest.longitude).abs() > threshold ||
        (old.northeast.latitude - newBounds.northeast.latitude).abs() > threshold ||
        (old.northeast.longitude - newBounds.northeast.longitude).abs() > threshold;
  }

  @override
  Widget build(BuildContext context) {
    final location = ref.watch(userLocationProvider);

    return Scaffold(
      body: Stack(
        children: [
          location.when(
            data: (pos) => _buildMap(pos),
            loading: () => const Center(child: CircularProgressIndicator()),
            error: (e, _) => Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.location_off, size: 64, color: Colors.grey),
                  const SizedBox(height: 16),
                  Text('$e'),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: () => ref.invalidate(userLocationProvider),
                    child: const Text('Erneut versuchen'),
                  ),
                ],
              ),
            ),
          ),

          // Loading indicator
          if (_loading)
            Positioned(
              top: MediaQuery.of(context).padding.top + 70,
              left: 0, right: 0,
              child: const Center(
                child: SizedBox(width: 24, height: 24, child: CircularProgressIndicator(strokeWidth: 2)),
              ),
            ),

          // Station count
          if (_stations.isNotEmpty)
            Positioned(
              top: MediaQuery.of(context).padding.top + 64,
              right: 12,
              child: Material(
                elevation: 2,
                borderRadius: BorderRadius.circular(16),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: Theme.of(context).colorScheme.primaryContainer,
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Text(
                    '${_stations.length} Stationen',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                      color: Theme.of(context).colorScheme.onPrimaryContainer,
                    ),
                  ),
                ),
              ),
            ),

          // Top search bar
          Positioned(
            top: MediaQuery.of(context).padding.top + 8,
            left: 12,
            right: 12,
            child: _buildTopBar(context),
          ),

          // Active filter chips
          if (_hasActiveFilters)
            Positioned(
              top: MediaQuery.of(context).padding.top + 64,
              left: 12,
              right: _stations.isNotEmpty ? 120 : 12,
              child: _buildActiveFilterChips(),
            ),

          // Legend
          Positioned(
            bottom: 16,
            left: 12,
            child: _buildLegend(context),
          ),

          // Filter sheet
          if (_showFilterSheet)
            Positioned(
              bottom: 0, left: 0, right: 0,
              child: _buildFilterSheet(context),
            ),
        ],
      ),
    );
  }

  Widget _buildTopBar(BuildContext context) {
    return Material(
      elevation: 4,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
        decoration: BoxDecoration(
          color: Theme.of(context).colorScheme.surface,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Row(
          children: [
            const Icon(Icons.ev_station, size: 20),
            const SizedBox(width: 8),
            Expanded(
              child: Text('Ladestationen finden', style: Theme.of(context).textTheme.bodyLarge),
            ),
            IconButton(
              icon: Badge(
                isLabelVisible: _hasActiveFilters,
                child: const Icon(Icons.tune),
              ),
              onPressed: () => setState(() => _showFilterSheet = !_showFilterSheet),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildActiveFilterChips() {
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Row(
        children: [
          if (_connectorType != null)
            _chipRemovable(_connectorType!, () => setState(() => _connectorType = null)),
          if (_currentCategory != null)
            _chipRemovable(_currentCategory!, () => setState(() => _currentCategory = null)),
          if (_minPowerKw != null)
            _chipRemovable('ab ${_minPowerKw!.toInt()} kW', () => setState(() { _minPowerKw = null; _maxPowerKw = null; })),
          if (_maxPowerKw != null && _minPowerKw == null)
            _chipRemovable('bis ${_maxPowerKw!.toInt()} kW', () => setState(() => _maxPowerKw = null)),
          if (_onlyStartable == true)
            _chipRemovable('Nur startbar', () => setState(() => _onlyStartable = null)),
        ],
      ),
    );
  }

  Widget _chipRemovable(String label, VoidCallback onRemove) {
    return Padding(
      padding: const EdgeInsets.only(right: 6),
      child: Chip(
        label: Text(label, style: const TextStyle(fontSize: 12)),
        onDeleted: onRemove,
        materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
        visualDensity: VisualDensity.compact,
      ),
    );
  }

  Widget _buildFilterSheet(BuildContext context) {
    return Material(
      elevation: 8,
      borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
      child: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: Theme.of(context).colorScheme.surface,
          borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Text('Filter', style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold)),
                const Spacer(),
                TextButton(
                  onPressed: () => setState(() {
                    _minPowerKw = null; _maxPowerKw = null;
                    _connectorType = null; _onlyStartable = null; _currentCategory = null;
                    _currentBounds = null; // force reload
                  }),
                  child: const Text('Zurücksetzen'),
                ),
                IconButton(icon: const Icon(Icons.close), onPressed: () => setState(() => _showFilterSheet = false)),
              ],
            ),
            const SizedBox(height: 12),
            _sectionTitle(context, 'Steckertyp'),
            Wrap(spacing: 8, children: [
              _choice('Alle', _connectorType == null, () => setState(() => _connectorType = null)),
              _choice('CCS', _connectorType == 'CCS', () => setState(() => _connectorType = 'CCS')),
              _choice('Typ 2', _connectorType == 'Type2', () => setState(() => _connectorType = 'Type2')),
              _choice('CHAdeMO', _connectorType == 'CHAdeMO', () => setState(() => _connectorType = 'CHAdeMO')),
            ]),
            const SizedBox(height: 12),
            _sectionTitle(context, 'Stromart'),
            Wrap(spacing: 8, children: [
              _choice('Alle', _currentCategory == null, () => setState(() => _currentCategory = null)),
              _choice('AC', _currentCategory == 'AC', () => setState(() => _currentCategory = 'AC')),
              _choice('DC', _currentCategory == 'DC', () => setState(() => _currentCategory = 'DC')),
            ]),
            const SizedBox(height: 12),
            _sectionTitle(context, 'Ladeleistung'),
            Wrap(spacing: 8, children: [
              _choice('Alle', _minPowerKw == null && _maxPowerKw == null, () => setState(() { _minPowerKw = null; _maxPowerKw = null; })),
              _choice('bis 22 kW', _maxPowerKw == 22, () => setState(() { _minPowerKw = null; _maxPowerKw = 22; })),
              _choice('ab 50 kW', _minPowerKw == 50, () => setState(() { _minPowerKw = 50; _maxPowerKw = null; })),
              _choice('ab 150 kW', _minPowerKw == 150, () => setState(() { _minPowerKw = 150; _maxPowerKw = null; })),
              _choice('ab 300 kW', _minPowerKw == 300, () => setState(() { _minPowerKw = 300; _maxPowerKw = null; })),
            ]),
            const SizedBox(height: 12),
            _sectionTitle(context, 'Verfügbarkeit'),
            Wrap(spacing: 8, children: [
              _choice('Alle Stationen', _onlyStartable != true, () => setState(() => _onlyStartable = null)),
              _choice('Nur über uns startbar', _onlyStartable == true, () => setState(() => _onlyStartable = true)),
            ]),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: FilledButton(
                onPressed: () {
                  setState(() => _showFilterSheet = false);
                  _currentBounds = null; // force reload with new filters
                  _onCameraIdle();
                },
                child: const Text('Filter anwenden'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _sectionTitle(BuildContext context, String title) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Text(title, style: Theme.of(context).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.w600)),
    );
  }

  Widget _choice(String label, bool selected, VoidCallback onTap) {
    return ChoiceChip(label: Text(label), selected: selected, onSelected: (_) => onTap(), visualDensity: VisualDensity.compact);
  }

  Widget _buildLegend(BuildContext context) {
    return Material(
      elevation: 2,
      borderRadius: BorderRadius.circular(8),
      child: Container(
        padding: const EdgeInsets.all(8),
        decoration: BoxDecoration(
          color: Theme.of(context).colorScheme.surface.withAlpha(230),
          borderRadius: BorderRadius.circular(8),
        ),
        child: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            _LegendItem(color: Colors.blue, label: 'AC (≤22 kW)'),
            SizedBox(height: 4),
            _LegendItem(color: Colors.orange, label: 'DC (50 kW)'),
            SizedBox(height: 4),
            _LegendItem(color: Colors.deepPurple, label: 'HPC (≥150 kW)'),
            SizedBox(height: 4),
            _LegendItem(color: Colors.green, label: 'Über uns startbar'),
            SizedBox(height: 4),
            _LegendItem(color: Colors.red, label: 'Nicht startbar'),
          ],
        ),
      ),
    );
  }

  /// Determine marker hue based on max connector power.
  double _markerHueForPower(double maxPowerKw, bool isStartable) {
    // Startable stations get green
    if (isStartable) return BitmapDescriptor.hueGreen;
    // Speed-based colors
    if (maxPowerKw >= 150) return BitmapDescriptor.hueViolet;   // HPC: purple
    if (maxPowerKw >= 43)  return BitmapDescriptor.hueOrange;   // DC: orange
    return BitmapDescriptor.hueAzure;                            // AC: blue
  }

  Widget _buildMap(Position position) {
    final markers = <Marker>{};

    for (final cp in _stations) {
      final lat = double.tryParse(cp['latitude']?.toString() ?? '');
      final lng = double.tryParse(cp['longitude']?.toString() ?? '');
      if (lat == null || lng == null) continue;

      final bool isStartable = cp['is_startable'] == true || cp['is_startable'] == 1;
      final bool isExternal = cp['source'] == 'osm';
      final double maxPower = (cp['max_power_kw'] is num ? (cp['max_power_kw'] as num).toDouble() : 0);

      double hue;
      if (isStartable) {
        hue = BitmapDescriptor.hueGreen;
      } else if (isExternal && maxPower <= 0) {
        hue = BitmapDescriptor.hueRed;
      } else {
        hue = _markerHueForPower(maxPower, false);
      }

      final cpId = cp['id']?.toString() ?? 'osm_${cp['osm_id']}';
      final operatorName = cp['operator_name'] ?? '';
      final name = cp['name'] ?? 'Ladepunkt';

      String snippet = operatorName;
      if (maxPower > 0) snippet += ' • ${maxPower.toInt()} kW';
      if (isStartable) snippet += ' • Startbar ✓';

      markers.add(Marker(
        markerId: MarkerId('cp_$cpId'),
        position: LatLng(lat, lng),
        infoWindow: InfoWindow(
          title: name,
          snippet: snippet,
          onTap: isExternal
              ? null
              : () => context.push('/charge-point/$cpId'),
        ),
        icon: BitmapDescriptor.defaultMarkerWithHue(hue),
      ));
    }

    return GoogleMap(
      initialCameraPosition: CameraPosition(
        target: LatLng(position.latitude, position.longitude),
        zoom: 14,
      ),
      myLocationEnabled: true,
      myLocationButtonEnabled: true,
      zoomControlsEnabled: false,
      markers: markers,
      onMapCreated: (controller) {
        _mapController = controller;
        // Load initial stations after map created
        Future.delayed(const Duration(milliseconds: 500), _onCameraIdle);
      },
      onCameraIdle: _onCameraIdle,
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
        Icon(Icons.location_on, color: color, size: 16),
        const SizedBox(width: 4),
        Text(label, style: const TextStyle(fontSize: 11)),
      ],
    );
  }
}
