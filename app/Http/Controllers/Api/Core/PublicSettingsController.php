<?php

namespace App\Http\Controllers\Api\Core;

use App\Models\Core\Setting;
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

            // Contact Info
            'contact_email' => Setting::get('contact_email', 'hello@janari.com'),
            'contact_phone' => Setting::get('contact_phone', ''),
            'contact_address' => Setting::get('contact_address', ''),

            // Social Links
            'social_twitter' => Setting::get('social_twitter', ''),
            'social_github' => Setting::get('social_github', ''),
            'social_linkedin' => Setting::get('social_linkedin', ''),
            'social_instagram' => Setting::get('social_instagram', ''),
        ], 'Public settings retrieved successfully');
    }
}
