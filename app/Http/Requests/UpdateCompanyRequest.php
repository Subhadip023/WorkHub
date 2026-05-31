<?php

namespace App\Http\Requests;

use App\Models\CompanyUsers;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $company = $this->route('company');
        if (! $company) {
            return false;
        }

        return CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $this->user()->id)
            ->where('role', 1)
            ->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
