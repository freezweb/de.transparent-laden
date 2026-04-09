<?php

namespace App\Models;

use CodeIgniter\Model;

class SystemConfigModel extends Model
{
    protected $table            = 'system_config';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'config_key', 'config_value', 'description', 'updated_by',
    ];

    public function getValue(string $key, $default = null): ?string
    {
        $row = $this->where('config_key', $key)->first();
        return $row ? $row['config_value'] : $default;
    }

    public function setValue(string $key, string $value, ?string $updatedBy = null): bool
    {
        $existing = $this->where('config_key', $key)->first();
        if ($existing) {
            return $this->update($existing['id'], [
                'config_value' => $value,
                'updated_by'   => $updatedBy,
            ]);
        }

        return (bool) $this->insert([
            'config_key'   => $key,
            'config_value' => $value,
            'updated_by'   => $updatedBy,
        ]);
    }
}
