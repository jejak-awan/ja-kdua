<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = Setting::query();

        if ($request->has('group')) {
            $query->where('group', $request->group);
        }

        if ($request->has('public_only')) {
            $query->where('is_public', true);
        }

        $settings = $query->orderBy('group')->orderBy('key')->get();

        return $this->success($settings, 'Settings retrieved successfully');
    }

    public function getGroup($group)
    {
        $settings = Setting::getGroup($group);

        return $this->success($settings, 'Settings retrieved successfully');
    }

    public function show($key)
    {
        $setting = Setting::where('key', $key)->firstOrFail();

        return $this->success($setting, 'Setting retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|unique:settings,key',
            'value' => 'nullable',
            'type' => 'required|in:string,integer,boolean,json,text',
            'group' => 'required|string',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $setting = Setting::create($validated);

        return $this->success($setting, 'Setting created successfully', 201);
    }

    public function update(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'value' => 'nullable',
            'type' => 'sometimes|in:string,integer,boolean,json,text',
            'group' => 'sometimes|string',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $setting->update($validated);

        return $this->success($setting, 'Setting updated successfully');
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
            'settings.*.type' => 'sometimes|in:string,integer,boolean,json,text',
            'settings.*.group' => 'sometimes|string',
        ]);

        foreach ($validated['settings'] as $settingData) {
            Setting::set(
                $settingData['key'],
                $settingData['value'] ?? null,
                $settingData['type'] ?? 'string',
                $settingData['group'] ?? 'general'
            );
        }

        return $this->success(null, 'Settings updated successfully');
    }

    public function destroy(Setting $setting)
    {
        $setting->delete();

        return $this->success(null, 'Setting deleted successfully');
    }

    /**
     * Test storage connection dynamically
     */
    public function testStorage(Request $request)
    {
        $driver = $request->input('driver');
        $config = $request->input('config'); // Array of settings formData

        if (!$driver || !$config) {
            return $this->error('Driver and configuration are required', 400);
        }

        // Map config keys to Laravel filesystem config
        $diskConfig = ['driver' => $driver];

        if ($driver === 's3') {
            $diskConfig = array_merge($diskConfig, [
                'key' => $config['aws_access_key_id'] ?? '',
                'secret' => $config['aws_secret_access_key'] ?? '',
                'region' => $config['aws_default_region'] ?? 'us-east-1',
                'bucket' => $config['aws_bucket'] ?? '',
                'endpoint' => $config['aws_endpoint'] ?? '',
                'use_path_style_endpoint' => !empty($config['aws_endpoint']),
            ]);
        } elseif ($driver === 'google') {
            $diskConfig = array_merge($diskConfig, [
                'clientId' => $config['google_client_id'] ?? '',
                'clientSecret' => $config['google_client_secret'] ?? '',
                'refreshToken' => $config['google_refresh_token'] ?? '',
                'folderId' => $config['google_folder_id'] ?? '/',
            ]);
        } elseif ($driver === 'ftp') {
            $diskConfig = array_merge($diskConfig, [
                'host' => $config['ftp_host'] ?? '',
                'username' => $config['ftp_username'] ?? '',
                'password' => $config['ftp_password'] ?? '',
                'port' => $config['ftp_port'] ?? 21,
                'root' => $config['ftp_root'] ?? '',
                'ssl' => $config['ftp_ssl'] ?? false,
            ]);
        } elseif ($driver === 'dropbox') {
             $diskConfig = array_merge($diskConfig, [
                'authorizationToken' => $config['dropbox_authorization_token'] ?? '',
            ]);
        }

        // Set dynamic config
        \Illuminate\Support\Facades\Config::set("filesystems.disks.test_{$driver}", $diskConfig);

        try {
            // Attempt to list contents
            \Illuminate\Support\Facades\Storage::disk("test_{$driver}")->listContents('/');
            return $this->success(null, 'Connection successful! Storage is accessible.');
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            
            // Helpful messages for missing drivers
            if (str_contains($message, 'Driver [google] is not supported')) {
                $message = 'Google Drive adapter is missing. Please run: composer require spatie/flysystem-google-drive';
            }
            if (str_contains($message, 'Driver [s3] is not supported')) {
                 $message = 'AWS S3 adapter is missing. Please run: composer require league/flysystem-aws-s3-v3';
            }
             if (str_contains($message, 'Driver [dropbox] is not supported')) {
                 $message = 'Dropbox adapter is missing. Please run: composer require spatie/flysystem-dropbox';
            }
             if (str_contains($message, 'Driver [ftp] is not supported')) {
                 $message = 'FTP adapter is missing. Please run: composer require league/flysystem-ftp';
            }

            return $this->error('Connection failed: ' . $message, 500);
        }
    }
}
