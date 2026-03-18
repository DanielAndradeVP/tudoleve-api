<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'public_id',
        'customer_id',
        'label',
        'recipient_name',
        'street',
        'number',
        'complement',
        'district',
        'city',
        'state',
        'postal_code',
        'country',
        'is_default',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}

