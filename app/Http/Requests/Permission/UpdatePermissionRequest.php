<?php

namespace App\Http\Requests\Permission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $permission = $this->route('permission');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                Rule::unique('permissions', 'name')->ignore($permission->id),
                'regex:/^[a-z0-9_]+(\.[a-z0-9_]+){2,}$/',
            ],
            'status' => ['required', 'in:active,inactive'],
            'description' => ['nullable', 'string'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.regex' => 'The Permission Code must follow the format {app}.{resource}.{action} (e.g. kejarkarir.users.create).',
        ];
    }
}
