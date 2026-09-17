<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $role = $this->route('role');

        return [
            'display_name' => ['required', 'string', 'max:255'],
            'name' => [
                'required',
                'string',
                Rule::unique('roles', 'name')->ignore($role->id),
                'regex:/^[A-Z0-9_]+$/',
            ],
            'status' => ['required', 'in:active,inactive'],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'The Role Code must be uppercase with no spaces (letters, numbers, underscores only).',
        ];
    }
}
