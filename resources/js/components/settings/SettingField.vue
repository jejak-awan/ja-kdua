<template>
    <div class="p-4 bg-card rounded-lg border border-border">
        <label class="block text-sm font-medium text-foreground mb-1">
            {{ label }}
        </label>
        <p v-if="description" class="text-xs text-muted-foreground mb-2">
            {{ description }}
        </p>

        <!-- Dropdown Select (if field has options) -->
        <Select
            v-if="hasOptions && !isMailPort"
            :model-value="localValue"
            :disabled="disabled"
            @update:model-value="localValue = $event; updateValue()"
        >
            <SelectTrigger :class="{ 'border-destructive focus:ring-destructive': error }">
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
            <SelectTrigger :class="{ 'border-destructive focus:ring-destructive': error }">
                <SelectValue :placeholder="$t('common.actions.select')" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="option in translatedMailPortOptions"
                    :key="option.value"
                    :value="String(option.value)"
                >
                    {{ option.label }}
                </SelectItem>
            </SelectContent>
        </Select>

        <!-- Text Input -->
        <Input
            v-else-if="(type === 'string' || type === 'password') && !isTextarea"
            v-model="localValue"
            :disabled="disabled"
            @input="updateValue"
            :type="(isPassword || type === 'password') ? 'password' : 'text'"
            :class="{ 'border-destructive focus-visible:ring-destructive': error }"
        />

        <!-- Textarea -->
        <Textarea
            v-else-if="isTextarea"
            v-model="localValue"
            :disabled="disabled"
            @input="updateValue"
            :rows="3"
            :class="{ 'border-destructive focus-visible:ring-destructive': error }"
        />

        <!-- Number Input -->
        <Input
            v-else-if="type === 'integer'"
            v-model.number="localValue"
            :disabled="disabled"
            @input="updateValue"
            type="number"
            :class="{ 'border-destructive focus-visible:ring-destructive': error }"
        />

        <!-- Boolean Toggle -->
        <div v-else-if="type === 'boolean'" class="mt-1 flex items-center space-x-2">
            <Switch
                :checked="localValue"
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

<script setup>
import { ref, computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { getFieldOptions, getMailPortOptions } from '../../config/settingsFieldOptions'
import Input from '../../components/ui/input.vue'
import Textarea from '../../components/ui/textarea.vue'
import Switch from '../../components/ui/switch.vue'
import Select from '../../components/ui/select.vue'
import SelectContent from '../../components/ui/select-content.vue'
import SelectItem from '../../components/ui/select-item.vue'
import SelectTrigger from '../../components/ui/select-trigger.vue'
import SelectValue from '../../components/ui/select-value.vue'

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
    },
    error: {
        type: [String, Array],
        default: null
    },
    disabled: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['update:modelValue'])

const localValue = ref(props.modelValue)

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
