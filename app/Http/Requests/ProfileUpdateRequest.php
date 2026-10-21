<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'bio' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'github_url' => [
                'nullable',
                'string',
                'max:2048',
                function ($attribute, $value, $fail): void {
                    $this->validateSafeHttpsUrl($attribute, $value, $fail);
                },
            ],
            'portfolio_url' => [
                'nullable',
                'string',
                'max:2048',
                function ($attribute, $value, $fail): void {
                    $this->validateSafeHttpsUrl($attribute, $value, $fail);
                },
            ],
            'notify_replies' => ['nullable', 'boolean'],
            'notify_courses' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Validate an HTTPS URL without user credentials or control characters.
     */
    private function validateSafeHttpsUrl(string $attribute, mixed $value, \Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        if (mb_strlen($value) > 2048) {
            $fail('The '.$attribute.' must not exceed 2048 characters.');

            return;
        }

        if (preg_match('/[\x00-\x20\x7F]|%(?:0[0-9a-f]|1[0-9a-f]|7f)/i', $value)) {
            $fail('The '.$attribute.' contains invalid control characters.');

            return;
        }

        if (! filter_var($value, FILTER_VALIDATE_URL)) {
            $fail('The '.$attribute.' must be a valid URL.');

            return;
        }

        $parts = parse_url($value);
        if (! is_array($parts) || strtolower($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) {
            $fail('The '.$attribute.' must use the HTTPS scheme.');

            return;
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            $fail('The '.$attribute.' must not contain credentials.');

            return;
        }
    }
}
