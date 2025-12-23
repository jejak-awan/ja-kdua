<template>
    <div class="fixed inset-0 bg-black/50 overflow-y-auto h-full w-full z-50" @click.self="close">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-card max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-foreground">
                    {{ field ? 'Edit Field' : 'Add New Field' }}
                </h2>
                <button
                    @click="close"
                    class="text-gray-400 hover:text-muted-foreground"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="saveField" class="space-y-4">
                <!-- Field Type -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">
                        Field Type <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="fieldData.type"
                        required
                        @change="handleTypeChange"
                        class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="text">Text</option>
                        <option value="email">Email</option>
                        <option value="textarea">Textarea</option>
                        <option value="number">Number</option>
                        <option value="select">Select</option>
                        <option value="radio">Radio</option>
                        <option value="checkbox">Checkbox</option>
                        <option value="file">File Upload</option>
                        <option value="date">Date</option>
                        <option value="url">URL</option>
                        <option value="tel">Phone</option>
                    </select>
                </div>

                <!-- Field Name -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">
                        Field Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="fieldData.name"
                        type="text"
                        required
                        pattern="[a-z0-9_]+"
                        class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="field_name"
                    >
                    <p class="mt-1 text-xs text-muted-foreground">Only lowercase letters, numbers, and underscores</p>
                </div>

                <!-- Field Label -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">
                        Field Label <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="fieldData.label"
                        type="text"
                        required
                        class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Field Label"
                    >
                </div>

                <!-- Placeholder -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">
                        Placeholder
                    </label>
                    <input
                        v-model="fieldData.placeholder"
                        type="text"
                        class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Enter placeholder text..."
                    >
                </div>

                <!-- Help Text -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">
                        Help Text
                    </label>
                    <textarea
                        v-model="fieldData.help_text"
                        rows="2"
                        class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Help text to display below the field..."
                    />
                </div>

                <!-- Options (for select, radio, checkbox) -->
                <div v-if="needsOptions">
                    <label class="block text-sm font-medium text-foreground mb-1">
                        Options <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-2">
                        <div
                            v-for="(option, index) in fieldData.options"
                            :key="index"
                            class="flex items-center space-x-2"
                        >
                            <input
                                v-model="fieldData.options[index]"
                                type="text"
                                class="flex-1 px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                :placeholder="`Option ${index + 1}`"
                            >
                            <button
                                type="button"
                                @click="removeOption(index)"
                                class="p-2 text-red-600 hover:text-red-800 hover:bg-red-500/20 rounded transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                        <button
                            type="button"
                            @click="addOption"
                            class="inline-flex items-center px-3 py-2 border border-input bg-card text-foreground rounded-md text-sm font-medium text-foreground bg-card hover:bg-muted"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Option
                        </button>
                    </div>
                </div>

                <!-- Required -->
                <div class="flex items-center">
                    <input
                        v-model="fieldData.is_required"
                        type="checkbox"
                        id="is_required"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-input rounded"
                    >
                    <label for="is_required" class="ml-2 block text-sm text-foreground">
                        Field is required
                    </label>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-border">
                    <button
                        type="button"
                        @click="close"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md shadow-sm text-sm font-medium text-foreground bg-card hover:bg-muted"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
                    >
                        {{ field ? 'Update Field' : 'Add Field' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';

const props = defineProps({
    field: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close', 'save']);

const fieldData = reactive({
    name: '',
    label: '',
    type: 'text',
    placeholder: '',
    help_text: '',
    options: [],
    is_required: false
});

const needsOptions = computed(() => {
    return ['select', 'radio', 'checkbox'].includes(fieldData.type);
});

watch(() => props.field, (newField) => {
    if (newField) {
        fieldData.name = newField.name || '';
        fieldData.label = newField.label || '';
        fieldData.type = newField.type || 'text';
        fieldData.placeholder = newField.placeholder || '';
        fieldData.help_text = newField.help_text || '';
        fieldData.options = newField.options ? [...newField.options] : [];
        fieldData.is_required = newField.is_required || false;
    } else {
        // Reset field
        fieldData.name = '';
        fieldData.label = '';
        fieldData.type = 'text';
        fieldData.placeholder = '';
        fieldData.help_text = '';
        fieldData.options = [];
        fieldData.is_required = false;
    }
}, { immediate: true });

const handleTypeChange = () => {
    // Reset options if type doesn't need them
    if (!needsOptions.value) {
        fieldData.options = [];
    } else if (fieldData.options.length === 0) {
        fieldData.options = [''];
    }
};

const addOption = () => {
    fieldData.options.push('');
};

const removeOption = (index) => {
    fieldData.options.splice(index, 1);
};

const saveField = () => {
    if (needsOptions.value && fieldData.options.length === 0) {
        alert('Please add at least one option for this field type.');
        return;
    }

    const fieldToSave = {
        ...fieldData,
        options: needsOptions.value ? fieldData.options.filter(opt => opt.trim()) : []
    };

    if (props.field && props.field.id) {
        fieldToSave.id = props.field.id;
    }

    emit('save', fieldToSave);
};

const close = () => {
    emit('close');
};
</script>

