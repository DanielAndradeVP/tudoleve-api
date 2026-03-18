<?php

namespace App\Domain\Payments\Enums;

enum PaymentMethod: string
{
    case PIX = 'pix';
    case CREDIT_CARD = 'credit_card';
    case BOLETO = 'boleto';
}

