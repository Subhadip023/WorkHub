<?php

namespace App\Http\Middleware;

use App\Models\ExternalTaskApi;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyExternalApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Header only: keys in query strings / bodies leak into logs and proxies.
        $apiKey = $request->header('X-Api-Key');

        if ($apiKey) {
            $externalApiConfig = ExternalTaskApi::where('api_key', $apiKey)
                ->where('is_active', true)
                ->first();

            if (! $externalApiConfig) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or inactive API key provided.',
                ], 403);
            }

            $signature = $request->header('X-Api-Signature') ?? $request->header('X-Signature');
            if (! $signature) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required HMAC signature header (X-Api-Signature).',
                ], 403);
            }

            $rawContent = $request->getContent();
            $expectedSignature = hash_hmac('sha256', $rawContent, $externalApiConfig->api_secret);

            if (! hash_equals($expectedSignature, $signature)) {
                // Support form submissions / multipart uploads
                $inputs = $request->except(['image', 'images', 'api_key', '_token']);
                $fallbackSignature = hash_hmac('sha256', json_encode($inputs), $externalApiConfig->api_secret);

                // Body fields only (query-string filters on GET are not part of the signed payload).
                $bodyFields = Arr::except($request->request->all(), ['image', 'images', 'api_key', '_token']);

                // The empty-payload signature is a constant per key, so it is only
                // acceptable when the request genuinely carries no signed fields
                // (e.g. GET listing or a file-only upload). Otherwise anyone who saw
                // one such signature could forge arbitrary task payloads.
                $emptySignatureValid = $bodyFields === []
                    && hash_equals(hash_hmac('sha256', '', $externalApiConfig->api_secret), $signature);

                if (! hash_equals($fallbackSignature, $signature) && ! $emptySignatureValid) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid HMAC signature.',
                    ], 403);
                }
            }

            // Bind resolved API config to request attributes
            $request->attributes->set('externalApiConfig', $externalApiConfig);
        } else {
            if (! auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 403);
            }
        }

        return $next($request);
    }
}
