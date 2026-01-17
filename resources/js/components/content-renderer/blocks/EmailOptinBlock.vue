<template>
    <section 
        :class="containerClasses"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div class="container mx-auto px-6">
            <div 
                :class="[
                    'max-w-4xl mx-auto transition-all duration-300 overflow-hidden',
                    style === 'card' ? 'bg-card border rounded-3xl shadow-xl p-8 md:p-12' : '',
                    style === 'gradient' ? 'bg-gradient-to-br from-primary to-primary/70 text-white rounded-3xl p-8 md:p-12' : '',
                    style === 'minimal' ? 'bg-transparent' : ''
                ]"
            >
                <div :class="contentWrapperClasses">
                    
                    <!-- Image -->
                    <div v-if="image" class="flex-1 md:max-w-xs xl:max-w-sm shrink-0">
                        <img 
                            :src="image" 
                            alt="Newsletter" 
                            class="w-full h-full object-cover rounded-2xl shadow-lg"
                        />
                    </div>
                    
                    <!-- Content -->
                    <div 
                        :class="[
                            'flex-1 min-w-0',
                            alignment === 'center' ? 'text-center' : alignment === 'left' ? 'text-left' : 'text-right'
                        ]"
                    >
                        <!-- Header -->
                        <div class="mb-8">
                            <div 
                                v-if="style !== 'minimal' && !image"
                                :class="[
                                    'w-16 h-16 rounded-2xl flex items-center justify-center mb-6',
                                    style === 'gradient' ? 'bg-white/20' : 'bg-primary/10 text-primary',
                                    alignment === 'center' ? 'mx-auto' : 'mr-auto',
                                    alignment === 'right' ? 'ml-auto mr-0' : ''
                                ]"
                            >
                                <Mail class="w-8 h-8" />
                            </div>
                            <h2 class="text-2xl md:text-3xl font-extrabold mb-3 tracking-tight">{{ title }}</h2>
                            <p :class="['max-w-md', alignment === 'center' ? 'mx-auto' : '', alignment === 'right' && 'ml-auto', style === 'gradient' ? 'opacity-90' : 'text-muted-foreground']">
                                {{ description }}
                            </p>
                        </div>
                        
                        <!-- Form -->
                        <div v-if="status !== 'success'">
                            <form @submit.prevent="handleSubmit" :class="['flex gap-3', layout === 'inline' ? 'flex-row' : 'flex-col']">
                                <input 
                                    v-if="show_name"
                                    v-model="name"
                                    type="text"
                                    :placeholder="name_placeholder"
                                    :class="[
                                        'h-12 px-4 rounded-xl border text-sm transition-all focus:outline-none focus:ring-2 focus:ring-primary',
                                        style === 'gradient' ? 'bg-white/10 border-white/20 text-white placeholder:text-white/60' : 'bg-background text-foreground border-input placeholder:text-muted-foreground'
                                    ]"
                                />
                                <input 
                                    v-model="email"
                                    type="email"
                                    :placeholder="placeholder"
                                    :class="[
                                        'h-12 px-4 rounded-xl border text-sm transition-all focus:outline-none focus:ring-2 focus:ring-primary flex-1',
                                        style === 'gradient' ? 'bg-white/10 border-white/20 text-white placeholder:text-white/60' : 'bg-background text-foreground border-input placeholder:text-muted-foreground'
                                    ]"
                                    @focus="resetForm"
                                />
                                <button 
                                    type="submit"
                                    :disabled="status === 'loading'"
                                    :class="[
                                        'h-12 px-8 font-bold rounded-xl transition-all flex items-center justify-center gap-2 shadow-lg active:scale-95',
                                        style === 'gradient' ? 'bg-white text-primary hover:bg-white/90' : 'bg-primary text-primary-foreground hover:opacity-90',
                                        layout === 'inline' ? '' : 'w-full'
                                    ]"
                                >
                                    <Loader2 v-if="status === 'loading'" class="w-4 h-4 animate-spin" />
                                    <span>{{ button_text }}</span>
                                </button>
                            </form>
                            
                            <!-- Error -->
                            <div v-if="status === 'error'" :class="['flex items-center gap-2 mt-3 text-sm', style === 'gradient' ? 'text-red-200' : 'text-destructive', alignment === 'center' ? 'justify-center' : alignment === 'right' ? 'justify-end' : '']">
                                <AlertCircle class="w-4 h-4" />
                                {{ errorMessage }}
                            </div>
                            
                            <!-- Provider Info (Builder only visible or subtle) -->
                            <p v-if="redirect_url" class="text-[10px] mt-2 opacity-50">
                                Will redirect to {{ redirect_url }}
                            </p>
                        </div>
                        
                        <!-- Success -->
                        <div v-else class="py-6 animate-in fade-in zoom-in">
                            <div 
                                :class="[
                                    'w-16 h-16 rounded-full flex items-center justify-center mb-4',
                                    style === 'gradient' ? 'bg-white/20' : 'bg-primary/10 text-primary',
                                    alignment === 'center' ? 'mx-auto' : 'mr-auto',
                                    alignment === 'right' ? 'ml-auto mr-0' : ''
                                ]"
                            >
                                <Check class="w-8 h-8" />
                            </div>
                            <p :class="['text-lg font-semibold', style === 'gradient' ? '' : 'text-foreground']">{{ success_message }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Mail, Loader2, Check, AlertCircle } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    title: { type: String, default: 'Subscribe to Our Newsletter' },
    description: { type: String, default: 'Get the latest updates and news delivered to your inbox.' },
    button_text: { type: String, default: 'Subscribe' },
    success_message: { type: String, default: 'Thank you for subscribing!' },
    placeholder: { type: String, default: 'Enter your email' },
    show_name: { type: Boolean, default: false },
    name_placeholder: { type: String, default: 'Your name' },
    provider: { type: String, default: 'mailchimp' },
    list_id: { type: String, default: '' },
    redirect_url: { type: String, default: '' },
    image: { type: String, default: '' },
    image_position: { type: String, default: 'top' },
    layout: { type: String, default: 'stacked' },
    style: { type: String, default: 'card' },
    alignment: { type: String, default: 'center' },
    padding: { type: String, default: 'py-16' },
    bgColor: { type: String, default: '' }
});

const email = ref('');
const name = ref('');
const status = ref('idle'); // idle, loading, success, error
const errorMessage = ref('');

const handleSubmit = async () => {
    if (!email.value || !email.value.includes('@')) {
        status.value = 'error';
        errorMessage.value = 'Please enter a valid email address';
        return;
    }
    
    status.value = 'loading';
    
    // Simulate API call
    setTimeout(() => {
        status.value = 'success';
        email.value = '';
        name.value = '';
        
        if (props.redirect_url) {
            window.location.href = props.redirect_url;
        }
    }, 1500);
};

const resetForm = () => {
    status.value = 'idle';
    errorMessage.value = '';
};

const containerClasses = computed(() => {
    return ['transition-all duration-500', props.padding].filter(Boolean);
});

const contentWrapperClasses = computed(() => {
    const base = 'flex flex-col gap-8';
    if (!props.image) return base;
    
    const positions = {
        left: 'md:flex-row items-center',
        right: 'md:flex-row-reverse items-center',
        top: 'flex-col',
        bottom: 'flex-col-reverse'
    };
    return [base, positions[props.image_position] || 'flex-col'].join(' ');
});
</script>
