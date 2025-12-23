<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Language;
use App\Services\LanguagePackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LanguageController extends BaseApiController
{
    protected LanguagePackService $languagePackService;

    public function __construct(LanguagePackService $languagePackService)
    {
        $this->languagePackService = $languagePackService;
    }

    /**
     * Get all active languages
     */
    public function index()
    {
        $languages = Language::getActive();

        // Add UI translation stats to each language
        $languages = $languages->map(function ($lang) {
            $stats = $this->languagePackService->getLocaleStats($lang->code);
            $lang->has_ui_translations = $stats['exists'] ?? false;
            $lang->translation_keys = $stats['total_keys'] ?? 0;
            return $lang;
        });

        return $this->success($languages, 'Languages retrieved successfully');
    }

    /**
     * Get a single language
     */
    public function show(Language $language)
    {
        $stats = $this->languagePackService->getLocaleStats($language->code);
        $language->has_ui_translations = $stats['exists'] ?? false;
        $language->translation_keys = $stats['total_keys'] ?? 0;

        return $this->success($language, 'Language retrieved successfully');
    }

    /**
     * Create a new language
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:10|unique:languages,code',
                'name' => 'required|string|max:255',
                'native_name' => 'nullable|string|max:255',
                'flag' => 'nullable|string',
                'is_default' => 'boolean',
                'is_active' => 'boolean',
                'sort_order' => 'integer',
                'create_from_template' => 'boolean',
                'template_locale' => 'string|max:10',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        // If setting as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            Language::where('is_default', true)->update(['is_default' => false]);
        }

        // Create language folder from template if requested
        if ($validated['create_from_template'] ?? false) {
            $templateLocale = $validated['template_locale'] ?? 'en';
            $result = $this->languagePackService->createFromTemplate($validated['code'], $templateLocale);
            
            if (!$result['success']) {
                return $this->error($result['message'], 400);
            }
        }

        $language = Language::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'native_name' => $validated['native_name'] ?? null,
            'flag' => $validated['flag'] ?? null,
            'is_default' => $validated['is_default'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return $this->success($language, 'Language created successfully', 201);
    }

    /**
     * Update a language
     */
    public function update(Request $request, Language $language)
    {
        try {
            $validated = $request->validate([
                'code' => 'sometimes|required|string|max:10|unique:languages,code,'.$language->id,
                'name' => 'sometimes|required|string|max:255',
                'native_name' => 'nullable|string|max:255',
                'flag' => 'nullable|string',
                'is_default' => 'boolean',
                'is_active' => 'boolean',
                'sort_order' => 'integer',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        // If setting as default, unset other defaults
        if (isset($validated['is_default']) && $validated['is_default']) {
            Language::where('id', '!=', $language->id)->where('is_default', true)->update(['is_default' => false]);
        }

        $language->update($validated);

        return $this->success($language, 'Language updated successfully');
    }

    /**
     * Delete a language
     */
    public function destroy(Language $language)
    {
        if ($language->is_default) {
            return $this->validationError(['language' => ['Cannot delete default language']], 'Cannot delete default language');
        }

        $language->delete();

        return $this->success(null, 'Language deleted successfully');
    }

    /**
     * Set a language as default
     */
    public function setDefault(Language $language)
    {
        Language::where('is_default', true)->update(['is_default' => false]);
        $language->update(['is_default' => true]);

        return $this->success($language, 'Default language set successfully');
    }

    /**
     * Export a language pack as ZIP
     */
    public function exportPack(Language $language)
    {
        $zipPath = $this->languagePackService->exportLanguagePack($language->code);

        if (!$zipPath || !file_exists($zipPath)) {
            return $this->error('Failed to create language pack export', 500);
        }

        $fileName = basename($zipPath);

        return response()->download($zipPath, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Import a language pack from ZIP
     */
    public function importPack(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:zip|max:10240', // Max 10MB
                'target_locale' => 'nullable|string|max:10',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $file = $request->file('file');
        $targetLocale = $request->input('target_locale');

        // Store uploaded file temporarily
        $tempPath = $file->storeAs('temp', 'import-' . now()->timestamp . '.zip');
        $fullPath = storage_path('app/' . $tempPath);

        $result = $this->languagePackService->importLanguagePack($fullPath, $targetLocale);

        // Clean up temp file
        @unlink($fullPath);

        if (!$result['success']) {
            return $this->error($result['message'], 400);
        }

        // Create or update language in DB if it doesn't exist
        $locale = $result['locale'];
        $language = Language::firstOrCreate(
            ['code' => $locale],
            ['name' => ucfirst($locale), 'is_active' => true]
        );

        return $this->success([
            'language' => $language,
            'files_imported' => $result['files'] ?? 0,
        ], $result['message']);
    }

    /**
     * Get UI translation stats for all locales
     */
    public function uiStats()
    {
        $locales = $this->languagePackService->getAvailableLocales();
        $stats = [];

        foreach ($locales as $locale) {
            $localeStats = $this->languagePackService->getLocaleStats($locale);
            $stats[$locale] = $localeStats;
        }

        return $this->success($stats, 'UI translation stats retrieved');
    }
}
