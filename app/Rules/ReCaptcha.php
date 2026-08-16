<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Translation\PotentiallyTranslatedString;

class ReCaptcha implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Bypass in automated test environment if token is not submitted
        if (app()->runningUnitTests() && empty($value)) {
            return;
        }

        // Bypass when RECAPTCHA_SKIP=true (e.g. local development)
        if (config('services.recaptcha.skip')) {
            return;
        }

        $secretKey = config('services.recaptcha.secret_key');

        if (empty($secretKey)) {
            return;
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secretKey,
            'response' => $value,
            'remoteip' => request()->ip(),
        ]);

        if (! $response->successful() || ! $response->json('success')) {
            $fail('The reCAPTCHA verification failed. Please try again.');
        }
    }
}
