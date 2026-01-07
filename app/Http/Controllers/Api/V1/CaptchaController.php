<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\CaptchaService;
use Illuminate\Http\JsonResponse;

class CaptchaController extends BaseApiController
{
    /**
     * Generate a new captcha challenge.
     */
    public function generate(): JsonResponse
    {
        $service = new CaptchaService;
        $captcha = $service->generate();

        return $this->success($captcha, 'Captcha generated successfully');
    }

    /**
     * Verify the captcha token and answer.
     */
    public function verify(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'answer' => 'required|string',
        ]);

        $service = new CaptchaService;
        $valid = $service->verify($request->token, $request->answer, false); // Don't consume on dry-run verify

        if (! $valid) {
            return $this->error('Invalid captcha', 422);
        }

        return $this->success(null, 'Captcha verified');
    }

    /**
     * Get captcha settings for frontend.
     */
    public function settings(): JsonResponse
    {
        return $this->success([
            'enabled' => CaptchaService::isEnabled('login'),
            'enabled_login' => CaptchaService::isEnabled('login'),
            'enabled_register' => CaptchaService::isEnabled('register'),
            'enabled_comment' => CaptchaService::isEnabled('comment'),
            'method' => CaptchaService::getMethod(),
        ], 'Captcha settings retrieved');
    }
}
