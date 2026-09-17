<?php

namespace App\Http\Requests\Permission;

use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'unique:permissions,name',
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
