<?php

namespace App\Http\Requests\Api;

use App\Enums\DeliveryMode;
use App\Enums\OrderSource;
use App\Enums\PaymentStatus;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

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
            'order_number' => ['required', Rule::unique('orders', 'order_number')],
            'client_phone' => ['required'],
            'client_name' => ['required', 'max:255'],
            'country_id' => ['required', Rule::exists('countries', 'id')],
            'address' => ['required', 'min:3', 'max:255'],
            'latitude' => ['required', 'min:3', 'max:255'],
            'longitude' => ['required', 'min:3', 'max:255'],
            'entrance' => ['nullable', 'max:255'],
            'floor' => ['nullable', 'max:255'],
            'flat' => ['nullable', 'max:255'],
            'order_price' => ['required', 'numeric', 'between:0,99999.99'],
            'payment_cash' => ['required', 'numeric', 'between:0,99999.99'],
            'payment_bonuses' => ['required', 'numeric', 'between:0,99999.99'],
            'payment_status' => ['required', new Enum(PaymentStatus::class)],
            'delivery_mode' => ['required', new Enum(DeliveryMode::class)],
            'delivery_date' => Rule::requiredIf(function () {
                return $this->input('delivery_mode') == DeliveryMode::OnSpecifiedDate->value;
            }),
            'source' => [new Enum(OrderSource::class)],
            'comment_for_driver' => ['nullable', 'max:255'],
            'items' => ['required', 'json'],
        ];
    }

    /**
     * @param Validator $validator
     * @return void
     */
    public function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'data' => $validator->errors()
        ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY));
    }
}
