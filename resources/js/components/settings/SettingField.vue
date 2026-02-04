<template>
    <div>
        <label class="block text-sm font-medium text-foreground mb-1">
            {{ label }}
        </label>
        <p v-if="description" class="text-xs text-muted-foreground mb-2">
            {{ description }}
        </p>

        <!-- Dropdown Select (if field has options) -->
        <Select
            v-if="hasOptions && !isMailPort"
            :model-value="localValue !== null && localValue !== undefined ? String(localValue) : undefined"
            :disabled="disabled"
            @update:model-value="localValue = hasNumericOptions ? Number($event) : $event; updateValue()"
        >
            <SelectTrigger :class="error ? 'border-destructive focus:ring-destructive' : ''">
                <SelectValue :placeholder="$t('common.actions.select')" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="option in translatedFieldOptions"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </SelectItem>
            </SelectContent>
        </Select>

        <!-- Mail Port Dropdown (dynamic based on encryption) -->
        <Select
            v-else-if="isMailPort"
            :model-value="String(localValue)"
            :disabled="disabled"
            @update:model-value="localValue = Number($event); updateValue()"
        >
            <SelectTrigger :class="error ? 'border-destructive focus:ring-destructive' : ''">
                <SelectValue :placeholder="$t('common.actions.select')" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="option in translatedMailPortOptions"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </SelectItem>
            </SelectContent>
        </Select>

        <Input
            v-else-if="(type === 'string' || type === 'password') && !isTextarea"
            :model-value="(localValue as string)"
            :disabled="disabled"
            @input="localValue = ($event.target as HTMLInputElement).value; updateValue()"
            :type="(isPassword || type === 'password') ? 'password' : 'text'"
            :class="error ? 'border-destructive focus-visible:ring-destructive' : ''"
        />

        <!-- Textarea -->
        <Textarea
            v-else-if="isTextarea"
            :model-value="(localValue as string)"
            :disabled="disabled"
            @input="localValue = ($event.target as HTMLTextAreaElement).value; updateValue()"
            :rows="3"
            :class="error ? 'border-destructive focus-visible:ring-destructive' : ''"
        />

        <!-- Number Input -->
        <Input
            v-else-if="type === 'integer'"
            :model-value="(localValue as number)"
            :disabled="disabled"
            @input="localValue = Number(($event.target as HTMLInputElement).value); updateValue()"
            type="number"
            :class="error ? 'border-destructive focus-visible:ring-destructive' : ''"
        />

        <!-- Boolean Toggle -->
        <div v-else-if="type === 'boolean'" class="mt-1 flex items-center space-x-2">
            <Switch
                :checked="Boolean(localValue)"
                :disabled="disabled"
                @update:checked="localValue = $event; updateValue()"
            />
            <span class="text-sm text-foreground">
                {{ localValue ? enabledText : disabledText }}
            </span>
        </div>
        
        <!-- Error Message -->
        <p v-if="error" class="text-sm text-destructive mt-1">
            {{ Array.isArray(error) ? error[0] : error }}
        </p>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { getFieldOptions, getMailPortOptions } from '@/config/settingsFieldOptions'
import type { SettingValue } from '@/types/settings'
import {
    Input,
    Textarea,
    Switch,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/components/ui'

interface SettingOption {
    value: string | number;
    label?: string;
    labelKey?: string;
}

const { t } = useI18n()


const props = withDefaults(defineProps<{
    modelValue: SettingValue;
    fieldKey: string;
    label: string;
    description?: string;
    type?: string;
    mailEncryption?: string;
    enabledText?: string;
    disabledText?: string;
    error?: string | string[] | null;
    disabled?: boolean;
}>(), {
    description: '',
    type: 'string',
    mailEncryption: 'tls',
    enabledText: 'Enabled',
    disabledText: 'Disabled',
    error: null,
    disabled: false,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: SettingValue): void;
}>()

const localValue = ref<SettingValue>(props.modelValue)

// Watch for external changes
watch(() => props.modelValue, (newValue) => {
    if (newValue !== localValue.value) {
        localValue.value = newValue
    }
})

// Computed properties
const hasOptions = computed(() => {
    return getFieldOptions(props.fieldKey) !== null
})

const fieldOptions = computed<SettingOption[]>(() => {
    return getFieldOptions(props.fieldKey) || []
})

// Translate options using labelKey
const translatedFieldOptions = computed(() => {
    return fieldOptions.value.map(option => ({
        value: String(option.value),
        label: option.labelKey ? t(option.labelKey) : (option.label || String(option.value))
    }))
})

const hasNumericOptions = computed(() => {
    return fieldOptions.value.some(option => typeof option.value === 'number')
})

const isMailPort = computed(() => {
    return props.fieldKey === 'mail_port'
})

const mailPortOptions = computed<SettingOption[]>(() => {
    return getMailPortOptions(props.mailEncryption || 'tls')
})

const translatedMailPortOptions = computed(() => {
    return mailPortOptions.value.map(option => ({
        value: String(option.value),
        label: option.labelKey ? t(option.labelKey) : (option.label || String(option.value))
    }))
})

const isPassword = computed(() => {
    return props.fieldKey.includes('password')
})

const isTextarea = computed(() => {
    return props.type === 'text' || props.type === 'json'
})

// Update parent
const updateValue = () => {
    emit('update:modelValue', localValue.value)
}
</script>
