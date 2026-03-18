<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingMethod extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'public_id',
        'name',
        'code',
        'is_active',
        'base_cost',
        'estimated_min_days',
        'estimated_max_days',
        'configuration',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'configuration' => 'array',
    ];

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
}

