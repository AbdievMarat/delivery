<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderYandexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'order_id' => ['required', Rule::exists('orders', 'id')],
            'shop_id' => ['required', Rule::exists('shops', 'id')],
            'address' => ['required', 'min:3', 'max:255'],
            'latitude' => ['required', 'min:3', 'max:255'],
            'longitude' => ['required', 'min:3', 'max:255'],
            'entrance' => ['nullable', 'max:255'],
            'floor' => ['nullable', 'max:255'],
            'flat' => ['nullable', 'max:255'],
            'comment_for_operator' => ['nullable', 'max:255'],
            'comment_for_manager' => ['nullable', 'max:255'],
            'comment_for_driver' => ['nullable', 'max:255'],
        ];
    }
}
