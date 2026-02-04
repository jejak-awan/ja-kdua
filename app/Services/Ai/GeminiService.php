<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService implements AiProviderInterface
{
    protected $apiKey;

    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct(?string $apiKey = null)
    {
        // Allow passing key explicitly (for testing connection), otherwise use settings
        $this->apiKey = $apiKey ?? \App\Models\Setting::get('gemini_api_key') ?? config('services.gemini.api_key');
    }

    public function getName(): string
    {
        return 'Google Gemini';
    }

    public function generateText(string $prompt, string $context = '', string $model = ''): string
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Gemini API Key is not configured.');
        }

        // Use provided model or default to gemini-2.0-flash
        $model = $model ?: (\App\Models\Setting::get('gemini_model') ?: 'gemini-2.0-flash');

        // Ensure model name format is correct for API URL
        if (! str_starts_with($model, 'models/')) {
            $model = 'models/'.$model; // e.g. models/gemini-2.0-flash
        }

        // Strip 'models/' for the base URL construction if it was already there because the endpoint structure differs slightly based on how we call it
        // Actually, for generateContent, the URL is: models/{model}:generateContent
        // So let's normalize.
        $model = str_replace('models/', '', $model);

        try {
            $fullPrompt = $prompt.":\n\n".$context;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/models/{$model}:generateContent?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $fullPrompt],
                        ],
                    ],
                ],
            ]);

            if ($response->failed()) {
                $this->handleError($response);
            }

            $data = $response->json();

            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return $data['candidates'][0]['content']['parts'][0]['text'];
            }

            throw new \Exception('Unexpected response format from Gemini.');
        } catch (\Exception $e) {
            Log::error('Gemini Generation Exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getModels(): array
    {
        if (empty($this->apiKey)) {
            return [];
        }

        try {
            $response = Http::get("{$this->baseUrl}/models?key={$this->apiKey}");

            if ($response->failed()) {
                return [];
            }

            $data = $response->json();
            $models = [];

            if (isset($data['models'])) {
                foreach ($data['models'] as $m) {
                    if (in_array('generateContent', $m['supportedGenerationMethods'] ?? [])) {
                        $models[] = [
                            'id' => str_replace('models/', '', $m['name']),
                            'name' => $m['displayName'] ?? $m['name'],
                        ];
                    }
                }
            }

            return $models;
        } catch (\Exception $e) {
            Log::error('Gemini GetModels Exception', ['message' => $e->getMessage()]);

            return [];
        }
    }

    public function testConnection(): bool
    {
        if (empty($this->apiKey)) {
            throw new \Exception('API Key is missing.');
        }

        // Lightweight check: List models. If it works, key is valid.
        $response = Http::get("{$this->baseUrl}/models?key={$this->apiKey}");

        if ($response->failed()) {
            $this->handleError($response);
        }

        return true;
    }

    protected function handleError($response)
    {
        Log::error('Gemini API Error', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        $errorMsg = $response->json('error.message', 'Unknown error');

        if ($response->status() === 429 || str_contains(strtolower($errorMsg), 'quota')) {
            throw new \Exception('Gemini Quota Exceeded. Please check billing.');
        }

        throw new \Exception('Gemini API Error: '.$errorMsg);
    }
}
