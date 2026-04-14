<?php

namespace App\Libraries;

/**
 * Service to fetch charging station data from OpenStreetMap Overpass API.
 * Free, no API key required, uses ODbL licensed data.
 */
class OverpassService
{
    private const API_URL = 'https://overpass-api.de/api/interpreter';
    private int $cacheTtl;

    public function __construct()
    {
        $this->cacheTtl = (int) env('overpass.cacheTtl', 300); // 5 min cache
    }

    /**
     * Fetch charging stations within a bounding box.
     *
     * @param float $latMin Southern latitude
     * @param float $lngMin Western longitude
     * @param float $latMax Northern latitude
     * @param float $lngMax Eastern longitude
     * @param int   $limit  Max results (0 = unlimited from Overpass)
     * @return array Normalized charge point data
     */
    public function getStationsInBBox(float $latMin, float $lngMin, float $latMax, float $lngMax, int $limit = 500): array
    {
        $cacheKey = 'overpass_' . md5("{$latMin},{$lngMin},{$latMax},{$lngMax}");
        $cache    = \Config\Services::cache();

        $cached = $cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $query = sprintf(
            '[out:json][timeout:15];node["amenity"="charging_station"](%s,%s,%s,%s);out body %s;',
            number_format($latMin, 6, '.', ''),
            number_format($lngMin, 6, '.', ''),
            number_format($latMax, 6, '.', ''),
            number_format($lngMax, 6, '.', ''),
            $limit > 0 ? $limit : ''
        );

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => self::API_URL,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query(['data' => $query]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || empty($response)) {
            log_message('warning', "Overpass API returned HTTP {$httpCode}");
            return [];
        }

        $data = json_decode($response, true);
        if (! isset($data['elements'])) {
            return [];
        }

        $stations = [];
        foreach ($data['elements'] as $el) {
            if ($el['type'] !== 'node' || ! isset($el['lat'], $el['lon'])) {
                continue;
            }

            $tags = $el['tags'] ?? [];

            // Skip private stations
            if (($tags['access'] ?? '') === 'private') {
                continue;
            }

            $station = $this->normalizeStation($el['id'], $el['lat'], $el['lon'], $tags);
            if ($station !== null) {
                $stations[] = $station;
            }
        }

        $cache->save($cacheKey, $stations, $this->cacheTtl);

        return $stations;
    }

    /**
     * Normalize OSM data into our charge point format.
     */
    private function normalizeStation(int $osmId, float $lat, float $lng, array $tags): ?array
    {
        $operator = $tags['operator'] ?? $tags['brand'] ?? $tags['network'] ?? 'Unbekannt';
        $name     = $tags['name'] ?? ($operator . ' Ladestation');

        // Parse connectors from socket:* tags
        $connectors = $this->parseConnectors($tags);

        // Determine max power
        $maxPower = 0;
        foreach ($connectors as $c) {
            if ($c['power_kw'] > $maxPower) {
                $maxPower = $c['power_kw'];
            }
        }

        // If no connectors found, infer from other tags
        if (empty($connectors)) {
            $connectors[] = [
                'connector_type' => 'Type2',
                'power_kw'       => 22,
                'current_type'   => 'AC',
                'count'          => max(1, (int) ($tags['capacity'] ?? 1)),
            ];
            $maxPower = 22;
        }

        return [
            'id'             => 'osm_' . $osmId,
            'source'         => 'osm',
            'osm_id'         => $osmId,
            'name'           => mb_substr($name, 0, 255),
            'latitude'       => $lat,
            'longitude'      => $lng,
            'operator_name'  => mb_substr($operator, 0, 255),
            'address'        => trim(($tags['addr:street'] ?? '') . ' ' . ($tags['addr:housenumber'] ?? '')),
            'city'           => $tags['addr:city'] ?? '',
            'postal_code'    => $tags['addr:postcode'] ?? '',
            'max_power_kw'   => $maxPower,
            'is_startable'   => false,
            'status_known'   => false,
            'connectors'     => $connectors,
            'opening_hours'  => $tags['opening_hours'] ?? null,
            'fee'            => isset($tags['fee']) ? ($tags['fee'] === 'yes') : null,
            'network'        => $tags['network'] ?? null,
        ];
    }

    /**
     * Parse socket:* tags into connector list.
     */
    private function parseConnectors(array $tags): array
    {
        $connectors = [];

        $socketMap = [
            'socket:type2'         => ['Type2', 'AC', 22],
            'socket:type2_combo'   => ['CCS', 'DC', 50],
            'socket:chademo'       => ['CHAdeMO', 'DC', 50],
            'socket:type2_cable'   => ['Type2', 'AC', 22],
            'socket:schuko'        => ['Schuko', 'AC', 3.7],
            'socket:cee_blue'      => ['CEE', 'AC', 3.7],
            'socket:type1'         => ['Type1', 'AC', 7.4],
            'socket:type1_combo'   => ['CCS', 'DC', 50],
            'socket:tesla_supercharger' => ['Tesla', 'DC', 150],
        ];

        foreach ($socketMap as $tag => [$type, $current, $defaultPower]) {
            if (isset($tags[$tag]) && $tags[$tag] !== 'no' && $tags[$tag] !== '0') {
                $count = max(1, (int) $tags[$tag]);

                // Check for output tag
                $outputTag = str_replace('socket:', 'socket:', $tag) . ':output';
                $power = $defaultPower;
                if (isset($tags[$outputTag])) {
                    $parsed = $this->parsePower($tags[$outputTag]);
                    if ($parsed > 0) {
                        $power = $parsed;
                    }
                }

                // Also check generic charging_station:output
                if (isset($tags['charging_station:output'])) {
                    $parsed = $this->parsePower($tags['charging_station:output']);
                    if ($parsed > 0 && $parsed > $power) {
                        $power = $parsed;
                    }
                }

                $connectors[] = [
                    'connector_type' => $type,
                    'power_kw'       => $power,
                    'current_type'   => $current,
                    'count'          => $count,
                ];
            }
        }

        return $connectors;
    }

    /**
     * Parse power string like "22 kW", "50kW", "150000 W" into kW float.
     */
    private function parsePower(string $str): float
    {
        $str = strtolower(trim($str));
        if (preg_match('/^([\d.]+)\s*kw/i', $str, $m)) {
            return (float) $m[1];
        }
        if (preg_match('/^([\d.]+)\s*w/i', $str, $m)) {
            return (float) $m[1] / 1000;
        }
        if (preg_match('/^([\d.]+)$/i', $str, $m)) {
            $val = (float) $m[1];
            return $val > 1000 ? $val / 1000 : $val;
        }
        return 0;
    }
}
