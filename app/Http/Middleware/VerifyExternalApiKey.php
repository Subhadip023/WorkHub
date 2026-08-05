<?php

namespace App\Http\Middleware;

use App\Models\ExternalTaskApi;
use Closure;
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
        $apiKey = $request->header('X-Api-Key') ?? $request->input('api_key');

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
                $jsonInputs = json_encode($inputs);
                $fallbackSignature = hash_hmac('sha256', $jsonInputs, $externalApiConfig->api_secret);
                $emptySignature = hash_hmac('sha256', '', $externalApiConfig->api_secret);

                if (! hash_equals($fallbackSignature, $signature) && ! hash_equals($emptySignature, $signature)) {
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
