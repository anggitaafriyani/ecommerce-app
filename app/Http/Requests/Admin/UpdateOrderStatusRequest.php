<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_ADMIN;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order_status' => ['required', Rule::in(['shipped', 'completed'])],
            'payment_status' => ['prohibited'],
            'transaction_status' => ['prohibited'],
            'stock_reserved' => ['prohibited'],
            'stock_released_at' => ['prohibited'],
            'total_amount' => ['prohibited'],
        ];
    }
}
