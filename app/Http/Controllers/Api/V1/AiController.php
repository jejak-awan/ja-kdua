<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AiController extends Controller
{
    /**
     * Get list of available AI providers
     */
    public function getProviders()
    {
        return response()->json([
            'success' => true,
            'data' => \App\Services\Ai\AiProviderFactory::getProviders(),
        ]);
    }

    /**
     * Get available models for a provider
     */
    public function getModels(Request $request, string $provider)
    {
        try {
            // Instantiate service manually with the provided key (for setup) or null to use saved key
            $apiKey = $request->input('api_key');

            // Factory doesn't accept key in 'make', so we instantiate manually based on provider
            $service = match ($provider) {
                'openai' => new \App\Services\Ai\OpenAiService($apiKey),
                'deepseek' => new \App\Services\Ai\DeepSeekService($apiKey),
                'gemini' => new \App\Services\Ai\GeminiService($apiKey),
                default => throw new \Exception('Unknown provider'),
            };

            $models = $service->getModels();

            return response()->json([
                'success' => true,
                'data' => $models,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch models: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test connection to a provider
     */
    public function testConnection(Request $request)
    {
        $request->validate([
            'provider' => 'required|string',
            'api_key' => 'required|string',
        ]);

        try {
            $provider = $request->input('provider');
            $apiKey = $request->input('api_key');

            $service = match ($provider) {
                'openai' => new \App\Services\Ai\OpenAiService($apiKey),
                'deepseek' => new \App\Services\Ai\DeepSeekService($apiKey),
                'gemini' => new \App\Services\Ai\GeminiService($apiKey),
                default => throw new \Exception('Unknown provider'),
            };

            $service->testConnection();

            return response()->json([
                'success' => true,
                'message' => 'Connection successful!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Generate content using AI
     */
    public function generate(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'context' => 'nullable|string|max:5000',
            'provider' => 'nullable|string',
            'model' => 'nullable|string',
        ]);

        try {
            $providerName = $request->input('provider');
            $model = $request->input('model');

            // Use Factory to get the active service
            $service = \App\Services\Ai\AiProviderFactory::make($providerName);

            $result = $service->generateText(
                $request->input('prompt'),
                (string) $request->input('context', ''),
                (string) $model
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'content' => $result,
                    'provider' => $service->getName(),
                ],
            ]);

        } catch (\Exception $e) {
            $message = $e->getMessage();
            $status = 500;

            if (str_contains(strtolower($message), 'quota') || str_contains(strtolower($message), 'insufficient balance')) {
                $status = 429;
                $message = 'AI Quota/Balance Exceeded. Please check your billing.';
            } elseif (str_contains(strtolower($message), 'not found') || str_contains(strtolower($message), 'supported')) {
                $status = 404;
            } elseif (str_contains(strtolower($message), 'api key') || str_contains(strtolower($message), 'unauthorized')) {
                $status = 401;
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'original_error' => $e->getMessage(),
            ], $status);
        }
    }
}
