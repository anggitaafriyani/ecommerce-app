<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MidtransNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'string', 'max:50'],
            'status_code' => ['required', 'string', 'regex:/^[0-9]{3}$/'],
            'gross_amount' => ['required', 'string', 'regex:/^\d{1,10}(\.\d{1,2})?$/'],
            'signature_key' => ['required', 'string', 'size:128'],
            'transaction_status' => ['required', 'string', 'max:50'],
        ];
    }
}
