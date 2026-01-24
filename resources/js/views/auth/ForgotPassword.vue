<template>
    <div class="min-h-screen flex items-center justify-center bg-muted/40 px-4 py-12 sm:px-6 lg:px-8">
        <Card class="w-full max-w-md">
            <CardHeader class="space-y-1">
                <CardTitle class="text-2xl font-bold text-center tracking-tight">
                    {{ t('features.auth.forgotPassword.title') }}
                </CardTitle>
                <CardDescription class="text-center">
                    {{ t('features.auth.forgotPassword.subtitle') }}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form class="space-y-4" @submit.prevent="handleSubmit">
                    <div class="space-y-2">
                        <Label for="email">{{ t('common.labels.email') }}</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            name="email"
                            type="email"
                            required
                            :placeholder="t('features.auth.login.emailPlaceholder')"
                        />
                    </div>

                    <div v-if="message" class="rounded-md p-3 text-sm border" :class="messageType === 'error' ? 'bg-destructive/15 text-destructive border-destructive/20' : 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border-emerald-500/20'">
                        {{ message }}
                    </div>

                    <Button type="submit" class="w-full" :disabled="loading || !isValid">
                        <Loader2 v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
                        <span v-if="loading">{{ t('features.auth.verifyEmail.sending') }}</span>
                        <span v-else>{{ t('features.auth.forgotPassword.submit') }}</span>
                    </Button>

                    <div class="text-center text-sm text-muted-foreground mt-4">
                        <router-link :to="{ name: 'login' }" class="flex items-center justify-center font-medium text-primary hover:underline">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            {{ t('features.auth.forgotPassword.backToLogin') }}
                        </router-link>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '../../stores/auth';
import { useFormValidation } from '../../composables/useFormValidation';
import { forgotPasswordSchema } from '../../schemas/auth';
import { Loader2, ArrowLeft } from 'lucide-vue-next';

// Shadcn Components
// @ts-ignore
import Card from '../../components/ui/card.vue';
// @ts-ignore
import CardHeader from '../../components/ui/card-header.vue';
// @ts-ignore
import CardTitle from '../../components/ui/card-title.vue';
// @ts-ignore
import CardDescription from '../../components/ui/card-description.vue';
// @ts-ignore
import CardContent from '../../components/ui/card-content.vue';
// @ts-ignore
import Button from '../../components/ui/button.vue';
// @ts-ignore
import Input from '../../components/ui/input.vue';
// @ts-ignore
import Label from '../../components/ui/label.vue';

const { t } = useI18n();
const authStore = useAuthStore();
const { errors, validateWithZod, clearErrors } = useFormValidation(forgotPasswordSchema);

const form = reactive({
    email: '',
});

const isValid = computed(() => {
    return !!form.email;
});

const message = ref('');
const messageType = ref('');
const loading = ref(false);

const handleSubmit = async () => {
    // Client-side validation first
    if (!validateWithZod(form)) {
        return;
    }

    loading.value = true;
    clearErrors();
    message.value = '';

    const result = await authStore.forgotPassword(form.email);

    if (result.success) {
        message.value = result.message || '';
        messageType.value = 'success';
    } else {
        message.value = result.message || '';
        messageType.value = 'error';
    }

    loading.value = false;
};
</script>

