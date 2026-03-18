<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'public_id',
        'order_number',
        'customer_id',
        'billing_address_id',
        'shipping_address_id',
        'subtotal',
        'discount_total',
        'shipping_total',
        'grand_total',
        'quoted_shipping_cost',
        'currency',
        'status',
        'payment_status',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function billingAddress()
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    public function shippingAddress()
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
}

