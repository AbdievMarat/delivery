<?php

namespace App\Http\Requests\Admin;

use App\Enums\ShopStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateShopRequest extends FormRequest
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
            'name' => ['required', 'min:3', 'max:255'],
            'country_id' => ['required', Rule::exists('countries', 'id')],
            'mobile_backend_id' => ['required', Rule::unique('shops', 'mobile_backend_id')->ignore($this->route('shop')->id)],
            'contact_phone' => [
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
            'work_time_from' => ['required', 'date_format:H:i'],
            'work_time_to' => ['required', 'date_format:H:i'],
            'address' => ['required'],
            'latitude' => ['required'],
            'longitude' => ['required'],
            'status' => [new Enum(ShopStatus::class)],
        ];
    }
}
