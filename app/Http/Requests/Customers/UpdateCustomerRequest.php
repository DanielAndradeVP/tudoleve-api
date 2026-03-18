<?php

namespace App\Http\Requests\Customers;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
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
        $customerId = $this->route('customer');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:customers,email,' . $customerId],
            'phone' => ['sometimes', 'string', 'max:50'],
            'password' => ['sometimes', 'string', 'min:8'],
        ];
    }
}

