<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'customer';
    }

    public function rules(): array
    {
        return [
            'checkout_token' => ['required', 'uuid'],
            'recipient_name' => ['required', 'string', 'max:100'],
            'recipient_phone' => ['required', 'string', 'regex:/^\+?[0-9][0-9\s-]{7,19}$/'],
            'shipping_address' => ['required', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'regex:/^[0-9]{5}$/'],
            'courier_code' => ['nullable', 'string', 'max:50'],
            'courier_service' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'recipient_name' => 'nama penerima',
            'recipient_phone' => 'nomor telepon',
            'shipping_address' => 'alamat lengkap',
            'city' => 'kota/kabupaten',
            'province' => 'provinsi',
            'postal_code' => 'kode pos',
            'courier_code' => 'kurir pengiriman',
            'courier_service' => 'layanan pengiriman',
        ];
    }
}
