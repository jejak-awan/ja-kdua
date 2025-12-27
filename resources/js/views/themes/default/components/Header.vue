<template>
    <header class="sticky top-0 z-40 bg-background/80 backdrop-blur-md border-b border-border">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <router-link to="/" class="flex items-center gap-2 group">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-lg group-hover:bg-indigo-700 transition-colors">
                        JA
                    </div>
                    <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-gray-900 to-gray-600">
                        CMS
                    </span>
                </router-link>

                <!-- Desktop Nav -->
                <nav class="hidden md:flex items-center gap-8">
                    <router-link 
                        v-for="item in navItems" 
                        :key="item.path" 
                        :to="item.path"
                        class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition-colors relative group py-2"
                        active-class="text-indigo-600"
                    >
                        {{ item.label }}
                        <span class="absolute bottom-0 left-0 w-full h-0.5 bg-indigo-600 scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></span>
                    </router-link>
                </nav>

                <!-- Actions -->
                <div class="hidden md:flex items-center gap-4">
                    <router-link 
                        to="/login"
                        class="px-4 py-2 text-sm font-medium text-indigo-600 hover:text-indigo-700 transition-colors"
                    >
                        Sign In
                    </router-link>
                    <router-link 
                        to="/register"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-full hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 hover:shadow-indigo-300 transform hover:-translate-y-0.5"
                    >
                        Get Started
                    </router-link>
                </div>

                <!-- Mobile Menu Button -->
                <button 
                    @click="isOpen = !isOpen"
                    class="md:hidden p-2 text-muted-foreground hover:bg-accent rounded-lg transition-colors"
                >
                    <svg v-if="!isOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="transform -translate-y-4 opacity-0"
            enter-to-class="transform translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="transform translate-y-0 opacity-100"
            leave-to-class="transform -translate-y-4 opacity-0"
        >
            <div v-if="isOpen" class="md:hidden bg-background border-b border-border absolute w-full left-0 top-16 shadow-xl">
                <div class="container mx-auto px-4 py-4 space-y-2">
                    <router-link 
                        v-for="item in navItems" 
                        :key="item.path" 
                        :to="item.path"
                        class="block px-4 py-2.5 text-base font-medium text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                        active-class="text-indigo-600 bg-indigo-50"
                        @click="isOpen = false"
                    >
                        {{ item.label }}
                    </router-link>
                    <div class="h-px bg-border my-4"></div>
                    <div class="flex flex-col gap-2">
                        <router-link 
                            to="/login"
                            class="w-full px-4 py-2.5 text-center text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-lg transition-colors"
                        >
                            Sign In
                        </router-link>
                        <router-link 
                            to="/register"
                            class="w-full px-4 py-2.5 text-center text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-md"
                        >
                            Get Started
                        </router-link>
                    </div>
                </div>
            </div>
        </transition>
    </header>
</template>

<script setup>
import { ref } from 'vue';

const isOpen = ref(false);

const navItems = [
    { label: 'Home', path: '/' },
    { label: 'Blog', path: '/blog' },
    { label: 'About', path: '/about' },
    { label: 'Contact', path: '/contact' },
];
</script>
