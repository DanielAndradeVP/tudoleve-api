<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'public_id',
        'customer_id',
        'session_id',
        'subtotal',
        'discount_total',
        'shipping_total',
        'grand_total',
        'abandoned_at',
        'last_activity_at',
        'recovery_token',
    ];

    protected $casts = [
        'abandoned_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
}

