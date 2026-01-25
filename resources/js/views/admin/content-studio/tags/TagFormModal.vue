<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>{{ isEdit ? $t('features.tags.form.editTitle') : $t('features.tags.form.createTitle') }}</DialogTitle>
                <DialogDescription>
                    {{ isEdit ? $t('features.tags.form.editDescription') : $t('features.tags.form.createDescription') }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4 py-4">
                <!-- Name -->
                <div class="space-y-2">
                    <Label>
                        {{ $t('features.tags.form.name') }} <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        v-model="form.name"
                        required
                        @input="generateSlug"
                        :class="errors.name ? 'border-destructive focus-visible:ring-destructive' : ''"
                        :placeholder="$t('features.tags.form.namePlaceholder')"
                    />
                    <p v-if="errors.name" class="text-sm text-destructive">
                        {{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}
                    </p>
                </div>

                <!-- Slug -->
                <div class="space-y-2">
                    <Label>
                        {{ $t('features.tags.form.slug') }} <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        v-model="form.slug"
                        required
                        :class="errors.slug ? 'border-destructive focus-visible:ring-destructive' : ''"
                        :placeholder="$t('features.tags.form.slugPlaceholder')"
                    />
                    <p class="text-xs text-muted-foreground">{{ $t('features.tags.form.slugHelp') }}</p>
                    <p v-if="errors.slug" class="text-sm text-destructive">
                        {{ Array.isArray(errors.slug) ? errors.slug[0] : errors.slug }}
                    </p>
                </div>

                <!-- Description -->
                <div class="space-y-2">
                    <Label>
                        {{ $t('features.tags.form.description') }}
                    </Label>
                    <Textarea
                        v-model="form.description"
                        rows="3"
                        :placeholder="$t('features.tags.form.descriptionPlaceholder')"
                    />
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="$emit('update:open', false)">
                        {{ $t('common.actions.cancel') }}
                    </Button>
                    <Button type="submit" :disabled="saving || !isValid">
                        <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                        {{ saving ? (isEdit ? $t('common.messages.loading.updating') : $t('common.messages.loading.creating')) : (isEdit ? $t('common.actions.update') : $t('common.actions.create')) }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { useFormValidation } from '@/composables/useFormValidation';
import { tagSchema } from '@/schemas';
import { Loader2 } from 'lucide-vue-next';
import type { Tag } from '@/types/cms';

// Shadcn UI
// @ts-ignore
import Button from '@/components/ui/button.vue';
// @ts-ignore
import Input from '@/components/ui/input.vue';
// @ts-ignore
import Label from '@/components/ui/label.vue';
// @ts-ignore
import Textarea from '@/components/ui/textarea.vue';
// @ts-ignore
import Dialog from '@/components/ui/dialog.vue';
import DialogContent from '@/components/ui/dialog-content.vue';
import DialogDescription from '@/components/ui/dialog-description.vue';
import DialogFooter from '@/components/ui/dialog-footer.vue';
import DialogHeader from '@/components/ui/dialog-header.vue';
import DialogTitle from '@/components/ui/dialog-title.vue';

const props = defineProps<{
    open: boolean;
    tag?: Tag | null;
}>();

const emit = defineEmits(['update:open', 'success']);

const { t } = useI18n();
const toast = useToast();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(tagSchema);

const saving = ref(false);
const isEdit = computed(() => !!props.tag);

const form = ref<any>({
    name: '',
    slug: '',
    description: '',
});

// Initialize form when opening
watch(() => props.open, (isOpen) => {
    if (isOpen) {
        clearErrors();
        if (props.tag) {
            form.value = { ...props.tag };
        } else {
            // Reset
            form.value = {
                name: '',
                slug: '',
                description: '',
            };
        }
    }
});

const isValid = computed(() => {
    return !!form.value.name?.trim();
});

const generateSlug = () => {
    if (!isEdit.value || !form.value.slug || form.value.slug === slugify(form.value.name)) {
         form.value.slug = slugify(form.value.name);
    }
};

const slugify = (text: string) => {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '-')
        .replace(/[^\w\-]+/g, '')
        .replace(/\-\-+/g, '-')
        .replace(/^-+/, '')
        .replace(/-+$/, '');
};

const handleSubmit = async () => {
    if (!validateWithZod(form.value)) return;

    saving.value = true;
    clearErrors();
    try {
        if (isEdit.value) {
            await api.put(`/admin/ja/tags/${props.tag?.id}`, form.value);
            toast.success.update('Tag');
        } else {
            await api.post('/admin/ja/tags', form.value);
            toast.success.create('Tag');
        }
        
        emit('success');
        emit('update:open', false);
    } catch (error: any) {
        if (error.response?.status === 422) {
            setErrors(error.response.data.errors || {});
        } else {
            toast.error.fromResponse(error);
        }
    } finally {
        saving.value = false;
    }
};
</script>
