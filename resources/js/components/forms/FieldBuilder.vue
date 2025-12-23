<template>
    <div>
        <div v-if="fields.length === 0" class="text-center py-8 border-2 border-dashed border-input rounded-lg">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <p class="mt-2 text-sm text-muted-foreground">No fields yet. Click "Add Field" to get started.</p>
        </div>

        <div v-else class="space-y-3">
            <div
                v-for="(field, index) in fields"
                :key="field.id || index"
                class="bg-muted border border-border rounded-lg p-4 hover:border-indigo-300 transition-colors"
            >
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="px-2 py-1 text-xs font-medium bg-indigo-100 text-indigo-800 rounded">
                                {{ field.type }}
                            </span>
                            <span class="font-semibold text-foreground">{{ field.label || field.name }}</span>
                            <span v-if="field.is_required" class="px-2 py-1 text-xs font-medium bg-red-500/20 text-red-400 rounded">
                                Required
                            </span>
                        </div>
                        <p v-if="field.help_text" class="text-sm text-muted-foreground mb-1">{{ field.help_text }}</p>
                        <p v-if="field.placeholder" class="text-xs text-muted-foreground">Placeholder: {{ field.placeholder }}</p>
                        <div v-if="field.options && field.options.length > 0" class="mt-2">
                            <p class="text-xs text-muted-foreground">Options: {{ field.options.join(', ') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 ml-4">
                        <button
                            @click="$emit('update-field', field)"
                            class="p-2 text-muted-foreground hover:text-indigo-600 hover:bg-indigo-500/20 rounded transition-colors"
                            title="Edit field"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button
                            @click="moveField(index, 'up')"
                            :disabled="index === 0"
                            class="p-2 text-muted-foreground hover:text-indigo-600 hover:bg-indigo-500/20 rounded transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            title="Move up"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                            </svg>
                        </button>
                        <button
                            @click="moveField(index, 'down')"
                            :disabled="index === fields.length - 1"
                            class="p-2 text-muted-foreground hover:text-indigo-600 hover:bg-indigo-500/20 rounded transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            title="Move down"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <button
                            @click="$emit('delete-field', field.id)"
                            class="p-2 text-red-600 hover:text-red-800 hover:bg-red-500/20 rounded transition-colors"
                            title="Delete field"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    fields: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['add-field', 'update-field', 'delete-field', 'reorder-fields']);

const moveField = (index, direction) => {
    const newFields = [...props.fields];
    const targetIndex = direction === 'up' ? index - 1 : index + 1;
    
    if (targetIndex >= 0 && targetIndex < newFields.length) {
        [newFields[index], newFields[targetIndex]] = [newFields[targetIndex], newFields[index]];
        emit('reorder-fields', newFields);
    }
};
</script>

