<?php

namespace App\Models;

use CodeIgniter\Model;

class QrScanLogModel extends Model
{
    protected $table            = 'qr_scan_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'user_id', 'qr_content', 'latitude', 'longitude',
        'recognized', 'charge_point_id', 'provider_name',
        'is_startable', 'ip_address', 'created_at',
    ];

    public function log(array $data): int
    {
        $data['ip_address'] = service('request')->getIPAddress();
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    public function getRecent(int $limit = 100): array
    {
        return $this->orderBy('created_at', 'DESC')
                     ->limit($limit)
                     ->findAll();
    }

    public function getUnrecognized(int $limit = 100): array
    {
        return $this->where('recognized', 0)
                     ->orderBy('created_at', 'DESC')
                     ->limit($limit)
                     ->findAll();
    }
}
