<template>
    <div class="space-y-1.5">
        <Label class="text-xs flex items-center gap-1">
            {{ setting.label }}
            <span v-if="setting.required" class="text-destructive">*</span>
        </Label>

        <!-- Text Input -->
        <Input 
            v-if="setting.type === 'text'"
            :model-value="value"
            :placeholder="setting.placeholder"
            class="h-8"
            @update:model-value="$emit('update', $event)"
        />

        <!-- Textarea -->
        <Textarea 
            v-else-if="setting.type === 'textarea'"
            :model-value="value"
            :placeholder="setting.placeholder"
            rows="2"
            @update:model-value="$emit('update', $event)"
        />

        <!-- Number Input -->
        <Input 
            v-else-if="setting.type === 'number'"
            type="number"
            :model-value="value"
            :min="setting.min"
            :max="setting.max"
            class="h-8"
            @update:model-value="$emit('update', Number($event))"
        />

        <!-- Select -->
        <Select 
            v-else-if="setting.type === 'select'"
            :model-value="value"
            @update:model-value="$emit('update', $event)"
        >
            <SelectTrigger class="h-8">
                <SelectValue :placeholder="setting.placeholder || 'Select...'" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem 
                    v-for="opt in setting.options" 
                    :key="opt.value" 
                    :value="opt.value"
                >
                    {{ opt.label }}
                </SelectItem>
            </SelectContent>
        </Select>

        <!-- Boolean / Checkbox -->
        <div v-else-if="setting.type === 'boolean'" class="flex items-center gap-2 pt-1">
            <Checkbox 
                :id="setting.key"
                :checked="value"
                @update:checked="$emit('update', $event)"
            />
            <Label :for="setting.key" class="text-xs font-normal cursor-pointer">
                {{ setting.label }}
            </Label>
        </div>

        <!-- Color Picker -->
        <div v-else-if="setting.type === 'color'" class="flex items-center gap-2">
            <input 
                type="color" 
                :value="value || '#000000'"
                class="w-8 h-8 rounded border border-border cursor-pointer"
                @input="$emit('update', $event.target.value)"
            />
            <Input 
                :model-value="value"
                placeholder="#000000"
                class="h-8 flex-1 font-mono text-xs"
                @update:model-value="$emit('update', $event)"
            />
        </div>

        <!-- Icon Picker -->
        <div v-else-if="setting.type === 'icon_picker'" class="w-full">
            <IconPicker 
                :model-value="value"
                @update:model-value="$emit('update', $event)"
            />
        </div>

        <!-- Media / Image -->
        <div v-else-if="setting.type === 'media'" class="space-y-2">
            <div v-if="value" class="relative">
                <img :src="value" alt="" class="w-full h-24 object-cover rounded border border-border" />
                <Button 
                    size="icon" 
                    variant="destructive" 
                    class="absolute top-1 right-1 h-6 w-6"
                    @click="$emit('update', null)"
                >
                    <X class="w-3 h-3" />
                </Button>
            </div>
            <MediaPicker 
                :label="value ? 'Change Image' : 'Select Image'"
                @selected="handleMediaSelect"
            />
        </div>

        <!-- Data Select (for pages, posts, etc) -->
        <div v-else-if="setting.type === 'data_select'" class="text-xs text-muted-foreground">
            ID: {{ value }}
            <!-- Full implementation would fetch and display options -->
        </div>

        <!-- Fallback -->
        <div v-else class="text-xs text-muted-foreground">
            Unsupported type: {{ setting.type }}
        </div>

        <!-- Description -->
        <p v-if="setting.description" class="text-[10px] text-muted-foreground">
            {{ setting.description }}
        </p>
    </div>
</template>

<script setup>
// UI Components
import Input from '../../ui/input.vue';
import Textarea from '../../ui/textarea.vue';
import Label from '../../ui/label.vue';
import Checkbox from '../../ui/checkbox.vue';
import Select from '../../ui/select.vue';
import SelectTrigger from '../../ui/select-trigger.vue';
import SelectValue from '../../ui/select-value.vue';
import SelectContent from '../../ui/select-content.vue';
import SelectItem from '../../ui/select-item.vue';
import Button from '../../ui/button.vue';
import IconPicker from '../../ui/icon-picker.vue';
import MediaPicker from '../../MediaPicker.vue';

import { X } from 'lucide-vue-next';

const emit = defineEmits(['update']);

const handleMediaSelect = (media) => {
    if (media && media.url) {
        emit('update', media.url);
    }
};

defineProps({
    setting: {
        type: Object,
        required: true
    },
    value: {
        type: [String, Number, Boolean, Object, Array],
        default: null
    }
});
</script>
