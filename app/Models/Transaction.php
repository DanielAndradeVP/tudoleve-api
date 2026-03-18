<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'public_id',
        'payment_id',
        'transaction_type',
        'amount',
        'currency',
        'status',
        'external_reference',
        'raw_payload',
    ];

    protected $casts = [
        'raw_payload' => 'array',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}

