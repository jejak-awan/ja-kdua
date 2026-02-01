<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('features.forms.modal.createTitle') }}</h1>
                <p class="mt-1 text-sm text-muted-foreground">{{ $t('features.forms.title') }}</p>
            </div>
            <router-link :to="{ name: 'forms' }">
                <Button variant="ghost" size="sm">
                    ← {{ $t('common.actions.back') }}
                </Button>
            </router-link>
        </div>

        <!-- Form Content -->
        <div class="bg-card border border-border rounded-lg overflow-hidden">
            <!-- Form Settings -->
            <div class="p-6 space-y-6 border-b border-border">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.forms.modal.formName') }} <span class="text-destructive">*</span>
                        </label>
                        <Input
                            v-model="formData.name"
                            type="text"
                            required
                            @input="generateSlug"
                            :placeholder="$t('features.forms.modal.placeholders.name')"
                            :class="{ 'border-destructive focus-visible:ring-destructive': errors.name }"
                        />
                        <p v-if="errors.name" class="text-sm text-destructive mt-1">
                            {{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.forms.modal.slug') }} <span class="text-destructive">*</span>
                        </label>
                        <Input
                            v-model="formData.slug"
                            type="text"
                            required
                            :placeholder="$t('features.forms.modal.placeholders.slug')"
                            :class="{ 'border-destructive focus-visible:ring-destructive': errors.slug }"
                        />
                        <p v-if="errors.slug" class="text-sm text-destructive mt-1">
                            {{ Array.isArray(errors.slug) ? errors.slug[0] : errors.slug }}
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">
                        {{ $t('features.forms.modal.description') }}
                    </label>
                    <Textarea
                        v-model="formData.description"
                        rows="2"
                        :placeholder="$t('features.forms.modal.placeholders.description')"
                    />
                </div>

                <!-- Success Message & Redirect -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.forms.modal.successMessage') }}
                        </label>
                        <Input
                            v-model="formData.success_message"
                            type="text"
                            :placeholder="$t('features.forms.modal.placeholders.successMessage')"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.forms.modal.redirectUrl') }}
                        </label>
                        <Input
                            v-model="formData.redirect_url"
                            type="url"
                            :placeholder="$t('features.forms.modal.placeholders.redirectUrl')"
                        />
                    </div>
                </div>

                <!-- Status -->
                <div class="flex items-center space-x-2">
                    <Checkbox
                        id="is_active"
                        v-model:checked="formData.is_active"
                    />
                    <label for="is_active" class="text-sm font-medium leading-none">
                        {{ $t('features.forms.modal.isActive') }}
                    </label>
                </div>
            </div>

            <!-- Visual Builder Section -->
            <div class="p-6 pt-4">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-foreground">{{ $t('features.forms.modal.formFields') }}</h3>
                        <p class="text-xs text-muted-foreground mt-1">
                            Drag and drop form fields using the visual builder below
                        </p>
                    </div>
                </div>

                <!-- Visual Builder -->
                <div class="border border-border rounded-lg overflow-hidden h-[600px] relative bg-slate-50 dark:bg-slate-900/20">
                    <Builder 
                        ref="builderRef"
                        v-model="formData.blocks"
                        mode="page"
                    />
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3 p-6 pt-0">
                <router-link :to="{ name: 'forms' }">
                    <Button type="button" variant="outline">
                        {{ $t('common.actions.cancel') }}
                    </Button>
                </router-link>
                <Button
                    type="button"
                    :disabled="saving || !isValid"
                    @click="handleSubmit"
                >
                    <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ saving ? $t('common.messages.loading.creating') : $t('features.forms.actions.createForm') }}
                </Button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import type { BlockInstance } from '@/types/builder';
import api from '../../../services/api';
import { useToast } from '../../../composables/useToast';
import { useFormValidation } from '../../../composables/useFormValidation';
import { formBuilderSchema } from '../../../schemas';
import Button from '../../../components/ui/button.vue';
import Input from '../../../components/ui/input.vue';
import Textarea from '../../../components/ui/textarea.vue';
import Checkbox from '../../../components/ui/checkbox.vue';
import Builder from '../../../components/builder/Builder.vue';

const { t } = useI18n();
const router = useRouter();
const toast = useToast();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(formBuilderSchema);

const saving = ref(false);
const builderRef = ref(null);

const formData = reactive({
    name: '',
    slug: '',
    description: '',
    success_message: '',
    redirect_url: '',
    is_active: true,
    blocks: [
        {
            id: `row-${Date.now()}`,
            type: 'row',
            settings: {},
            children: [
                {
                    id: `col-${Date.now()}`,
                    type: 'column',
                    settings: { flexGrow: 1 },
                    children: []
                }
            ]
        }
    ] as BlockInstance[]
});

const isValid = computed(() => {
    return !!formData.name?.trim() && !!formData.slug?.trim();
});

const generateSlug = () => {
    if (!formData.slug || formData.slug === slugify(formData.name)) {
        formData.slug = slugify(formData.name);
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
    const validationData = { name: formData.name, slug: formData.slug };
    if (!validateWithZod(validationData)) return;

    saving.value = true;
    clearErrors();
    try {
        const payload = {
            name: formData.name,
            slug: formData.slug,
            description: formData.description,
            success_message: formData.success_message,
            redirect_url: formData.redirect_url,
            is_active: formData.is_active,
            blocks: formData.blocks
        };
        await api.post('/admin/ja/forms', payload);
        toast.success.create('Form');
        router.push({ name: 'forms' });
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
