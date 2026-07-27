<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectCredentialsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required'],
            'name' => ['required', 'string', 'max:255'],
            'host_or_identifier' => ['nullable', 'string', 'max:255'],
            'password_or_secret' => ['required', 'string'],
        ];
    }
}
