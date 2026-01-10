<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepSeekService implements AiProviderInterface
{
    protected $apiKey;
    protected $baseUrl = 'https://api.deepseek.com'; // DeepSeek Base URL

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? \App\Models\Setting::get('deepseek_api_key');
    }

    public function getName(): string
    {
        return 'DeepSeek';
    }

    public function generateText(string $prompt, string $context = '', string $model = ''): string
    {
        if (empty($this->apiKey)) {
            throw new \Exception('DeepSeek API Key is not configured.');
        }

        $model = $model ?: (\App\Models\Setting::get('deepseek_model') ?: 'deepseek-chat');

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
            Log::error('DeepSeek Generation Exception', ['message' => $e->getMessage()]);
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
                    $models[] = [
                        'id' => $m['id'],
                        'name' => $m['id']
                    ];
                }
            }
            
            return $models;

        } catch (\Exception $e) {
            Log::error('DeepSeek GetModels Exception', ['message' => $e->getMessage()]);
            return [];
        }
    }

    public function testConnection(): bool
    {
        if (empty($this->apiKey)) {
            throw new \Exception('API Key is missing.');
        }

        // DeepSeek also supports /models endpoint for check
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
            throw new \Exception('Invalid DeepSeek API Key.');
        }
        
        if ($response->status() === 402) {
             throw new \Exception('DeepSeek Validation Failed (Insufficient Balance).');
        }

        throw new \Exception('DeepSeek API Error: ' . $errorMsg);
    }
}
