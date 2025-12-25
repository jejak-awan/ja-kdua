<template>
    <div class="p-4 bg-card rounded-lg border border-border">
        <label class="block text-sm font-medium text-foreground mb-1">
            {{ label }}
        </label>
        <p v-if="description" class="text-xs text-muted-foreground mb-2">
            {{ description }}
        </p>

        <!-- Dropdown Select (if field has options) -->
        <select
            v-if="hasOptions && !isMailPort"
            v-model="localValue"
            @change="updateValue"
            class="mt-1 w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary text-sm"
        >
            <option
                v-for="option in translatedFieldOptions"
                :key="option.value"
                :value="option.value"
            >
                {{ option.label }}
            </option>
        </select>

        <!-- Mail Port Dropdown (dynamic based on encryption) -->
        <select
            v-else-if="isMailPort"
            v-model.number="localValue"
            @change="updateValue"
            class="mt-1 w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary text-sm"
        >
            <option
                v-for="option in translatedMailPortOptions"
                :key="option.value"
                :value="option.value"
            >
                {{ option.label }}
            </option>
        </select>

        <!-- Text Input -->
        <input
            v-else-if="type === 'string' && !isTextarea"
            v-model="localValue"
            @input="updateValue"
            :type="isPassword ? 'password' : 'text'"
            class="mt-1 w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary text-sm"
        >

        <!-- Textarea -->
        <textarea
            v-else-if="isTextarea"
            v-model="localValue"
            @input="updateValue"
            rows="3"
            class="mt-1 w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary text-sm"
        />

        <!-- Number Input -->
        <input
            v-else-if="type === 'integer'"
            v-model.number="localValue"
            @input="updateValue"
            type="number"
            class="mt-1 w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary text-sm"
        >

        <!-- Boolean Toggle -->
        <div v-else-if="type === 'boolean'" class="mt-1">
            <label class="flex items-center cursor-pointer">
                <div class="relative">
                    <input
                        v-model="localValue"
                        @change="updateValue"
                        type="checkbox"
                        class="sr-only peer"
                    >
                    <div class="w-11 h-6 bg-gray-600 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all after:shadow-md peer-checked:bg-emerald-500"></div>
                </div>
                <span class="ml-3 text-sm text-foreground">
                    {{ localValue ? enabledText : disabledText }}
                </span>
            </label>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { getFieldOptions, getMailPortOptions } from '../../config/settingsFieldOptions'

const { t } = useI18n()

const props = defineProps({
    modelValue: {
        required: true
    },
    fieldKey: {
        type: String,
        required: true
    },
    label: {
        type: String,
        required: true
    },
    description: {
        type: String,
        default: ''
    },
    type: {
        type: String,
        default: 'string'
    },
    mailEncryption: {
        type: String,
        default: 'tls'
    },
    enabledText: {
        type: String,
        default: 'Enabled'
    },
    disabledText: {
        type: String,
        default: 'Disabled'
    }
})

const emit = defineEmits(['update:modelValue'])

const localValue = ref(props.modelValue)

// Watch for external changes
watch(() => props.modelValue, (newValue) => {
    localValue.value = newValue
})

// Computed properties
const hasOptions = computed(() => {
    return getFieldOptions(props.fieldKey) !== null
})

const fieldOptions = computed(() => {
    return getFieldOptions(props.fieldKey) || []
})

// Translate options using labelKey
const translatedFieldOptions = computed(() => {
    return fieldOptions.value.map(option => ({
        value: option.value,
        label: option.labelKey ? t(option.labelKey) : (option.label || option.value)
    }))
})

const isMailPort = computed(() => {
    return props.fieldKey === 'mail_port'
})

const mailPortOptions = computed(() => {
    return getMailPortOptions(props.mailEncryption)
})

const translatedMailPortOptions = computed(() => {
    return mailPortOptions.value.map(option => ({
        value: option.value,
        label: option.labelKey ? t(option.labelKey) : (option.label || option.value)
    }))
})

const isPassword = computed(() => {
    return props.fieldKey.includes('password')
})

const isTextarea = computed(() => {
    return props.type === 'text'
})

// Update parent
const updateValue = () => {
    emit('update:modelValue', localValue.value)
}
</script>
