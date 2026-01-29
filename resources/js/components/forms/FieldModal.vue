<template>
    <Dialog :open="true" @update:open="close">
        <DialogContent class="sm:max-w-[700px] max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>
                    {{ field ? $t('features.forms.fieldModal.editTitle') : $t('features.forms.fieldModal.createTitle') }}
                </DialogTitle>
            </DialogHeader>
            <form @submit.prevent="saveField" class="space-y-4 py-4">
                <!-- Field Type -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">
                        {{ $t('features.forms.fieldModal.fieldType') }} <span class="text-destructive">*</span>
                    </label>
                    <select
                        v-model="fieldData.type"
                        required
                        @change="handleTypeChange"
                        class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                        <option value="text">{{ $t('features.forms.fieldModal.types.text') }}</option>
                        <option value="email">{{ $t('features.forms.fieldModal.types.email') }}</option>
                        <option value="textarea">{{ $t('features.forms.fieldModal.types.textarea') }}</option>
                        <option value="number">{{ $t('features.forms.fieldModal.types.number') }}</option>
                        <option value="select">{{ $t('features.forms.fieldModal.types.select') }}</option>
                        <option value="radio">{{ $t('features.forms.fieldModal.types.radio') }}</option>
                        <option value="checkbox">{{ $t('features.forms.fieldModal.types.checkbox') }}</option>
                        <option value="file">{{ $t('features.forms.fieldModal.types.file') }}</option>
                        <option value="date">{{ $t('features.forms.fieldModal.types.date') }}</option>
                        <option value="url">{{ $t('features.forms.fieldModal.types.url') }}</option>
                        <option value="tel">{{ $t('features.forms.fieldModal.types.tel') }}</option>
                    </select>
                </div>

                <!-- Field Name -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">
                        {{ $t('features.forms.fieldModal.fieldName') }} <span class="text-destructive">*</span>
                    </label>
                    <input
                        v-model="fieldData.name"
                        type="text"
                        required
                        pattern="[a-z0-9_]+"
                        class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                        :placeholder="$t('features.forms.fieldModal.fieldNamePlaceholder')"
                    >
                    <p class="mt-1 text-xs text-muted-foreground">{{ $t('features.forms.fieldModal.fieldNameHelp') }}</p>
                </div>

                <!-- Field Label -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">
                        {{ $t('features.forms.fieldModal.fieldLabel') }} <span class="text-destructive">*</span>
                    </label>
                    <input
                        v-model="fieldData.label"
                        type="text"
                        required
                        class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                        :placeholder="$t('features.forms.fieldModal.fieldLabelPlaceholder')"
                    >
                </div>

                <!-- Placeholder -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">
                        {{ $t('features.forms.fieldModal.placeholder') }}
                    </label>
                    <input
                        v-model="fieldData.placeholder"
                        type="text"
                        class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                        :placeholder="$t('features.forms.fieldModal.placeholderHint')"
                    >
                </div>

                <!-- Help Text -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">
                        {{ $t('features.forms.fieldModal.helpText') }}
                    </label>
                    <textarea
                        v-model="fieldData.help_text"
                        rows="2"
                        class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                        :placeholder="$t('features.forms.fieldModal.helpTextPlaceholder')"
                    />
                </div>

                <!-- Options (for select, radio, checkbox) -->
                <div v-if="needsOptions">
                    <label class="block text-sm font-medium text-foreground mb-1">
                        {{ $t('features.forms.fieldModal.options') }} <span class="text-destructive">*</span>
                    </label>
                    <div class="space-y-2" v-if="fieldData.options">
                        <div
                            v-for="(option, index) in fieldData.options"
                            :key="index"
                            class="flex items-center space-x-2"
                        >
                            <input
                                v-model="fieldData.options[index]"
                                type="text"
                                class="flex-1 px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                :placeholder="`Option ${index + 1}`"
                            >
                            <button
                                type="button"
                                @click="removeOption(index)"
                                class="p-2 text-destructive hover:bg-destructive/10 rounded transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                        <button
                            type="button"
                            @click="addOption"
                            class="inline-flex items-center px-3 py-2 border border-input bg-card text-foreground rounded-md text-sm font-medium hover:bg-muted"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            {{ $t('features.forms.fieldModal.addOption') }}
                        </button>
                    </div>
                </div>

                <!-- Required -->
                <div class="flex items-center">
                    <input
                        v-model="fieldData.is_required"
                        type="checkbox"
                        id="is_required"
                        class="h-4 w-4 text-primary focus:ring-primary border-input rounded"
                    >
                    <label for="is_required" class="ml-2 block text-sm text-foreground">
                        {{ $t('features.forms.fieldModal.required') }}
                    </label>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="close">
                        {{ $t('common.actions.cancel') }}
                    </Button>
                    <Button type="submit">
                        {{ field ? $t('features.forms.fieldModal.updateField') : $t('features.forms.fieldModal.addField') }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { reactive, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import toast from '@/services/toast';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
    Button
} from '@/components/ui';

interface Field {
    id?: number | string;
    name: string;
    label: string;
    type: string;
    placeholder?: string;
    help_text?: string;
    options?: string[];
    is_required: boolean;
    [key: string]: any;
}

const { t } = useI18n();

const props = defineProps<{
    field?: Field | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'save', field: Field): void;
}>();

const fieldData = reactive<Field>({
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
    } else if (!fieldData.options || fieldData.options.length === 0) {
        fieldData.options = [''];
    }
};

const addOption = () => {
    if (!fieldData.options) fieldData.options = [];
    fieldData.options.push('');
};

const removeOption = (index: number) => {
    fieldData.options?.splice(index, 1);
};

const saveField = () => {
    if (needsOptions.value && (!fieldData.options || fieldData.options.length === 0)) {
        toast.error('Error', t('features.forms.fieldModal.optionRequired'));
        return;
    }

    const fieldToSave: Field = {
        ...fieldData,
        options: needsOptions.value ? fieldData.options?.filter(opt => opt.trim()) : []
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
