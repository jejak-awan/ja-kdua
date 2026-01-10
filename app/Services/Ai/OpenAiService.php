<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiService implements AiProviderInterface
{
    protected $apiKey;
    protected $baseUrl = 'https://api.openai.com/v1';

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? \App\Models\Setting::get('openai_api_key');
    }

    public function getName(): string
    {
        return 'OpenAI';
    }

    public function generateText(string $prompt, string $context = '', string $model = ''): string
    {
        if (empty($this->apiKey)) {
            throw new \Exception('OpenAI API Key is not configured.');
        }

        $model = $model ?: (\App\Models\Setting::get('openai_model') ?: 'gpt-4o-mini');

        try {
            $messages = [
                ['role' => 'system', 'content' => 'You are a helpful content editor assistant.'],
                ['role' => 'user', 'content' => $context ? "Context:\n$context\n\nInstruction: $prompt" : $prompt]
            ];

            $response = Http::withToken($this->apiKey)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => 0.7,
                ]);

            if ($response->failed()) {
                $this->handleError($response);
            }

            $data = $response->json();

            return $data['choices'][0]['message']['content'] ?? '';

        } catch (\Exception $e) {
            Log::error('OpenAI Generation Exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getModels(): array
    {
        if (empty($this->apiKey)) {
            return [];
        }

        try {
            $response = Http::withToken($this->apiKey)->get("{$this->baseUrl}/models");

            if ($response->failed()) {
                return [];
            }

            $data = $response->json();
            $models = [];

            if (isset($data['data'])) {
                foreach ($data['data'] as $m) {
                    // Filter for chat models usually starting with gpt-
                    if (str_contains($m['id'], 'gpt')) {
                        $models[] = [
                            'id' => $m['id'],
                            'name' => $m['id']
                        ];
                    }
                }
            }
            
            // Sort models alphabetically
            usort($models, fn($a, $b) => strcmp($a['id'], $b['id']));

            return $models;

        } catch (\Exception $e) {
            Log::error('OpenAI GetModels Exception', ['message' => $e->getMessage()]);
            return [];
        }
    }

    public function testConnection(): bool
    {
        if (empty($this->apiKey)) {
            throw new \Exception('API Key is missing.');
        }

        $response = Http::withToken($this->apiKey)->get("{$this->baseUrl}/models");

        if ($response->failed()) {
            $this->handleError($response);
        }

        return true;
    }

    protected function handleError($response)
    {
        $errorMsg = $response->json('error.message', 'Unknown error');
        
        if ($response->status() === 401) {
            throw new \Exception('Invalid OpenAI API Key.');
        }
        
        if ($response->status() === 429) {
             throw new \Exception('OpenAI Rate Limit / Quota Exceeded.');
        }

        throw new \Exception('OpenAI API Error: ' . $errorMsg);
    }
}
