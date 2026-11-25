<?php

namespace App\Http\Middleware;

use App\Models\ExternalTaskApi;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates external integrations by API key + HMAC signature.
 *
 * Signing scheme v2 (recommended):
 *   X-Api-Timestamp: <unix seconds>
 *   X-Api-Signature: hex(HMAC_SHA256(secret, "{timestamp}.{payload}"))
 * Requests older/newer than MAX_CLOCK_SKEW are rejected and each signature is
 * single-use (replay protection).
 *
 * Legacy scheme v1 (no timestamp, signature over payload only) is accepted while
 * services.external_api.allow_legacy_signatures is true, with a Deprecation header.
 *
 * "payload" is the raw request body, or for form/multipart requests the JSON of the
 * non-file fields, or "" when there are no body fields (GET / file-only upload).
 */
class VerifyExternalApiKey
{
    /** Allowed clock difference between client and server, in seconds. */
    private const MAX_CLOCK_SKEW = 300;

    /** Fields excluded from the form-payload signature. */
    private const UNSIGNED_FIELDS = ['image', 'images', 'api_key', '_token'];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Header only: keys in query strings / bodies leak into logs and proxies.
        $apiKey = $request->header('X-Api-Key');

        if (! $apiKey) {
            if (! auth()->check()) {
                return $this->deny('Unauthenticated.');
            }

            return $next($request);
        }

        $externalApiConfig = ExternalTaskApi::where('api_key', $apiKey)
            ->where('is_active', true)
            ->first();

        if (! $externalApiConfig) {
            return $this->deny('Invalid or inactive API key provided.');
        }

        $signature = $request->header('X-Api-Signature') ?? $request->header('X-Signature');
        if (! $signature) {
            return $this->deny('Missing required HMAC signature header (X-Api-Signature).');
        }

        $secret = $externalApiConfig->api_secret;
        $timestamp = $request->header('X-Api-Timestamp');
        $legacy = false;

        if ($timestamp !== null) {
            if (! ctype_digit((string) $timestamp) || abs(time() - (int) $timestamp) > self::MAX_CLOCK_SKEW) {
                return $this->deny('Request timestamp is missing, malformed, or outside the allowed window.');
            }

            if (! $this->matchesSignature($request, $secret, $signature, $timestamp.'.')) {
                return $this->deny('Invalid HMAC signature.');
            }

            // Single-use signatures: a captured request cannot be replayed.
            $replayKey = 'api_sig:'.$externalApiConfig->id.':'.hash('sha256', $signature);
            if (! Cache::add($replayKey, 1, self::MAX_CLOCK_SKEW * 2)) {
                return $this->deny('Replayed request rejected.');
            }
        } else {
            if (! config('services.external_api.allow_legacy_signatures', true)) {
                return $this->deny('Missing X-Api-Timestamp header. Legacy signatures are disabled; sign "{timestamp}.{payload}".');
            }

            if (! $this->matchesSignature($request, $secret, $signature, '')) {
                return $this->deny('Invalid HMAC signature.');
            }

            $legacy = true;
        }

        // Bind resolved API config to request attributes
        $request->attributes->set('externalApiConfig', $externalApiConfig);

        $response = $next($request);

        if ($legacy) {
            $response->headers->set('Deprecation', 'true');
            $response->headers->set('Warning', '299 - "Unsigned timestamp: send X-Api-Timestamp and sign {timestamp}.{payload}"');
        }

        return $response;
    }

    /**
     * Check the signature against the supported payload encodings, each prefixed
     * with $prefix ("" for legacy, "{timestamp}." for v2).
     */
    private function matchesSignature(Request $request, string $secret, string $signature, string $prefix): bool
    {
        // 1. Raw body (JSON / raw requests).
        if (hash_equals(hash_hmac('sha256', $prefix.$request->getContent(), $secret), $signature)) {
            return true;
        }

        // 2. Form / multipart: JSON of the non-file fields.
        $inputs = $request->except(self::UNSIGNED_FIELDS);
        if (hash_equals(hash_hmac('sha256', $prefix.json_encode($inputs), $secret), $signature)) {
            return true;
        }

        // 3. Empty payload. Without a timestamp this signature is a constant per key,
        //    so it is only acceptable when the request carries no body fields
        //    (GET listing / file-only upload); otherwise it could forge any payload.
        //    Query-string filters are not part of the signed payload.
        $bodyFields = Arr::except($request->request->all(), self::UNSIGNED_FIELDS);

        return $bodyFields === []
            && hash_equals(hash_hmac('sha256', $prefix, $secret), $signature);
    }

    private function deny(string $message): Response
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 403);
    }
}
