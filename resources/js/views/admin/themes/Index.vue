<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Themes Management</h1>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="theme in themes"
                :key="theme.id"
                class="bg-white shadow rounded-lg overflow-hidden"
                :class="{ 'ring-2 ring-indigo-500': theme.is_active }"
            >
                <div class="h-48 bg-gray-200 relative">
                    <img
                        v-if="theme.screenshot"
                        :src="theme.screenshot"
                        :alt="theme.name"
                        class="w-full h-full object-cover"
                    />
                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div v-if="theme.is_active" class="absolute top-2 right-2">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-500 text-white">
                            Active
                        </span>
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ theme.name }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ theme.version || '1.0.0' }}</p>
                    <p v-if="theme.description" class="text-sm text-gray-600 mt-2">{{ theme.description }}</p>
                    <div class="mt-4 flex items-center space-x-2">
                        <button
                            v-if="!theme.is_active"
                            @click="activateTheme(theme)"
                            class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700"
                        >
                            Activate
                        </button>
                        <button
                            @click="openSettings(theme)"
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50"
                        >
                            Settings
                        </button>
                        <button
                            @click="openCustomCSS(theme)"
                            class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50"
                        >
                            CSS
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Modal -->
        <ThemeSettingsModal
            v-if="showSettingsModal"
            @close="showSettingsModal = false"
            @saved="handleSettingsSaved"
            :theme="selectedTheme"
        />

        <!-- Custom CSS Modal -->
        <CustomCSSModal
            v-if="showCustomCSSModal"
            @close="showCustomCSSModal = false"
            @saved="handleCSSSaved"
            :theme="selectedTheme"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../../services/api';
import ThemeSettingsModal from '../../../components/themes/ThemeSettingsModal.vue';
import CustomCSSModal from '../../../components/themes/CustomCSSModal.vue';

const themes = ref([]);
const showSettingsModal = ref(false);
const showCustomCSSModal = ref(false);
const selectedTheme = ref(null);

const fetchThemes = async () => {
    try {
        const response = await api.get('/admin/cms/themes');
        themes.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Failed to fetch themes:', error);
    }
};

const activateTheme = async (theme) => {
    if (!confirm(`Are you sure you want to activate theme "${theme.name}"?`)) {
        return;
    }

    try {
        await api.post(`/admin/cms/themes/${theme.id}/activate`);
        await fetchThemes();
        alert('Theme activated successfully');
    } catch (error) {
        console.error('Failed to activate theme:', error);
        alert(error.response?.data?.message || 'Failed to activate theme');
    }
};

const openSettings = (theme) => {
    selectedTheme.value = theme;
    showSettingsModal.value = true;
};

const openCustomCSS = (theme) => {
    selectedTheme.value = theme;
    showCustomCSSModal.value = true;
};

const handleSettingsSaved = () => {
    fetchThemes();
    showSettingsModal.value = false;
};

const handleCSSSaved = () => {
    showCustomCSSModal.value = false;
};

onMounted(() => {
    fetchThemes();
});
</script>

