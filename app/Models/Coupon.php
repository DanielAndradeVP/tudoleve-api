<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'public_id',
        'code',
        'discount_type',
        'discount_value',
        'max_discount_amount',
        'starts_at',
        'ends_at',
        'usage_limit',
        'usage_per_customer',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'bool',
    ];

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }
}

