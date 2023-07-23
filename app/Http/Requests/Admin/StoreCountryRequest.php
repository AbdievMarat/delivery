<?php

namespace App\Http\Requests\Admin;

use App\Enums\CountryStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreCountryRequest extends FormRequest
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
            'organization_name' => ['required', 'min:3', 'max:255'],
            'currency_name' => ['required', 'min:3', 'max:255'],
            'currency_iso' => ['required'],
            'contact_phone' => ['required', 'min:3', 'max:255'],
            'latitude' => ['nullable'],
            'longitude' => ['nullable'],
            'yandex_tariffs' => ['nullable', 'array'],
            'status' => [new Enum(CountryStatus::class)],
        ];
    }
}
