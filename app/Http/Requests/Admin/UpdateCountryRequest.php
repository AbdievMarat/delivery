<?php

namespace App\Http\Requests\Admin;

use App\Enums\CountryStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateCountryRequest extends FormRequest
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
            'currency_name' => ['required', 'min:3', 'max:255'],
            'currency_iso' => ['required'],
            'organization_name' => ['required', 'min:3', 'max:255', Rule::unique('countries', 'organization_name')->ignore($this->route('country')->id)],
            'contact_phone' => ['required', 'min:3', 'max:255'],
            'latitude' => ['nullable'],
            'longitude' => ['nullable'],
            'yandex_tariffs' => [
                'array',
                Rule::requiredIf(function () {
                    return in_array($this->route('country')->id, [2, 3]);
                }),
            ],
            'status' => [new Enum(CountryStatus::class)],
        ];
    }
}
