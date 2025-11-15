<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Translation;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function index()
    {
        $languages = Language::getActive();

        return response()->json($languages);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:languages,code',
            'name' => 'required|string|max:255',
            'native_name' => 'nullable|string|max:255',
            'flag' => 'nullable|string',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        // If setting as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            Language::where('is_default', true)->update(['is_default' => false]);
        }

        $language = Language::create($validated);

        return response()->json($language, 201);
    }

    public function update(Request $request, Language $language)
    {
        $validated = $request->validate([
            'code' => 'sometimes|required|string|max:10|unique:languages,code,' . $language->id,
            'name' => 'sometimes|required|string|max:255',
            'native_name' => 'nullable|string|max:255',
            'flag' => 'nullable|string',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        // If setting as default, unset other defaults
        if (isset($validated['is_default']) && $validated['is_default']) {
            Language::where('id', '!=', $language->id)->where('is_default', true)->update(['is_default' => false]);
        }

        $language->update($validated);

        return response()->json($language);
    }

    public function destroy(Language $language)
    {
        if ($language->is_default) {
            return response()->json(['message' => 'Cannot delete default language'], 422);
        }

        $language->delete();

        return response()->json(['message' => 'Language deleted successfully']);
    }

    public function getTranslations(Request $request, $type, $id)
    {
        $languageCode = $request->input('language');
        $language = $languageCode ? Language::where('code', $languageCode)->first() : Language::getDefault();

        if (!$language) {
            return response()->json(['message' => 'Language not found'], 404);
        }

        $translations = Translation::where('translatable_type', $type)
            ->where('translatable_id', $id)
            ->where('language_id', $language->id)
            ->get()
            ->mapWithKeys(function ($translation) {
                return [$translation->field => $translation->value];
            });

        return response()->json($translations);
    }

    public function setTranslation(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'id' => 'required|integer',
            'field' => 'required|string',
            'value' => 'required|string',
            'language' => 'required|string',
        ]);

        $model = $validated['type']::findOrFail($validated['id']);
        Translation::setTranslation($model, $validated['field'], $validated['value'], $validated['language']);

        return response()->json(['message' => 'Translation saved successfully']);
    }
}
