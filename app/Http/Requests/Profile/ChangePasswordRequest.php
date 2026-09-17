<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Named error bag so validation errors from this form do not leak
     * into the Edit Profile form on the same profile page.
     */
    protected $errorBag = 'changePassword';

    /**
     * Authorize — user must be authenticated (handled by auth middleware).
     */
    public function authorize(): bool
    {
        return true;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * Note: current_password is validated in the Repository (business logic),
     * not here, so a wrong current password throws a DomainException that the
     * controller can catch and display as a friendly error message.
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                Password::defaults()->mixedCase()->numbers()->symbols(),
                'confirmed',
            ],
        ];
    }
}