<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Named error bag so validation errors from this form do not leak
     * into the Change Password form on the same profile page.
     */
    protected $errorBag = 'updateProfile';

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
     * Email is intentionally NOT included — it is immutable for the profile.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}