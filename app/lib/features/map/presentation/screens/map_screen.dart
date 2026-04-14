import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:geolocator/geolocator.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:einfach_laden/features/charge_point/providers/charge_point_provider.dart';

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
  // Used for camera animations later
  // ignore: unused_field
  GoogleMapController? _mapController;
  double? _minPowerKw;

  @override
  Widget build(BuildContext context) {
    final location = ref.watch(userLocationProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Ladepunkte'),
        actions: [
          PopupMenuButton<double?>(
            icon: const Icon(Icons.tune),
            onSelected: (v) => setState(() => _minPowerKw = v),
            itemBuilder: (_) => [
              const PopupMenuItem(value: null, child: Text('Alle anzeigen')),
              const PopupMenuItem(value: 11, child: Text('ab 11 kW')),
              const PopupMenuItem(value: 22, child: Text('ab 22 kW')),
              const PopupMenuItem(value: 50, child: Text('ab 50 kW')),
              const PopupMenuItem(value: 100, child: Text('ab 100 kW')),
              const PopupMenuItem(value: 150, child: Text('ab 150 kW')),
              const PopupMenuItem(value: 300, child: Text('ab 300 kW')),
            ],
          ),
        ],
      ),
      body: location.when(
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
    );
  }

  Widget _buildMap(Position position) {
    final chargePoints = ref.watch(nearbyChargePointsProvider(
      (lat: position.latitude, lng: position.longitude, radius: 50, minPowerKw: _minPowerKw),
    ));

    final markers = <Marker>{};

    chargePoints.whenData((points) {
      for (final cp in points) {
        final lat = double.tryParse(cp['latitude']?.toString() ?? '');
        final lng = double.tryParse(cp['longitude']?.toString() ?? '');
        if (lat != null && lng != null) {
          markers.add(Marker(
            markerId: MarkerId('cp_${cp['id']}'),
            position: LatLng(lat, lng),
            infoWindow: InfoWindow(
              title: cp['name'] ?? 'Ladepunkt',
              snippet: cp['operator_name'] ?? '',
              onTap: () => context.push('/charge-point/${cp['id']}'),
            ),
            icon: BitmapDescriptor.defaultMarkerWithHue(BitmapDescriptor.hueGreen),
          ));
        }
      }
    });

    return GoogleMap(
      initialCameraPosition: CameraPosition(
        target: LatLng(position.latitude, position.longitude),
        zoom: 13,
      ),
      myLocationEnabled: true,
      myLocationButtonEnabled: true,
      markers: markers,
      onMapCreated: (controller) => _mapController = controller,
    );
  }
}
