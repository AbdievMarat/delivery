<?php

namespace App\Http\Requests\Admin;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role_id' => ['required', Rule::exists('roles', 'id')],
            'attached_shops' => Rule::requiredIf(function () {
                $managerRoleId = Role::query()->where('name', '=', 'manager')->pluck('id')->first();

                return $this->input('role_id') == $managerRoleId;
            }),
            'available_countries' => Rule::requiredIf(function () {
                $accountantRoleId = Role::query()->where('name', '=', 'accountant')->pluck('id')->first();

                return $this->input('role_id') == $accountantRoleId;
            }),
            'active' => ['boolean'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'active' => filter_var($this->input('active'), FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
