<?php

namespace App\Models;

use CodeIgniter\Model;

class ProviderModel extends Model
{
    protected $table            = 'providers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'name', 'slug', 'adapter_class', 'config_encrypted',
        'roaming_fee_type', 'roaming_fee_value', 'is_active',
    ];

    public function getActive(): array
    {
        return $this->where('is_active', 1)->findAll();
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)->first();
    }
}
