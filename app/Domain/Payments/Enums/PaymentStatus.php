<?php

namespace App\Domain\Payments\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case FAILED = 'failed';
}

