<template>
    <div>
        <!-- Performance Tab has custom layout with cache status -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column: Cache Status \u0026 Management -->
            <div class="bg-card border border-border rounded-lg p-6">
                <h3 class="text-base font-semibold text-foreground mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                    {{ $t('features.settings.cache.status') }}
                </h3>
                <div v-if="!cacheStatus" class="text-center py-8 text-muted-foreground text-sm">
                    {{ $t('features.settings.cache.loading') }}
                </div>
                <div v-else>
                    <!-- Status Grid -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-muted-foreground font-semibold">{{ $t('features.settings.cache.driver') }}</p>
                            <p class="text-sm font-bold text-foreground mt-1">{{ cacheStatus.driver }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-muted-foreground font-semibold">{{ $t('features.settings.cache.status') }}</p>
                            <p class="text-sm font-bold text-foreground mt-1">{{ cacheStatus.enabled ? $t('features.settings.enabled') : $t('features.settings.disabled') }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-muted-foreground font-semibold">{{ $t('features.settings.cache.keys') }}</p>
                            <p class="text-sm font-bold text-foreground mt-1">{{ cacheStatus.keys }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-muted-foreground font-semibold">{{ $t('features.settings.cache.size') }}</p>
                            <p class="text-sm font-bold text-foreground mt-1">{{ cacheStatus.size }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="grid grid-cols-2 gap-4">
                        <button
                            type="button"
                            @click="$emit('clear-cache')"
                            :disabled="clearingCache"
                            class="flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium border border-red-200 bg-red-50 text-red-700 rounded-md hover:bg-red-100 disabled:opacity-50 transition-colors"
                        >
                            <svg v-if="clearingCache" class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                            {{ clearingCache ? $t('features.settings.cache.clearing') : $t('features.settings.cache.clear') }}
                        </button>
                        <button
                            type="button"
                            @click="$emit('warm-cache')"
                            :disabled="warmingCache"
                            class="flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-md hover:bg-primary/90 disabled:opacity-50 transition-colors shadow-sm"
                        >
                            <svg v-if="warmingCache" class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.1.24-2.14.7-3.09C7.07 13.1 8 13.9 8.5 14.5z"/></svg>
                            {{ warmingCache ? $t('features.settings.cache.warming') : $t('features.settings.cache.warm') }}
                        </button>
                    </div>

                    <!-- Redis Info -->
                    <div v-if="formData.cache_driver === 'redis'" class="mt-6 p-4 rounded-md border border-blue-200 bg-blue-50 text-blue-800">
                        <div class="flex items-start gap-3">
                            <div class="text-xl">ℹ️</div>
                            <div>
                                <h4 class="font-bold text-sm uppercase tracking-wider mb-1">{{ $t('features.settings.cache.redisInfo.title') }}</h4>
                                <p class="text-xs mb-2 leading-relaxed">{{ $t('features.settings.cache.redisInfo.description') }}</p>
                                <router-link to="/admin/redis" class="text-xs font-semibold underline hover:text-blue-900 inline-flex items-center gap-1">
                                    {{ $t('features.settings.cache.redisInfo.linkText') }}
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/></svg>
                                </router-link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Cache Configuration -->
            <div class="bg-card border border-border rounded-lg p-6 h-fit">
                <h3 class="text-base font-semibold text-foreground mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7h-9"/><path d="M14 17H5"/><circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/></svg>
                    {{ $t('features.settings.cache.configTitle') }}
                </h3>
                <div class="space-y-4">
                    <template v-for="setting in cacheSettings" :key="setting.id">
                        <div class="border-b border-border last:border-0 pb-4 last:pb-0">
                            <label class="block text-sm font-medium text-foreground mb-1">
                                {{ $t('features.settings.labels.' + setting.key) }}
                            </label>
                            <p v-if="setting.description" class="text-xs text-muted-foreground mb-2">
                                {{ $t('features.settings.descriptions.' + setting.key) }}
                            </p>

                            <!-- Cache Driver Dropdown -->
                            <select
                                v-if="setting.key === 'cache_driver'"
                                v-model="formData[setting.key]"
                                class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary text-sm"
                            >
                                <option value="file">File</option>
                                <option value="redis">Redis</option>
                                <option value="memcached">Memcached</option>
                                <option value="database">Database</option>
                            </select>

                            <!-- Enable Cache Toggle -->
                            <div v-else-if="setting.key === 'enable_cache'">
                                <label class="flex items-center cursor-pointer">
                                    <div class="relative">
                                        <input v-model="formData[setting.key]" type="checkbox" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-600 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all after:shadow-md peer-checked:bg-emerald-500"></div>
                                    </div>
                                    <span class="ml-3 text-sm text-foreground">
                                        {{ formData[setting.key] ? $t('features.settings.enabled') : $t('features.settings.disabled') }}
                                    </span>
                                </label>
                            </div>

                            <!-- Cache TTL -->
                            <input
                                v-else
                                v-model.number="formData[setting.key]"
                                type="number"
                                class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary text-sm"
                            >
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Other Performance Settings -->
        <div class="mt-6 bg-card border border-border rounded-lg p-6">
            <h3 class="text-base font-semibold text-foreground mb-4">{{ $t('features.settings.tabs.performance') }} Settings</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- CDN Settings (Custom Layout) -->
                <div class="col-span-1 md:col-span-2 border-b border-border pb-6 mb-6">
                     <h4 class="text-sm font-semibold text-foreground mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8z"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        CDN Configuration
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Enable CDN (Left) -->
                        <div v-if="cdnEnabledSetting">
                            <label class="block text-sm font-medium text-foreground mb-1">
                                {{ $t('features.settings.labels.' + cdnEnabledSetting.key) }}
                            </label>
                            <p v-if="cdnEnabledSetting.description" class="text-xs text-muted-foreground mb-3">
                                {{ $t('features.settings.descriptions.' + cdnEnabledSetting.key) }}
                            </p>
                            <label class="flex items-center cursor-pointer">
                                <div class="relative">
                                    <input v-model="formData[cdnEnabledSetting.key]" type="checkbox" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-600 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all after:shadow-md peer-checked:bg-emerald-500"></div>
                                </div>
                                <span class="ml-3 text-sm text-foreground">
                                    {{ formData[cdnEnabledSetting.key] ? $t('features.settings.enabled') : $t('features.settings.disabled') }}
                                </span>
                            </label>
                        </div>
                        
                        <!-- CDN Provider -->
                        <div v-if="cdnPresetSetting">
                            <label class="block text-sm font-medium text-foreground mb-1" :class="{'opacity-50': !formData.enable_cdn}">
                                CDN Provider
                            </label>
                            <p class="text-xs text-muted-foreground mb-2" :class="{'opacity-50': !formData.enable_cdn}">
                                Select your CDN provider for optimized configuration
                            </p>
                            <select
                                v-model="formData[cdnPresetSetting.key]"
                                :disabled="!formData.enable_cdn"
                                class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <option value="custom">Custom / Other</option>
                                <option value="bunny">BunnyCDN</option>
                                <option value="cloudflare">Cloudflare</option>
                                <option value="aws">AWS CloudFront</option>
                            </select>
                        </div>

                        <!-- CDN URL (Full Width) -->
                        <div v-if="cdnUrlSetting" class="md:col-span-2">
                            <label class="block text-sm font-medium text-foreground mb-1" :class="{'opacity-50': !formData.enable_cdn}">
                                {{ $t('features.settings.labels.' + cdnUrlSetting.key) }}
                            </label>
                            <p v-if="cdnUrlSetting.description" class="text-xs text-muted-foreground mb-2" :class="{'opacity-50': !formData.enable_cdn}">
                                {{ $t('features.settings.descriptions.' + cdnUrlSetting.key) }}
                            </p>
                            <input
                                v-model="formData[cdnUrlSetting.key]"
                                type="text"
                                :disabled="!formData.enable_cdn"
                                class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                :placeholder="!formData.enable_cdn ? 'Enable CDN to configure URL' : 'https://cdn.example.com'"
                            >
                             <p v-if="formData.cdn_preset === 'bunny'" class="text-xs text-blue-600 mt-1">
                                Tip: Use your BunnyCDN Pull Zone URL (e.g. https://my-zone.b-cdn.net)
                            </p>
                        </div>

                        <!-- Included Dirs -->
                        <div v-if="cdnIncludedDirsSetting">
                            <label class="block text-sm font-medium text-foreground mb-1" :class="{'opacity-50': !formData.enable_cdn}">
                                Included Directories
                            </label>
                            <p class="text-xs text-muted-foreground mb-2" :class="{'opacity-50': !formData.enable_cdn}">
                                Comma-separated list of directories to serve via CDN
                            </p>
                            <input
                                v-model="formData[cdnIncludedDirsSetting.key]"
                                type="text"
                                :disabled="!formData.enable_cdn"
                                class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                        </div>

                         <!-- Excluded Extensions -->
                        <div v-if="cdnExcludedExtsSetting">
                            <label class="block text-sm font-medium text-foreground mb-1" :class="{'opacity-50': !formData.enable_cdn}">
                                Excluded Extensions
                            </label>
                            <p class="text-xs text-muted-foreground mb-2" :class="{'opacity-50': !formData.enable_cdn}">
                                File extensions to exclude from CDN
                            </p>
                            <input
                                v-model="formData[cdnExcludedExtsSetting.key]"
                                type="text"
                                :disabled="!formData.enable_cdn"
                                class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                        </div>
                    </div>
                </div>

                <template v-for="setting in otherSettings" :key="setting.id">
                    <div class="border-b border-border last:border-0 pb-4 md:border-0 md:pb-0">
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.settings.labels.' + setting.key) }}
                        </label>
                        <p v-if="setting.description" class="text-xs text-muted-foreground mb-2">
                            {{ $t('features.settings.descriptions.' + setting.key) }}
                        </p>

                        <input
                            v-if="setting.type === 'string'"
                            v-model="formData[setting.key]"
                            type="text"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary text-sm"
                        >
                        <input
                            v-else-if="setting.type === 'integer'"
                            v-model.number="formData[setting.key]"
                            type="number"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary text-sm"
                        >
                        <div v-else-if="setting.type === 'boolean'">
                            <label class="flex items-center cursor-pointer">
                                <div class="relative">
                                    <input v-model="formData[setting.key]" type="checkbox" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-600 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all after:shadow-md peer-checked:bg-emerald-500"></div>
                                </div>
                                <span class="ml-3 text-sm text-foreground">
                                    {{ formData[setting.key] ? $t('features.settings.enabled') : $t('features.settings.disabled') }}
                                </span>
                            </label>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    settings: {
        type: Array,
        required: true
    },
    formData: {
        type: Object,
        required: true
    },
    cacheStatus: {
        type: Object,
        default: null
    },
    clearingCache: {
        type: Boolean,
        default: false
    },
    warmingCache: {
        type: Boolean,
        default: false
    }
})

defineEmits(['clear-cache', 'warm-cache'])

const performanceSettings = computed(() => {
    return props.settings.filter(s => s && s.group === 'performance')
})

const cacheSettings = computed(() => {
    return performanceSettings.value.filter(s => 
        ['cache_driver', 'enable_cache', 'cache_ttl'].includes(s.key)
    )
})

const cdnEnabledSetting = computed(() => performanceSettings.value.find(s => s.key === 'enable_cdn'))
const cdnUrlSetting = computed(() => performanceSettings.value.find(s => s.key === 'cdn_url'))
const cdnPresetSetting = computed(() => performanceSettings.value.find(s => s.key === 'cdn_preset'))
const cdnIncludedDirsSetting = computed(() => performanceSettings.value.find(s => s.key === 'cdn_included_dirs'))
const cdnExcludedExtsSetting = computed(() => performanceSettings.value.find(s => s.key === 'cdn_excluded_extensions'))


const otherSettings = computed(() => {
    return performanceSettings.value.filter(s => 
        !['cache_driver', 'enable_cache', 'cache_ttl', 'enable_cdn', 'cdn_url', 'cdn_preset', 'cdn_included_dirs', 'cdn_excluded_extensions'].includes(s.key)
    )
})
</script>
