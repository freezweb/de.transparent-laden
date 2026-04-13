/// Demo-Daten für die App, wenn die API noch keine echten Daten liefert.

class DemoData {
  DemoData._();

  static Map<String, dynamic> chargingHistory() => {
        'sessions': [
          {
            'id': 9001,
            'status': 'completed',
            'energy_kwh': 32.4,
            'total_price_cent': 1296,
            'created_at': '2026-04-12 14:23',
            'connector_id': 1,
            'pricing': {
              'energy_cost_cent': 972,
              'service_fee_cent': 162,
              'operator_share_cent': 162,
              'kwh_price_cent': 40,
            },
          },
          {
            'id': 9002,
            'status': 'completed',
            'energy_kwh': 18.7,
            'total_price_cent': 748,
            'created_at': '2026-04-10 09:15',
            'connector_id': 2,
            'pricing': {
              'energy_cost_cent': 561,
              'service_fee_cent': 94,
              'operator_share_cent': 93,
              'kwh_price_cent': 40,
            },
          },
          {
            'id': 9003,
            'status': 'completed',
            'energy_kwh': 45.1,
            'total_price_cent': 1804,
            'created_at': '2026-04-08 18:42',
            'connector_id': 1,
            'pricing': {
              'energy_cost_cent': 1353,
              'service_fee_cent': 226,
              'operator_share_cent': 225,
              'kwh_price_cent': 40,
            },
          },
          {
            'id': 9004,
            'status': 'completed',
            'energy_kwh': 12.3,
            'total_price_cent': 492,
            'created_at': '2026-04-05 11:30',
            'connector_id': 3,
          },
          {
            'id': 9005,
            'status': 'cancelled',
            'energy_kwh': 2.1,
            'total_price_cent': 84,
            'created_at': '2026-04-03 16:08',
            'connector_id': 2,
          },
        ],
        'total': 5,
        'page': 1,
        'per_page': 20,
      };

  static Map<String, dynamic> invoices() => {
        'invoices': [
          {
            'id': 8001,
            'invoice_number': 'TL-2026-0042',
            'type': 'charging',
            'created_at': '2026-04-12',
            'total_gross_cent': 1296,
            'session_id': 9001,
            'pricing': {
              'energy_cost_cent': 972,
              'service_fee_cent': 162,
              'operator_share_cent': 162,
              'kwh_price_cent': 40,
            },
          },
          {
            'id': 8002,
            'invoice_number': 'TL-2026-0041',
            'type': 'charging',
            'created_at': '2026-04-10',
            'total_gross_cent': 748,
            'session_id': 9002,
            'pricing': {
              'energy_cost_cent': 561,
              'service_fee_cent': 94,
              'operator_share_cent': 93,
              'kwh_price_cent': 40,
            },
          },
          {
            'id': 8003,
            'invoice_number': 'TL-2026-0040',
            'type': 'charging',
            'created_at': '2026-04-08',
            'total_gross_cent': 1804,
            'session_id': 9003,
          },
          {
            'id': 8004,
            'invoice_number': 'TL-2026-0039',
            'type': 'charging',
            'created_at': '2026-04-05',
            'total_gross_cent': 492,
            'session_id': 9004,
          },
        ],
        'total': 4,
        'page': 1,
        'per_page': 20,
      };

  static List<Map<String, dynamic>> chargePoints() => [
        {
          'id': 7001,
          'name': 'Stadtwerke München – Odeonsplatz',
          'operator_name': 'Stadtwerke München',
          'latitude': '48.1426',
          'longitude': '11.5770',
          'address': 'Odeonsplatz 1, 80539 München',
          'connectors': [
            {'id': 1, 'type': 'CCS', 'power_kw': 150, 'status': 'available'},
            {'id': 2, 'type': 'Type 2', 'power_kw': 22, 'status': 'available'},
          ],
        },
        {
          'id': 7002,
          'name': 'EnBW Schnelllader – Marienplatz',
          'operator_name': 'EnBW',
          'latitude': '48.1374',
          'longitude': '11.5755',
          'address': 'Marienplatz 8, 80331 München',
          'connectors': [
            {'id': 3, 'type': 'CCS', 'power_kw': 300, 'status': 'available'},
          ],
        },
        {
          'id': 7003,
          'name': 'IONITY – Allianz Arena',
          'operator_name': 'IONITY',
          'latitude': '48.2188',
          'longitude': '11.6247',
          'address': 'Werner-Heisenberg-Allee 25, 80939 München',
          'connectors': [
            {'id': 4, 'type': 'CCS', 'power_kw': 350, 'status': 'occupied'},
            {'id': 5, 'type': 'CCS', 'power_kw': 350, 'status': 'available'},
          ],
        },
        {
          'id': 7004,
          'name': 'Ladepark Süd – Obersendling',
          'operator_name': 'Transparent Laden',
          'latitude': '48.0994',
          'longitude': '11.5380',
          'address': 'Gmunder Str. 37, 81379 München',
          'connectors': [
            {'id': 6, 'type': 'Type 2', 'power_kw': 11, 'status': 'available'},
            {'id': 7, 'type': 'Type 2', 'power_kw': 22, 'status': 'available'},
          ],
        },
        {
          'id': 7005,
          'name': 'E.ON Charger – Schwabing',
          'operator_name': 'E.ON',
          'latitude': '48.1651',
          'longitude': '11.5861',
          'address': 'Leopoldstr. 77, 80802 München',
          'connectors': [
            {'id': 8, 'type': 'CCS', 'power_kw': 50, 'status': 'available'},
          ],
        },
      ];
}
