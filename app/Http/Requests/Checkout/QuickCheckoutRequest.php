<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class QuickCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'payment_method' => ['required', 'string', 'in:pix,credit_card,boleto'],
            'shipping_method_id' => ['required', 'integer', 'exists:shipping_methods,id'],
        ];
    }
}

