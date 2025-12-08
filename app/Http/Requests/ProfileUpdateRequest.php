<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'max:30',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('users', 'username')->ignore($this->user()->id),
            ],
            'first_name' => ['nullable', 'string', 'max:50'],
            'last_name' => ['nullable', 'string', 'max:50'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:100',
                Rule::unique('users', 'email')->ignore($this->user()->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'country' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            // profile_picture removed - handled by separate avatarUpdate route
        ];
    }

    /**
     * Get custom error messages for validator.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.required' => 'Lietotājvārds ir obligāts / Username is required',
            'username.regex' => 'Lietotājvārds var saturēt tikai burtus, ciparus, - un _ / Username can only contain letters, numbers, - and _',
            'username.unique' => 'Šis lietotājvārds jau tiek izmantots / This username is already taken',
            'email.required' => 'E-pasts ir obligāts / Email is required',
            'email.email' => 'Nederīga e-pasta adrese / Invalid email address',
            'email.unique' => 'Šis e-pasts jau tiek izmantots / This email is already taken',
            'phone.max' => 'Tālrunis nedrīkst pārsniegt 20 rakstzīmes / Phone must not exceed 20 characters',
            'birth_date.before' => 'Dzimšanas datumam jābūt pagātnē / Birth date must be in the past',
            'profile_picture.image' => 'Failam jābūt attēlam / File must be an image',
            'profile_picture.max' => 'Attēls nedrīkst pārsniegt 2MB / Image must not exceed 2MB',
        ];
    }
}
