<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations - Convert legacy form fields to visual builder blocks.
     */
    public function up(): void
    {
        // Get all forms that have fields but no blocks (or empty blocks)
        $forms = DB::table('forms')
            ->whereNull('blocks')
            ->orWhere('blocks', '[]')
            ->orWhere('blocks', '')
            ->get();

        foreach ($forms as $form) {
            // Get fields for this form
            $fields = DB::table('form_fields')
                ->where('form_id', $form->id)
                ->orderBy('sort_order')
                ->get();

            if ($fields->isEmpty()) {
                continue;
            }

            $blocks = $this->convertFieldsToBlocks($fields);
            
            DB::table('forms')
                ->where('id', $form->id)
                ->update(['blocks' => json_encode($blocks)]);
        }
    }

    /**
     * Convert FormField collection to BlockInstance array structure.
     */
    private function convertFieldsToBlocks($fields): array
    {
        $fieldBlocks = [];
        
        foreach ($fields as $index => $field) {
            $blockType = 'form_input';
            
            switch ($field->type) {
                case 'textarea':
                    $blockType = 'form_textarea';
                    break;
                case 'select':
                    $blockType = 'form_select';
                    break;
                case 'checkbox':
                    $blockType = 'form_checkbox';
                    break;
                case 'radio':
                    $blockType = 'form_radio';
                    break;
            }
            
            $options = [];
            if ($field->options) {
                $rawOptions = json_decode($field->options, true) ?? [];
                // Convert string[] to {value, label}[] format if needed
                foreach ($rawOptions as $opt) {
                    if (is_string($opt)) {
                        $options[] = ['value' => $opt, 'label' => $opt];
                    } else {
                        $options[] = $opt;
                    }
                }
            }
            
            $fieldBlocks[] = [
                'id' => 'migrated-' . $field->id . '-' . time() . $index,
                'type' => $blockType,
                'settings' => [
                    'label' => $field->label ?? '',
                    'field_id' => $field->name ?? 'field_' . $index,
                    'placeholder' => $field->placeholder ?? '',
                    'help_text' => $field->help_text ?? '',
                    'is_required' => (bool) $field->is_required,
                    'options' => $options,
                    'type' => in_array($field->type, ['text', 'email', 'tel', 'number', 'url']) ? $field->type : 'text',
                ],
                'children' => [],
            ];
        }
        
        // Wrap all fields in a row/column structure for builder compatibility
        return [
            [
                'id' => 'row-' . time(),
                'type' => 'row',
                'settings' => [],
                'children' => [
                    [
                        'id' => 'col-' . time(),
                        'type' => 'column',
                        'settings' => ['flexGrow' => 1],
                        'children' => $fieldBlocks,
                    ]
                ],
            ]
        ];
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is data transformation, not reversible automatically.
        // The original fields remain in form_fields table.
    }
};
