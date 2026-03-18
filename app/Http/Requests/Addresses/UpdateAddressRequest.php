<?php

namespace App\Http\Requests\Addresses;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'label' => ['sometimes', 'string', 'max:255'],
            'recipient_name' => ['sometimes', 'string', 'max:255'],
            'street' => ['sometimes', 'string', 'max:255'],
            'number' => ['sometimes', 'string', 'max:50'],
            'complement' => ['sometimes', 'string', 'max:255'],
            'district' => ['sometimes', 'string', 'max:255'],
            'city' => ['sometimes', 'string', 'max:255'],
            'state' => ['sometimes', 'string', 'size:2'],
            'postal_code' => ['sometimes', 'string', 'max:20'],
            'country' => ['sometimes', 'string', 'size:2'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }
}

