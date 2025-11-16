<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Language;
use App\Models\Translation;
use Illuminate\Http\Request;

class LanguageController extends BaseApiController
{
    public function index()
    {
        $languages = Language::getActive();

        return $this->success($languages, 'Languages retrieved successfully');
    }

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
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        // If setting as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            Language::where('is_default', true)->update(['is_default' => false]);
        }

        $language = Language::create($validated);

        return $this->success($language, 'Language created successfully', 201);
    }

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

    public function destroy(Language $language)
    {
        if ($language->is_default) {
            return $this->validationError(['language' => ['Cannot delete default language']], 'Cannot delete default language');
        }

        $language->delete();

        return $this->success(null, 'Language deleted successfully');
    }

    public function getTranslations(Request $request, $type, $id)
    {
        $languageCode = $request->input('language');
        $language = $languageCode ? Language::where('code', $languageCode)->first() : Language::getDefault();

        if (! $language) {
            return $this->notFound('Language');
        }

        $translations = Translation::where('translatable_type', $type)
            ->where('translatable_id', $id)
            ->where('language_id', $language->id)
            ->get()
            ->mapWithKeys(function ($translation) {
                return [$translation->field => $translation->value];
            });

        return $this->success($translations, 'Translations retrieved successfully');
    }

    public function setTranslation(Request $request)
    {
        try {
            $validated = $request->validate([
                'type' => 'required|string',
                'id' => 'required|integer',
                'field' => 'required|string',
                'value' => 'required|string',
                'language' => 'required|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $model = $validated['type']::findOrFail($validated['id']);
        Translation::setTranslation($model, $validated['field'], $validated['value'], $validated['language']);

        return $this->success(null, 'Translation saved successfully');
    }
}
