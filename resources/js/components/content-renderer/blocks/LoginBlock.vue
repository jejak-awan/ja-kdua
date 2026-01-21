<template>
    <section :class="containerClasses">
        <div :class="cardClasses">
            <div class="mb-8" :class="headerAlignClass">
                <h2 v-if="title" class="text-2xl font-bold tracking-tight">{{ title }}</h2>
            </div>

            <form @submit.prevent="handleLogin" class="space-y-4">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-muted-foreground ml-1">{{ usernameLabel || 'Email Address' }}</label>
                    <input 
                        type="email" 
                        class="w-full h-11 rounded-xl border bg-background px-4 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 transition-all"
                        placeholder="name@example.com"
                    />
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between px-1">
                        <label class="text-xs font-bold text-muted-foreground">{{ passwordLabel || 'Password' }}</label>
                        <a v-if="showForgotPassword" href="#" class="text-[10px] font-bold text-primary hover:underline">Forgot password?</a>
                    </div>
                    <input 
                        type="password" 
                        class="w-full h-11 rounded-xl border bg-background px-4 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 transition-all"
                        placeholder="••••••••"
                    />
                </div>
                <button 
                    type="submit" 
                    class="w-full h-11 bg-primary text-primary-foreground font-bold rounded-xl hover:opacity-90 transition-all active:scale-[0.98] shadow-lg shadow-primary/20 flex items-center justify-center gap-2 mt-6"
                >
                    width="100%"
                >
                    {{ buttonText || 'Login' }}
                </button>
            </form>

            <div v-if="showRegistration" class="mt-8 pt-6 border-t border-border text-center">
                <p class="text-xs text-muted-foreground">
                    Don't have an account? 
                    <a href="#" class="text-primary font-bold hover:underline">Register now</a>
                </p>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    title: { type: String, default: '' },
    usernameLabel: { type: String, default: 'Email Address' },
    passwordLabel: { type: String, default: 'Password' },
    buttonText: { type: String, default: 'Login' },
    showForgotPassword: { type: Boolean, default: true },
    showRegistration: { type: Boolean, default: true },
    style: { type: String, default: 'bg-card border shadow-sm p-8 rounded-2xl' },
    padding: { type: String, default: '' },
    width: { type: String, default: 'max-w-md' },
    alignment: { type: String, default: 'text-left' }
});

const containerClasses = computed(() => {
    return [
        'transition-all duration-500',
        props.padding || '',
        props.width || 'max-w-md',
        props.alignment || 'text-left',
        'mx-auto'
    ].filter(Boolean);
});

const cardClasses = computed(() => {
    return ['transition-all duration-500', props.style || ''].filter(Boolean);
});

const headerAlignClass = computed(() => {
    if (props.alignment === 'text-center') return 'text-center';
    if (props.alignment === 'text-right') return 'text-right';
    return 'text-left';
});

const handleLogin = () => {
    // Simulated
};
</script>
