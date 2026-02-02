<template>
    <div class="space-y-6 pb-20">
        <!-- Header & Main Metadata -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ $t('features.forms.modal.createTitle') }}</h1>
                        <p class="text-sm text-muted-foreground">{{ $t('features.forms.title') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <router-link :to="{ name: 'forms' }">
                        <Button variant="outline" type="button" class="shadow-sm">
                            {{ $t('common.actions.cancel') }}
                        </Button>
                    </router-link>

                    <Button
                        type="button"
                        variant="default"
                        :disabled="saving || !isValid"
                        @click="handleSubmit"
                        class="shadow-sm border-b-2 border-primary/20 active:border-b-0 translate-y-[-2px] active:translate-y-0 transition-all font-semibold"
                    >
                        <Loader2 v-if="saving" class="animate-spin mr-2 h-4 w-4" />
                        <Save v-else class="mr-2 h-4 w-4" />
                        {{ saving ? $t('common.messages.loading.creating') : $t('features.forms.actions.createForm') }}
                    </Button>
                </div>
            </div>

            <!-- Essential Meta Card -->
            <Card class="p-5 border-border/60 shadow-none mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 items-end gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-[11px] uppercase tracking-wider font-bold text-muted-foreground mb-1.5 ml-0.5">
                            {{ $t('features.forms.modal.formName') }} <span class="text-destructive font-normal">*</span>
                        </label>
                        <Input
                            v-model="formData.name"
                            type="text"
                            required
                            @input="generateSlug"
                            class="bg-background/50 border-border focus:ring-primary/20 h-11"
                            :placeholder="$t('features.forms.modal.placeholders.name')"
                            :class="{ 'border-destructive focus-visible:ring-destructive': errors.name }"
                        />
                        <p v-if="errors.name" class="text-xs text-destructive mt-1 ml-0.5">
                            {{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-[11px] uppercase tracking-wider font-bold text-muted-foreground mb-1.5 ml-0.5">
                            {{ $t('features.forms.modal.slug') }} <span class="text-destructive font-normal">*</span>
                        </label>
                        <div class="relative">
                            <Input
                                v-model="formData.slug"
                                type="text"
                                required
                                class="bg-background/50 border-border focus:ring-primary/20 pl-7 h-11"
                                :placeholder="$t('features.forms.modal.placeholders.slug')"
                                :class="{ 'border-destructive focus-visible:ring-destructive': errors.slug }"
                            />
                            <LinkIcon class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground" />
                        </div>
                        <p v-if="errors.slug" class="text-xs text-destructive mt-1 ml-0.5">
                            {{ Array.isArray(errors.slug) ? errors.slug[0] : errors.slug }}
                        </p>
                    </div>

                    <div class="flex items-center space-x-2 h-11 pb-2">
                        <Checkbox
                            id="is_active"
                            v-model:checked="formData.is_active"
                        />
                        <label for="is_active" class="text-sm font-medium leading-none cursor-pointer">
                            {{ $t('features.forms.modal.isActive') }}
                        </label>
                    </div>
                </div>
            </Card>
        </div>

        <!-- Tabs Navigation -->
        <Tabs defaultValue="fields" class="w-full">
            <div class="flex items-center justify-between mb-2">
                <TabsList class="bg-muted/50 p-1 rounded-lg">
                    <TabsTrigger value="fields" class="px-6 py-2 rounded-md transition-all active:scale-95">
                        <LayoutDashboard class="h-4 w-4 mr-2" />
                        {{ $t('features.forms.modal.tabs.fields') }}
                    </TabsTrigger>
                    <TabsTrigger value="settings" class="px-6 py-2 rounded-md transition-all active:scale-95">
                        <Settings2 class="h-4 w-4 mr-2" />
                        {{ $t('features.forms.modal.tabs.settings') }}
                    </TabsTrigger>
                </TabsList>
            </div>

            <!-- Visual Builder Tab -->
            <TabsContent value="fields" class="mt-0 focus-visible:ring-0">
                <div class="border border-border rounded-xl overflow-hidden h-[850px] relative bg-slate-50 dark:bg-slate-900/20 shadow-inner">
                    <Builder 
                        ref="builderRef"
                        v-model="formData.blocks"
                        mode="page"
                    />
                </div>
            </TabsContent>

            <!-- Advanced Settings Tab -->
            <TabsContent value="settings" class="mt-0 focus-visible:ring-0">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <Card class="lg:col-span-2 p-6 space-y-8 border-border/60 shadow-none">
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold border-b pb-2 mb-4">{{ $t('features.forms.modal.description') }}</h3>
                            <div>
                                <Textarea
                                    v-model="formData.description"
                                    rows="4"
                                    class="bg-background/50 border-border focus:ring-primary/20"
                                    :placeholder="$t('features.forms.modal.placeholders.description')"
                                />
                                <p class="text-xs text-muted-foreground mt-2 italic">Briefly describe the purpose of this form for internal reference.</p>
                            </div>
                        </div>

                        <div class="space-y-6 pt-2">
                            <h3 class="text-lg font-semibold border-b pb-2 mb-4">{{ $t('features.forms.modal.submissionBehavior') }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-foreground">
                                        {{ $t('features.forms.modal.successMessage') }}
                                    </label>
                                    <Input
                                        v-model="formData.success_message"
                                        type="text"
                                        class="bg-background/50 border-border focus:ring-primary/20"
                                        :placeholder="$t('features.forms.modal.placeholders.successMessage')"
                                    />
                                    <p class="text-[11px] text-muted-foreground italic">Displayed after a successful form submission.</p>
                                </div>
            
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-foreground">
                                        {{ $t('features.forms.modal.redirectUrl') }}
                                    </label>
                                    <div class="relative">
                                        <Input
                                            v-model="formData.redirect_url"
                                            type="url"
                                            class="bg-background/50 border-border focus:ring-primary/20 pl-8"
                                            :placeholder="$t('features.forms.modal.placeholders.redirectUrl')"
                                        />
                                        <Globe class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground" />
                                    </div>
                                    <p class="text-[11px] text-muted-foreground italic">Optional: Redirect user to this URL after submission.</p>
                                </div>
                            </div>
                        </div>
                    </Card>

                    <div class="space-y-6">
                        <Card class="p-6 border-border/60 shadow-none h-fit space-y-4">
                            <h3 class="font-bold flex items-center text-primary">
                                <Info class="h-4 w-4 mr-2" />
                                {{ $t('features.forms.modal.publishingTips.title') }}
                            </h3>
                            <ul class="text-xs space-y-3 text-muted-foreground leading-relaxed">
                                <li class="flex items-start gap-2">
                                    <div class="h-1 w-1 bg-primary rounded-full mt-1.5 shrink-0" />
                                    {{ $t('features.forms.modal.publishingTips.slug') }}
                                </li>
                                <li class="flex items-start gap-2">
                                    <div class="h-1 w-1 bg-primary rounded-full mt-1.5 shrink-0" />
                                    {{ $t('features.forms.modal.publishingTips.fields') }}
                                </li>
                                <li class="flex items-start gap-2">
                                    <div class="h-1 w-1 bg-primary rounded-full mt-1.5 shrink-0" />
                                    {{ $t('features.forms.modal.publishingTips.mobile') }}
                                </li>
                            </ul>
                        </Card>
                    </div>
                </div>
            </TabsContent>
        </Tabs>
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
import { Button, Card, Checkbox, Input, Textarea, Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui';
import { 
    ArrowLeft, 
    Save, 
    Loader2, 
    Link as LinkIcon, 
    LayoutDashboard, 
    Settings2, 
    Globe, 
    Info 
} from 'lucide-vue-next';

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
