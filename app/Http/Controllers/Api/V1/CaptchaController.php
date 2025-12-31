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
        $service = new CaptchaService();
        $captcha = $service->generate();

        return $this->success($captcha, 'Captcha generated successfully');
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
            'method' => CaptchaService::getMethod(),
        ], 'Captcha settings retrieved');
    }
}
