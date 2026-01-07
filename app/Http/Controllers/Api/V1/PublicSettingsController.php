<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;

/**
 * Controller for public settings (no auth required)
 * Only exposes non-sensitive settings that the frontend needs before login
 */
class PublicSettingsController extends BaseApiController
{
    /**
     * Get public settings for the frontend
     */
    public function index(): JsonResponse
    {
        return $this->success([
            'enable_registration' => (bool) Setting::get('enable_registration', true),
            'require_email_verification' => (bool) Setting::get('require_email_verification', true),
            'site_name' => Setting::get('site_name', 'JA-CMS'),
            'site_description' => Setting::get('site_description', ''),
            'site_url' => Setting::get('site_url', config('app.url')),
            'admin_email' => Setting::get('admin_email', ''),
            'site_version' => config('app.version'),
            'site_logo' => Setting::get('site_logo', ''),
            'site_favicon' => Setting::get('site_favicon', '/favicon.svg'),
        ], 'Public settings retrieved successfully');
    }
}
