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
                'regex:/^[a-zA-Z0-9_ .-]+$/',
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
        $locale = auth()->user()?->locale ?? app()->getLocale();

        $msgs = [
            'lv' => [
                'username.required' => 'Lietotājvārds ir obligāts.',
                'username.regex'    => 'Lietotājvārds var saturēt tikai burtus, ciparus, atstarpes, - un _.',
                'username.unique'   => 'Šis lietotājvārds jau tiek izmantots.',
                'email.required'    => 'E-pasts ir obligāts.',
                'email.email'       => 'Nederīga e-pasta adrese.',
                'email.unique'      => 'Šis e-pasts jau tiek izmantots.',
                'phone.max'         => 'Tālrunis nedrīkst pārsniegt 20 rakstzīmes.',
                'birth_date.before' => 'Dzimšanas datumam jābūt pagātnē.',
            ],
            'en' => [
                'username.required' => 'Username is required.',
                'username.regex'    => 'Username can only contain letters, numbers, spaces, - and _.',
                'username.unique'   => 'This username is already taken.',
                'email.required'    => 'Email is required.',
                'email.email'       => 'Invalid email address.',
                'email.unique'      => 'This email is already taken.',
                'phone.max'         => 'Phone must not exceed 20 characters.',
                'birth_date.before' => 'Birth date must be in the past.',
            ],
        ];

        return $msgs[$locale] ?? $msgs['lv'];
    }
}
