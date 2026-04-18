import 'dart:convert';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:path_provider/path_provider.dart';
import 'package:sqflite/sqflite.dart';

final stationCacheServiceProvider = Provider<StationCacheService>((ref) {
  return StationCacheService();
});

/// Persistent local station database using SQLite.
/// Static data (name, coords, connectors) cached long-lived.
/// Dynamic data (availability, status) refreshed every 2 min for visible stations.
class StationCacheService {
  static const Duration statusRefreshInterval = Duration(minutes: 2);
  static const Duration _staticTtl = Duration(hours: 24);
  static const int _dbVersion = 1;

  Database? _db;
  bool _initialized = false;
  int _stationCount = 0;
  DateTime? _lastFullSync;

  Future<void> init() async {
    if (_initialized) return;

    final dir = await getApplicationDocumentsDirectory();
    _db = await openDatabase(
      '${dir.path}/stations_v1.db',
      version: _dbVersion,
      onCreate: _createDb,
    );

    // Prune expired entries (older than 24h)
    final cutoff = DateTime.now().millisecondsSinceEpoch - _staticTtl.inMilliseconds;
    await _db!.delete('stations', where: 'cached_at < ?', whereArgs: [cutoff]);

    _stationCount = Sqflite.firstIntValue(
      await _db!.rawQuery('SELECT COUNT(*) FROM stations'),
    ) ?? 0;

    // Read last sync timestamp
    final meta = await _db!.query('sync_meta', where: 'key = ?', whereArgs: ['last_full_sync']);
    if (meta.isNotEmpty) {
      final ts = meta.first['value'] as int?;
      if (ts != null) _lastFullSync = DateTime.fromMillisecondsSinceEpoch(ts);
    }

    _initialized = true;
  }

  Future<void> _createDb(Database db, int version) async {
    await db.execute('''
      CREATE TABLE stations (
        key TEXT PRIMARY KEY,
        id INTEGER,
        source TEXT NOT NULL DEFAULT 'local',
        osm_id TEXT,
        name TEXT,
        latitude REAL,
        longitude REAL,
        address TEXT,
        city TEXT,
        postal_code TEXT,
        operator_name TEXT,
        max_power_kw REAL DEFAULT 0,
        connector_count INTEGER DEFAULT 0,
        connector_types TEXT,
        connectors_json TEXT,
        opening_hours TEXT,
        fee TEXT,
        network TEXT,
        is_startable INTEGER DEFAULT 0,
        status_known INTEGER DEFAULT 0,
        total_connectors INTEGER DEFAULT 0,
        available_connectors INTEGER DEFAULT 0,
        occupied_connectors INTEGER DEFAULT 0,
        out_of_service INTEGER DEFAULT 0,
        last_status_refresh INTEGER DEFAULT 0,
        cached_at INTEGER NOT NULL
      )
    ''');
    await db.execute(
        'CREATE INDEX idx_stations_bounds ON stations(latitude, longitude)');
    await db.execute(
        'CREATE INDEX idx_stations_source ON stations(source)');
    await db.execute(
        'CREATE INDEX idx_stations_stale ON stations(source, last_status_refresh)');

    await db.execute('''
      CREATE TABLE sync_meta (
        key TEXT PRIMARY KEY,
        value INTEGER
      )
    ''');
  }

  /// Get all cached stations as a map.
  Future<Map<String, Map<String, dynamic>>> getAllStations() async {
    final rows = await _db!.query('stations');
    final result = <String, Map<String, dynamic>>{};
    for (final row in rows) {
      result[row['key'] as String] = _rowToStationMap(row);
    }
    return result;
  }

  /// Get stations within bounds from SQLite (indexed query).
  Future<Map<String, Map<String, dynamic>>> getStationsInBounds(
    double latMin, double lngMin, double latMax, double lngMax,
  ) async {
    final rows = await _db!.query('stations',
      where: 'latitude >= ? AND latitude <= ? AND longitude >= ? AND longitude <= ?',
      whereArgs: [latMin, latMax, lngMin, lngMax],
    );
    final result = <String, Map<String, dynamic>>{};
    for (final row in rows) {
      result[row['key'] as String] = _rowToStationMap(row);
    }
    return result;
  }

  /// Merge new station data from nearby/bounding box API into cache.
  Future<void> mergeStations(List<Map<String, dynamic>> stations) async {
    final now = DateTime.now().millisecondsSinceEpoch;
    final batch = _db!.batch();

    for (final cp in stations) {
      final key = _stationKey(cp);
      final connectors = cp['connectors'];
      final connectorsJson = connectors != null ? json.encode(connectors) : null;

      int total = 0, available = 0, occupied = 0, outOfService = 0;
      if (connectors is List) {
        total = connectors.length;
        for (final c in connectors) {
          final s = (c is Map ? c['status'] : null) ?? 'unknown';
          if (s == 'available') available++;
          else if (s == 'occupied') occupied++;
          else if (s == 'out_of_service') outOfService++;
        }
      }

      final types = <String>[];
      double maxPwr = 0;
      if (connectors is List) {
        for (final c in connectors) {
          if (c is Map) {
            final t = c['connector_type']?.toString();
            if (t != null && t.isNotEmpty) types.add(t);
            final p = (c['power_kw'] is num) ? (c['power_kw'] as num).toDouble() : 0.0;
            if (p > maxPwr) maxPwr = p;
          }
        }
      }

      batch.insert('stations', {
        'key': key,
        'id': cp['id'],
        'source': cp['source'] ?? 'local',
        'osm_id': cp['osm_id']?.toString(),
        'name': cp['name'],
        'latitude': _toDouble(cp['latitude']),
        'longitude': _toDouble(cp['longitude']),
        'address': cp['address'],
        'city': cp['city'],
        'postal_code': cp['postal_code'],
        'operator_name': cp['operator_name'],
        'max_power_kw': maxPwr > 0 ? maxPwr : _toDouble(cp['max_power_kw']),
        'connector_count': total > 0 ? total : (cp['connector_count'] ?? 0),
        'connector_types': json.encode(types.isNotEmpty ? types.toSet().toList() : (cp['connector_types'] ?? [])),
        'connectors_json': connectorsJson,
        'opening_hours': cp['opening_hours'],
        'fee': cp['fee'],
        'network': cp['network'],
        'is_startable': (cp['is_startable'] == true || cp['is_startable'] == 1) ? 1 : 0,
        'status_known': total > 0 ? 1 : 0,
        'total_connectors': total > 0 ? total : (cp['total_connectors'] ?? 0),
        'available_connectors': available,
        'occupied_connectors': occupied,
        'out_of_service': outOfService,
        'last_status_refresh': total > 0 ? now : 0,
        'cached_at': now,
      }, conflictAlgorithm: ConflictAlgorithm.replace);
    }

    await batch.commit(noResult: true);
    _stationCount = Sqflite.firstIntValue(
      await _db!.rawQuery('SELECT COUNT(*) FROM stations'),
    ) ?? 0;
  }

  /// Update only dynamic status data for specific stations.
  Future<void> updateStatus(Map<String, Map<String, dynamic>> statusMap) async {
    final now = DateTime.now().millisecondsSinceEpoch;
    final batch = _db!.batch();

    for (final entry in statusMap.entries) {
      final key = 'local_${entry.key}';
      final s = entry.value;

      final connectorsJson = s['connectors'] != null ? json.encode(s['connectors']) : null;

      final updateData = <String, dynamic>{
        'total_connectors': s['total_connectors'] ?? 0,
        'available_connectors': s['available_connectors'] ?? 0,
        'occupied_connectors': s['occupied_connectors'] ?? 0,
        'out_of_service': s['out_of_service'] ?? 0,
        'is_startable': (s['is_startable'] == true) ? 1 : 0,
        'status_known': 1,
        'last_status_refresh': now,
      };

      if (connectorsJson != null) {
        updateData['connectors_json'] = connectorsJson;
      }

      batch.update('stations', updateData,
        where: 'key = ?', whereArgs: [key],
      );
    }

    await batch.commit(noResult: true);
  }

  /// Get station IDs that need a status refresh (older than interval).
  Future<List<int>> getStaleLocalStationIds() async {
    final cutoff = DateTime.now().millisecondsSinceEpoch -
        statusRefreshInterval.inMilliseconds;
    final rows = await _db!.query('stations',
      columns: ['id'],
      where: "source = 'local' AND last_status_refresh < ?",
      whereArgs: [cutoff],
    );
    return rows.map((r) => r['id'] as int).toList();
  }

  /// Get IDs of local stations within bounds that need status refresh.
  Future<List<int>> getStaleLocalIdsInBounds(
    double latMin, double lngMin, double latMax, double lngMax,
  ) async {
    final cutoff = DateTime.now().millisecondsSinceEpoch -
        statusRefreshInterval.inMilliseconds;
    final rows = await _db!.query('stations',
      columns: ['id'],
      where: "source = 'local' AND last_status_refresh < ? "
          "AND latitude >= ? AND latitude <= ? "
          "AND longitude >= ? AND longitude <= ?",
      whereArgs: [cutoff, latMin, latMax, lngMin, lngMax],
    );
    return rows.map((r) => r['id'] as int).toList();
  }

  /// Preload station locations into SQLite.
  /// Only stores static data — status preserved via ON CONFLICT.
  Future<void> preloadLocations(List<Map<String, dynamic>> stations) async {
    final now = DateTime.now().millisecondsSinceEpoch;
    final batch = _db!.batch();

    for (final cp in stations) {
      final key = _stationKey(cp);

      batch.rawInsert('''
        INSERT INTO stations (key, id, source, name, latitude, longitude, address, city,
          postal_code, operator_name, max_power_kw, connector_count, connector_types,
          is_startable, status_known, total_connectors, available_connectors,
          last_status_refresh, cached_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, 0, 0, ?)
        ON CONFLICT(key) DO UPDATE SET
          name = excluded.name,
          latitude = excluded.latitude,
          longitude = excluded.longitude,
          address = excluded.address,
          city = excluded.city,
          postal_code = excluded.postal_code,
          operator_name = excluded.operator_name,
          max_power_kw = excluded.max_power_kw,
          connector_count = excluded.connector_count,
          connector_types = excluded.connector_types,
          is_startable = excluded.is_startable,
          cached_at = excluded.cached_at
      ''', [
        key,
        cp['id'],
        cp['source'] ?? 'local',
        cp['name'],
        _toDouble(cp['latitude']),
        _toDouble(cp['longitude']),
        cp['address'],
        cp['city'],
        cp['postal_code'],
        cp['operator_name'],
        _toDouble(cp['max_power_kw']),
        cp['connector_count'] ?? 0,
        json.encode(cp['connector_types'] ?? []),
        (cp['is_startable'] == true || cp['is_startable'] == 1) ? 1 : 0,
        cp['connector_count'] ?? 0,
        now,
      ]);
    }

    await batch.commit(noResult: true);
    _stationCount = Sqflite.firstIntValue(
      await _db!.rawQuery('SELECT COUNT(*) FROM stations'),
    ) ?? 0;

    // Store sync timestamp
    await _db!.insert('sync_meta',
      {'key': 'last_full_sync', 'value': now},
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
    _lastFullSync = DateTime.fromMillisecondsSinceEpoch(now);
  }

  /// Check if preloaded locations exist in database.
  bool get hasPreloadedLocations => _stationCount > 0;

  /// Last full sync timestamp.
  DateTime? get lastFullSync => _lastFullSync;

  /// Whether a delta sync is sufficient (full sync < 1h ago).
  bool get needsFullSync =>
      _lastFullSync == null ||
      DateTime.now().difference(_lastFullSync!) > const Duration(hours: 1);

  /// Total station count (sync getter for UI).
  int get stationCount => _stationCount;

  /// No-op — SQLite auto-persists.
  Future<void> persist() async {}

  /// Full clear (on logout or force refresh).
  Future<void> clear() async {
    await _db?.delete('stations');
    await _db?.delete('sync_meta');
    _stationCount = 0;
    _lastFullSync = null;
  }

  // ── Helpers ──

  Map<String, dynamic> _rowToStationMap(Map<String, dynamic> row) {
    return {
      'id': row['id'],
      'source': row['source'],
      'osm_id': row['osm_id'],
      'name': row['name'],
      'latitude': row['latitude'],
      'longitude': row['longitude'],
      'address': row['address'],
      'city': row['city'],
      'postal_code': row['postal_code'],
      'operator_name': row['operator_name'],
      'max_power_kw': row['max_power_kw'],
      'connector_count': row['connector_count'],
      'connector_types': _decodeJsonList(row['connector_types']),
      'connectors': _decodeJsonList(row['connectors_json']),
      'opening_hours': row['opening_hours'],
      'fee': row['fee'],
      'network': row['network'],
      'is_startable': (row['is_startable'] as int?) == 1,
      'status_known': (row['status_known'] as int?) == 1,
      'total_connectors': row['total_connectors'] ?? 0,
      'available_connectors': row['available_connectors'] ?? 0,
      'occupied_connectors': row['occupied_connectors'] ?? 0,
      'out_of_service': row['out_of_service'] ?? 0,
      'last_status_refresh': row['last_status_refresh'] ?? 0,
    };
  }

  List<dynamic> _decodeJsonList(dynamic value) {
    if (value == null) return [];
    if (value is String && value.isNotEmpty) {
      try {
        return json.decode(value) as List<dynamic>;
      } catch (_) {
        return [];
      }
    }
    return [];
  }

  String _stationKey(Map<String, dynamic> cp) {
    if (cp['source'] == 'osm') return 'osm_${cp['osm_id']}';
    return 'local_${cp['id']}';
  }

  double _toDouble(dynamic v) {
    if (v is num) return v.toDouble();
    if (v is String) return double.tryParse(v) ?? 0;
    return 0;
  }
}
