<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization can be refined with policies; for now we allow
        // the authenticated customer to perform checkout.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'cart_public_id' => ['required', 'uuid'],
            'billing_address_id' => ['required', 'integer', 'exists:addresses,id'],
            'shipping_address_id' => ['required', 'integer', 'exists:addresses,id'],
            'shipping_method_id' => ['required', 'integer', 'exists:shipping_methods,id'],
            'payment_method' => ['required', 'string', 'in:pix,credit_card,boleto'],
            'payment_provider' => ['nullable', 'string', 'max:255'],
            'coupon_code' => ['nullable', 'string', 'max:255'],
        ];
    }
}

