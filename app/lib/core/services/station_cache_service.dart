import 'dart:convert';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

final stationCacheServiceProvider = Provider<StationCacheService>((ref) {
  return StationCacheService();
});

/// Separates station data into:
/// - Static data (name, address, coords, connectors, power, operator) — cached long-lived
/// - Dynamic data (availability, status, startability) — refreshed every ~2 min
class StationCacheService {
  static const _staticKey = 'station_static_cache';
  static const _dynamicKey = 'station_dynamic_cache';
  static const _timestampKey = 'station_cache_timestamps';
  static const Duration _staticTtl = Duration(hours: 24);
  static const Duration statusRefreshInterval = Duration(minutes: 2);

  // In-memory caches for fast access
  Map<String, Map<String, dynamic>>? _staticCache;
  Map<String, Map<String, dynamic>>? _dynamicCache;
  Map<String, int>? _timestamps;
  bool _initialized = false;

  /// Initialize caches from SharedPreferences.
  Future<void> init() async {
    if (_initialized) return;
    final prefs = await SharedPreferences.getInstance();

    final staticJson = prefs.getString(_staticKey);
    if (staticJson != null) {
      final decoded = json.decode(staticJson) as Map<String, dynamic>;
      _staticCache = decoded.map((k, v) => MapEntry(k, Map<String, dynamic>.from(v as Map)));
    }

    final dynamicJson = prefs.getString(_dynamicKey);
    if (dynamicJson != null) {
      final decoded = json.decode(dynamicJson) as Map<String, dynamic>;
      _dynamicCache = decoded.map((k, v) => MapEntry(k, Map<String, dynamic>.from(v as Map)));
    }

    final tsJson = prefs.getString(_timestampKey);
    if (tsJson != null) {
      final decoded = json.decode(tsJson) as Map<String, dynamic>;
      _timestamps = decoded.map((k, v) => MapEntry(k, v as int));
    }

    _staticCache ??= {};
    _dynamicCache ??= {};
    _timestamps ??= {};

    // Prune expired static entries
    final now = DateTime.now().millisecondsSinceEpoch;
    final expired = <String>[];
    for (final entry in _timestamps!.entries) {
      if (now - entry.value > _staticTtl.inMilliseconds) {
        expired.add(entry.key);
      }
    }
    for (final key in expired) {
      _staticCache!.remove(key);
      _dynamicCache!.remove(key);
      _timestamps!.remove(key);
    }

    _initialized = true;
  }

  /// Get all cached stations (static merged with dynamic).
  Map<String, Map<String, dynamic>> getAllStations() {
    final merged = <String, Map<String, dynamic>>{};
    for (final entry in (_staticCache ?? {}).entries) {
      merged[entry.key] = {
        ...entry.value,
        ...(_dynamicCache?[entry.key] ?? {}),
      };
    }
    return merged;
  }

  /// Get stations that fall within given bounds from cache.
  Map<String, Map<String, dynamic>> getStationsInBounds(
    double latMin, double lngMin, double latMax, double lngMax,
  ) {
    final result = <String, Map<String, dynamic>>{};
    for (final entry in getAllStations().entries) {
      final lat = double.tryParse(entry.value['latitude']?.toString() ?? '');
      final lng = double.tryParse(entry.value['longitude']?.toString() ?? '');
      if (lat == null || lng == null) continue;
      if (lat >= latMin && lat <= latMax && lng >= lngMin && lng <= lngMax) {
        result[entry.key] = entry.value;
      }
    }
    return result;
  }

  /// Merge new station data from API into cache.
  /// Separates static and dynamic fields automatically.
  void mergeStations(List<Map<String, dynamic>> stations) {
    final now = DateTime.now().millisecondsSinceEpoch;
    for (final cp in stations) {
      final key = _stationKey(cp);

      // Static data — rarely changes
      _staticCache![key] = {
        'id': cp['id'],
        'source': cp['source'] ?? 'local',
        'osm_id': cp['osm_id'],
        'name': cp['name'],
        'latitude': cp['latitude'],
        'longitude': cp['longitude'],
        'address': cp['address'],
        'city': cp['city'],
        'postal_code': cp['postal_code'],
        'operator_name': cp['operator_name'],
        'max_power_kw': cp['max_power_kw'],
        'connectors': cp['connectors'],
        'opening_hours': cp['opening_hours'],
        'fee': cp['fee'],
        'network': cp['network'],
      };

      // Dynamic data — changes frequently
      _dynamicCache![key] = {
        'is_startable': cp['is_startable'],
        'status_known': cp['status_known'] ?? true,
        'total_connectors': cp['total_connectors'] ?? 0,
        'available_connectors': cp['available_connectors'] ?? 0,
        'last_status_refresh': now,
      };

      _timestamps![key] = now;
    }
  }

  /// Update only dynamic status data for specific stations.
  void updateStatus(Map<String, Map<String, dynamic>> statusMap) {
    final now = DateTime.now().millisecondsSinceEpoch;
    for (final entry in statusMap.entries) {
      final key = 'local_${entry.key}';
      if (_dynamicCache!.containsKey(key)) {
        _dynamicCache![key] = {
          ..._dynamicCache![key]!,
          ...entry.value,
          'last_status_refresh': now,
        };
      }
    }
  }

  /// Get station IDs that need a status refresh (older than interval).
  List<int> getStaleLocalStationIds() {
    final now = DateTime.now().millisecondsSinceEpoch;
    final stale = <int>[];
    for (final entry in (_dynamicCache ?? {}).entries) {
      if (!entry.key.startsWith('local_')) continue;
      final lastRefresh = entry.value['last_status_refresh'] as int? ?? 0;
      if (now - lastRefresh > statusRefreshInterval.inMilliseconds) {
        final id = int.tryParse(entry.key.replaceFirst('local_', ''));
        if (id != null) stale.add(id);
      }
    }
    return stale;
  }

  /// Persist caches to SharedPreferences.
  Future<void> persist() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_staticKey, json.encode(_staticCache));
    await prefs.setString(_dynamicKey, json.encode(_dynamicCache));
    await prefs.setString(_timestampKey, json.encode(_timestamps));
  }

  /// Full clear (on logout or force refresh).
  Future<void> clear() async {
    _staticCache?.clear();
    _dynamicCache?.clear();
    _timestamps?.clear();
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_staticKey);
    await prefs.remove(_dynamicKey);
    await prefs.remove(_timestampKey);
  }

  /// Preload station locations into static cache.
  /// Only stores static data (coords, name, address) — no status.
  /// Stations without existing dynamic data get a placeholder.
  void preloadLocations(List<Map<String, dynamic>> stations) {
    final now = DateTime.now().millisecondsSinceEpoch;
    for (final cp in stations) {
      final key = _stationKey(cp);

      // Static data — overwrite with fresh preload data
      _staticCache![key] = {
        'id': cp['id'],
        'source': cp['source'] ?? 'local',
        'osm_id': cp['osm_id'],
        'name': cp['name'],
        'latitude': cp['latitude'],
        'longitude': cp['longitude'],
        'address': cp['address'],
        'city': cp['city'],
        'postal_code': cp['postal_code'],
        'operator_name': cp['operator_name'],
        'max_power_kw': cp['max_power_kw'],
        'connector_count': cp['connector_count'],
        'connector_types': cp['connector_types'],
      };

      // Only set dynamic placeholder if not already cached
      _dynamicCache![key] ??= {
        'is_startable': cp['is_startable'] ?? false,
        'status_known': false,
        'total_connectors': cp['connector_count'] ?? 0,
        'available_connectors': 0,
        'last_status_refresh': 0, // marks as stale → will be refreshed
      };

      _timestamps![key] ??= now;
    }
  }

  /// Check if preloaded locations exist in cache.
  bool get hasPreloadedLocations => (_staticCache?.length ?? 0) > 0;

  /// Get IDs of local stations within given bounds that need status refresh.
  List<int> getStaleLocalIdsInBounds(
    double latMin, double lngMin, double latMax, double lngMax,
  ) {
    final now = DateTime.now().millisecondsSinceEpoch;
    final stale = <int>[];
    for (final entry in (_staticCache ?? {}).entries) {
      if (!entry.key.startsWith('local_')) continue;
      final lat = double.tryParse(entry.value['latitude']?.toString() ?? '');
      final lng = double.tryParse(entry.value['longitude']?.toString() ?? '');
      if (lat == null || lng == null) continue;
      if (lat < latMin || lat > latMax || lng < lngMin || lng > lngMax) continue;

      final dynamic = _dynamicCache?[entry.key];
      final lastRefresh = (dynamic?['last_status_refresh'] as int?) ?? 0;
      if (now - lastRefresh > statusRefreshInterval.inMilliseconds) {
        final id = int.tryParse(entry.key.replaceFirst('local_', ''));
        if (id != null) stale.add(id);
      }
    }
    return stale;
  }

  String _stationKey(Map<String, dynamic> cp) {
    if (cp['source'] == 'osm') return 'osm_${cp['osm_id']}';
    return 'local_${cp['id']}';
  }

  int get stationCount => _staticCache?.length ?? 0;
}
