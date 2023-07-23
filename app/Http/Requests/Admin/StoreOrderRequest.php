<?php

namespace App\Http\Requests\Admin;

use App\Enums\DeliveryMode;
use App\Enums\OrderSource;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreOrderRequest extends FormRequest
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
            'client_phone' => [
                'required',
                function ($attribute, $value, $fail) {
                    $countryId = $this->input('country_id');

                    if ($countryId == 1 && !preg_match('/^996\d{9}$/', $value)) {
                        $fail('Телефон клиента должен быть в формате 996555123456.');
                    } elseif ($countryId == 2 && !preg_match('/^77\d{9}$/', $value)) {
                        $fail('Телефон клиента должен быть в формате 77123456789.');
                    } elseif ($countryId == 3 && !preg_match('/^79\d{9}$/', $value)) {
                        $fail('Телефон клиента должен быть в формате 79123456789.');
                    }
                },
            ],
            'client_name' => ['required', 'min:3', 'max:255'],
            'country_id' => ['required', Rule::exists('countries', 'id')],
            'address' => ['required', 'min:3', 'max:255'],
            'latitude' => ['required', 'min:3', 'max:255'],
            'longitude' => ['required', 'min:3', 'max:255'],
            'entrance' => ['nullable', 'max:255'],
            'floor' => ['nullable', 'max:255'],
            'flat' => ['nullable', 'max:255'],
            'order_price' => ['required', 'numeric', 'between:0,99999.99'],
            'delivery_mode' => [new Enum(DeliveryMode::class)],
            'delivery_date' => [
                'nullable',
                'date',
                'after:today',
                Rule::requiredIf(function () {
                    return $this->input('delivery_mode') == DeliveryMode::OnSpecifiedDate->value;
                }),
            ],
            'delivery_time' => [
                'nullable',
                'date_format:H:i',
                Rule::requiredIf(function () {
                    return $this->input('delivery_mode') == DeliveryMode::OnSpecifiedDate->value;
                }),
            ],
            'source' => [new Enum(OrderSource::class)],
            'comment_for_operator' => ['nullable', 'max:255'],
            'comment_for_manager' => ['nullable', 'max:255'],
            'comment_for_driver' => ['nullable', 'max:255'],

            'product_sku' => ['required', 'array'],
            'product_name' => ['required', 'array'],
            'quantity' => ['required', 'array'],
            'product_price' => ['required', 'array'],
            'product_sku.1' => ['required', 'min:3', 'max:255'],
            'product_name.1' => ['required', 'min:3', 'max:255'],
            'quantity.1' => ['required', 'numeric'],
            'product_price.1' => ['required'],
        ];
    }
}
