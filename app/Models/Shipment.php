<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'public_id',
        'order_id',
        'shipping_method_id',
        'shipping_cost',
        'tracking_code',
        'logistics_status',
        'estimated_delivery_at',
        'delivered_at',
        'external_reference',
        'label_url',
        'metadata',
    ];

    protected $casts = [
        'estimated_delivery_at' => 'datetime',
        'delivered_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class);
    }
}

