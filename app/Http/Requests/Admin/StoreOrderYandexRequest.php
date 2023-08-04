<?php

namespace App\Http\Requests\Admin;

use App\Models\Country;
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
            'client_phone' => [
                'required',
                function ($attribute, $value, $fail) {
                    $countryId = $this->input('country_id');

                    if ($countryId == Country::KYRGYZSTAN_COUNTRY_ID && !preg_match('/^996\d{9}$/', $value)) {
                        $fail('Телефон клиента должен быть в формате 996555123456.');
                    } elseif ($countryId == Country::KAZAKHSTAN_COUNTRY_ID && !preg_match('/^77\d{9}$/', $value)) {
                        $fail('Телефон клиента должен быть в формате 77123456789.');
                    } elseif ($countryId == Country::RUSSIA_COUNTRY_ID && !preg_match('/^79\d{9}$/', $value)) {
                        $fail('Телефон клиента должен быть в формате 79123456789.');
                    }
                },
            ],
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
