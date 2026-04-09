<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentMethodModel extends Model
{
    protected $table            = 'payment_methods';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'user_id', 'type', 'label', 'is_default',
        'external_reference', 'fee_model_id', 'status',
    ];

    public function getForUser(int $userId): array
    {
        return $this->where('user_id', $userId)
                     ->where('status', 'active')
                     ->findAll();
    }

    public function getDefault(int $userId): ?array
    {
        return $this->where('user_id', $userId)
                     ->where('status', 'active')
                     ->where('is_default', 1)
                     ->first();
    }

    public function setDefault(int $userId, int $methodId): bool
    {
        $this->where('user_id', $userId)->set(['is_default' => 0])->update();
        return $this->update($methodId, ['is_default' => 1]);
    }
}
