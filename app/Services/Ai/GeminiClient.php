<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Minimal Google Gemini client (REST generateContent) built on Laravel's Http
 * client, so no extra Composer package is needed.
 *
 * The API key is sent in the x-goog-api-key header, never in the URL, so it
 * does not end up in access logs.
 */
class GeminiClient
{
    public function isConfigured(): bool
    {
        return filled(config('services.gemini.key'));
    }

    /**
     * Ask Gemini for JSON matching $schema and return it decoded.
     *
     * @param  array<string, mixed>  $schema  OpenAPI-style schema for responseSchema
     * @return array<string, mixed>
     *
     * @throws AiUnavailableException
     */
    public function generateJson(string $systemInstruction, string $prompt, array $schema, int $maxOutputTokens = 1024): array
    {
        if (! $this->isConfigured()) {
            throw new AiUnavailableException('AI drafting is not configured on this server.');
        }

        $model = config('services.gemini.model');
        $url = rtrim(config('services.gemini.base_url'), '/')."/models/{$model}:generateContent";

        try {
            $response = Http::withHeaders(['x-goog-api-key' => config('services.gemini.key')])
                ->acceptJson()
                ->timeout(config('services.gemini.timeout', 20))
                ->post($url, [
                    'systemInstruction' => ['parts' => [['text' => $systemInstruction]]],
                    'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'responseSchema' => $schema,
                        'temperature' => 0.2,
                        'maxOutputTokens' => $maxOutputTokens,
                    ],
                ]);
        } catch (ConnectionException $e) {
            report($e);
            throw new AiUnavailableException('The AI service could not be reached. Please try again.');
        }

        if ($response->status() === 429) {
            throw new AiUnavailableException('The AI service is busy right now. Please try again in a minute.');
        }

        if ($response->failed()) {
            report(new \RuntimeException('Gemini request failed with HTTP '.$response->status().': '.mb_substr($response->body(), 0, 500)));
            throw new AiUnavailableException('The AI service returned an error. Please try again.');
        }

        $data = $response->json();

        if (! empty($data['promptFeedback']['blockReason'])) {
            throw new AiUnavailableException('The AI declined this request. Try rephrasing it.');
        }

        $candidate = $data['candidates'][0] ?? null;
        $finishReason = $candidate['finishReason'] ?? null;

        if ($finishReason !== null && $finishReason !== 'STOP') {
            throw new AiUnavailableException($finishReason === 'MAX_TOKENS'
                ? 'The request was too long for the AI to finish. Try a shorter description.'
                : 'The AI could not complete this request. Try rephrasing it.');
        }

        $text = collect($candidate['content']['parts'] ?? [])->pluck('text')->filter()->implode('');
        $decoded = json_decode($text, true);

        if (! is_array($decoded)) {
            throw new AiUnavailableException('The AI returned an unexpected answer. Please try again.');
        }

        return $decoded;
    }
}
