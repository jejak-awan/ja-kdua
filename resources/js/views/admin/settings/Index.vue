<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.settings.title') }}</h1>
            <p class="mt-1 text-sm text-muted-foreground">{{ $t('features.settings.description') }}</p>
        </div>

        <div v-if="loading" class="bg-card border border-border rounded-lg p-12 text-center">
            <p class="text-muted-foreground">{{ $t('features.settings.loading') }}</p>
        </div>

        <div v-else class="bg-card border border-border rounded-lg">
            <!-- Shadcn Tabs -->
            <Tabs v-model="activeTab" class="w-full">
                <div class="p-4 border-b border-border">
                    <TabsList class="flex-wrap">
                        <TabsTrigger v-for="tab in tabs" :key="tab.id" :value="tab.id">
                            {{ $t('features.settings.tabs.' + tab.id) }}
                        </TabsTrigger>
                    </TabsList>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <form @submit.prevent="handleSubmit" class="space-y-6">
                    <div v-if="currentSettings.length === 0" class="text-center py-8">
                        <p class="text-muted-foreground">{{ $t('features.settings.noSettings') }}</p>
                    </div>

                    <!-- Grouped Security Settings -->
                    <div v-else-if="activeTab === 'security'" class="space-y-8">
                        <div
                            v-for="group in securitySettingsGrouped"
                            :key="group.id"
                            class="bg-muted/20 rounded-xl border border-border p-5"
                        >
                            <!-- Group Header -->
                            <div class="flex items-center gap-3 mb-4 pb-3 border-b border-border">
                                <div class="p-2 rounded-lg bg-primary/10">
                                    <component :is="group.icon" class="w-5 h-5 text-primary" />
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-foreground">{{ group.title }}</h3>
                                    <p class="text-xs text-muted-foreground">{{ group.description }}</p>
                                </div>
                            </div>
                            
                            <!-- Group Settings Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div
                                    v-for="setting in group.settings"
                                    :key="setting.id"
                                    class="p-4 bg-card rounded-lg border border-border"
                                >
                                    <label class="block text-sm font-medium text-foreground mb-1">
                                        {{ $t('features.settings.labels.' + setting.key) }}
                                    </label>
                                    <p v-if="setting.description" class="text-xs text-muted-foreground mb-2">
                                        {{ $t('features.settings.descriptions.' + setting.key) }}
                                    </p>

                                    <!-- Security Dropdown -->
                                    <select
                                        v-if="setting.type === 'integer' && isSecurityDropdown(setting.key)"
                                        v-model.number="formData[setting.key]"
                                        class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary"
                                    >
                                        <option
                                            v-for="option in getSecurityOptions(setting.key)"
                                            :key="option.value"
                                            :value="option.value"
                                        >
                                            {{ option.label }}
                                        </option>
                                    </select>

                                    <!-- Number Input -->
                                    <input
                                        v-else-if="setting.type === 'integer'"
                                        v-model.number="formData[setting.key]"
                                        type="number"
                                        class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary"
                                    >

                                    <!-- Boolean Toggle -->
                                    <div v-else-if="setting.type === 'boolean'" class="mt-1">
                                        <label class="flex items-center cursor-pointer">
                                            <div class="relative">
                                                <input
                                                    v-model="formData[setting.key]"
                                                    type="checkbox"
                                                    class="sr-only peer"
                                                >
                                                <div class="w-11 h-6 bg-gray-600 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all after:shadow-md peer-checked:bg-emerald-500"></div>
                                            </div>
                                            <span class="ml-3 text-sm text-foreground">
                                                {{ formData[setting.key] ? $t('features.settings.enabled') : $t('features.settings.disabled') }}
                                            </span>
                                        </label>
                                    </div>

                                    <!-- Current Value -->
                                    <p v-if="setting.value !== undefined && setting.value !== null" class="mt-2 text-xs text-muted-foreground">
                                        {{ $t('features.settings.current') }}: {{ formatValue(setting.value, setting.type) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Settings -->
                    <div v-else-if="activeTab === 'performance'" class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Left Column: Cache Management (Priority) -->
                            <div class="bg-card border border-border rounded-lg overflow-hidden h-fit flex flex-col">
                                <div class="px-6 py-4 border-b border-border flex items-center justify-between bg-muted/50">
                                    <h3 class="text-base font-semibold text-foreground flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-zap"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                        {{ $t('features.settings.cache.managementTitle') }}
                                    </h3>
                                    <button
                                        type="button"
                                        @click="getCacheStatus"
                                        :disabled="loadingCacheStatus"
                                        class="text-xs text-primary hover:text-primary/80 transition-colors flex items-center gap-1"
                                    >
                                        <svg v-if="loadingCacheStatus" class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        {{ loadingCacheStatus ? $t('features.settings.loading') : $t('features.settings.emailTest.refresh') }}
                                    </button>
                                </div>

                                <div class="p-6 space-y-6 flex-1">
                                    <!-- Status Cards (Compact Row) -->
                                    <div v-if="cacheStatus" class="grid grid-cols-3 gap-4">
                                        <div class="p-3 bg-muted/30 rounded-md border border-border">
                                            <p class="text-[10px] uppercase tracking-wider text-muted-foreground font-semibold">{{ $t('features.settings.cache.driver') }}</p>
                                            <p class="text-sm font-bold text-foreground capitalize mt-1 truncate" :title="cacheStatus.driver">{{ cacheStatus.driver }}</p>
                                        </div>
                                        <div class="p-3 bg-muted/30 rounded-md border border-border">
                                            <p class="text-[10px] uppercase tracking-wider text-muted-foreground font-semibold">{{ $t('features.settings.cache.status') }}</p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="relative flex h-2 w-2">
                                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                                  <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                                </span>
                                                <p class="text-sm font-bold text-green-600">{{ cacheStatus.status }}</p>
                                            </div>
                                        </div>
                                        <div class="p-3 bg-muted/30 rounded-md border border-border">
                                            <p class="text-[10px] uppercase tracking-wider text-muted-foreground font-semibold">{{ $t('features.settings.cache.size') }}</p>
                                            <p class="text-sm font-bold text-foreground mt-1">{{ cacheStatus.size }}</p>
                                        </div>
                                    </div>

                                    <!-- Actions (Side by side) -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <button
                                            type="button"
                                            @click="clearSystemCache"
                                            :disabled="clearingCache"
                                            class="flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium border border-red-200 bg-red-50 text-red-700 rounded-md hover:bg-red-100 disabled:opacity-50 transition-colors"
                                        >
                                            <svg v-if="clearingCache" class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                            {{ clearingCache ? $t('features.settings.cache.clearing') : $t('features.settings.cache.clear') }}
                                        </button>
                                        <button
                                            type="button"
                                            @click="warmSystemCache"
                                            :disabled="warmingCache"
                                            class="flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-md hover:bg-primary/90 disabled:opacity-50 transition-colors shadow-sm"
                                        >
                                            <svg v-if="warmingCache" class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-flame"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.1.24-2.14.7-3.09C7.07 13.1 8 13.9 8.5 14.5z"/></svg>
                                            {{ warmingCache ? $t('features.settings.cache.warming') : $t('features.settings.cache.warm') }}
                                        </button>
                                    </div>

                                    <!-- Redis Info Card -->
                                    <div v-if="formData.cache_driver === 'redis'" class="mt-6 p-4 rounded-md border border-blue-200 bg-blue-50 text-blue-800 transition-all duration-300">
                                        <div class="flex items-start gap-3">
                                            <div class="text-xl">ℹ️</div>
                                            <div>
                                                <h4 class="font-bold text-sm uppercase tracking-wider mb-1">{{ $t('features.settings.cache.redisInfo.title') }}</h4>
                                                <p class="text-xs mb-2 leading-relaxed">
                                                    {{ $t('features.settings.cache.redisInfo.description') }}
                                                </p>
                                                <router-link to="/admin/redis" class="text-xs font-semibold underline hover:text-blue-900 inline-flex items-center gap-1">
                                                    {{ $t('features.settings.cache.redisInfo.linkText') }}
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-external-link"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/></svg>
                                                </router-link>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Cache Configuration -->
                            <div class="bg-card border border-border rounded-lg p-6 h-fit">
                                <h3 class="text-base font-semibold text-foreground mb-4 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings-2"><path d="M20 7h-9"/><path d="M14 17H5"/><circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/></svg>
                                    {{ $t('features.settings.cache.configTitle') }}
                                </h3>
                                <div class="space-y-4">
                                    <template v-for="setting in currentSettings" :key="setting.id">
                                        <!-- Only Driver, Enable, TTL -->
                                        <div v-if="['cache_driver', 'enable_cache', 'cache_ttl'].includes(setting.key)" class="border-b border-border last:border-0 pb-4 last:pb-0">
                                            <label class="block text-sm font-medium text-foreground mb-1">
                                                {{ $t('features.settings.labels.' + setting.key) }}
                                            </label>
                                            <p v-if="setting.description" class="text-xs text-muted-foreground mb-2">
                                                {{ $t('features.settings.descriptions.' + setting.key) }}
                                            </p>

                                            <!-- Cache Driver Selector -->
                                            <div v-if="setting.key === 'cache_driver'">
                                                <select
                                                    v-model="formData[setting.key]"
                                                    class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary text-sm"
                                                >
                                                    <option value="file">{{ $t('features.settings.cache.drivers.file') }}</option>
                                                    <option value="redis">{{ $t('features.settings.cache.drivers.redis') }}</option>
                                                    <option value="database">{{ $t('features.settings.cache.drivers.database') }}</option>
                                                    <option value="array">{{ $t('features.settings.cache.drivers.array') }}</option>
                                                </select>
                                            </div>

                                            <!-- Generic Inputs for others -->
                                            <div v-else>
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
                                                <div v-else-if="setting.type === 'boolean'" class="mt-1">
                                                    <label class="flex items-center cursor-pointer">
                                                        <input
                                                            v-model="formData[setting.key]"
                                                            type="checkbox"
                                                            class="h-4 w-4 text-primary focus:ring-primary border-input rounded cursor-pointer"
                                                        >
                                                        <span class="ml-2 text-sm text-foreground">
                                                            {{ formData[setting.key] ? $t('features.settings.enabled') : $t('features.settings.disabled') }}
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom: Optimization & Media (Other Performance Settings) -->
                        <div class="bg-card border border-border rounded-lg p-6">
                            <h3 class="text-base font-semibold text-foreground mb-4">{{ $t('features.settings.tabs.performance') }} Settings</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <template v-for="setting in currentSettings" :key="setting.id + '_opt'">
                                    <!-- Render everything NOT in the cache specific list -->
                                    <div v-if="!['cache_driver', 'enable_cache', 'cache_ttl'].includes(setting.key)" class="border-b border-border last:border-0 pb-4 md:border-0 md:pb-0">
                                         <label class="block text-sm font-medium text-foreground mb-1">
                                            {{ $t('features.settings.labels.' + setting.key) }}
                                        </label>
                                        <p v-if="setting.description" class="text-xs text-muted-foreground mb-2">
                                            {{ $t('features.settings.descriptions.' + setting.key) }}
                                        </p>
                                        
                                        <!-- Generic Inputs -->
                                        <div>
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
                                            <div v-else-if="setting.type === 'boolean'" class="mt-1">
                                                <label class="flex items-center cursor-pointer">
                                                    <input
                                                        v-model="formData[setting.key]"
                                                        type="checkbox"
                                                        class="h-4 w-4 text-primary focus:ring-primary border-input rounded cursor-pointer"
                                                    >
                                                    <span class="ml-2 text-sm text-foreground">
                                                        {{ formData[setting.key] ? $t('features.settings.enabled') : $t('features.settings.disabled') }}
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Default Layout for Other Tabs -->
                    <div v-else class="space-y-6">
                        <div
                            v-for="setting in currentSettings"
                            :key="setting.id"
                            class="border-b border-border pb-6 last:border-b-0 last:pb-0"
                        >
                            <label class="block text-sm font-medium text-foreground mb-1">
                                {{ $t('features.settings.labels.' + setting.key) }}
                            </label>
                            <p v-if="setting.description" class="text-xs text-muted-foreground mb-2">
                                {{ $t('features.settings.descriptions.' + setting.key) }}
                            </p>

                            <!-- String Input -->
                            <input
                                v-if="setting.type === 'string'"
                                v-model="formData[setting.key]"
                                type="text"
                                class="mt-1 w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >

                            <!-- Number Input -->
                            <input
                                v-else-if="setting.type === 'integer'"
                                v-model.number="formData[setting.key]"
                                type="number"
                                class="mt-1 w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >

                            <!-- Boolean Checkbox -->
                            <div v-else-if="setting.type === 'boolean'" class="mt-1">
                                <label class="flex items-center">
                                    <input
                                        v-model="formData[setting.key]"
                                        type="checkbox"
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-input rounded"
                                    >
                                    <span class="ml-2 text-sm text-foreground">
                                        {{ formData[setting.key] ? $t('features.settings.enabled') : $t('features.settings.disabled') }}
                                    </span>
                                </label>
                            </div>

                            <!-- Textarea -->
                            <textarea
                                v-else-if="setting.type === 'text'"
                                v-model="formData[setting.key]"
                                rows="4"
                                class="mt-1 w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            />

                            <!-- JSON Editor -->
                            <div v-else-if="setting.type === 'json'" class="mt-1">
                                <textarea
                                    v-model="formData[setting.key]"
                                    rows="6"
                                    class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm"
                                    :placeholder="$t('features.settings.jsonPlaceholder')"
                                />
                                <p class="mt-1 text-xs text-muted-foreground">{{ $t('features.settings.jsonPlaceholder') }}</p>
                            </div>

                            <!-- Current Value Display -->
                            <p v-if="setting.value !== undefined && setting.value !== null" class="mt-1 text-xs text-muted-foreground">
                                {{ $t('features.settings.current') }}: {{ formatValue(setting.value, setting.type) }}
                            </p>
                        </div>
                    </div>

                    <!-- Email Test Section (only for email tab) -->
                    <div v-if="activeTab === 'email'" class="mt-8 pt-8 border-t border-border">
                        <h3 class="text-lg font-medium text-foreground mb-4">{{ $t('features.settings.emailTest.title') }}</h3>
                        
                        <!-- Config Validation -->
                        <div class="mb-6 p-4 bg-muted rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-sm font-medium text-foreground">{{ $t('features.settings.emailTest.configStatus') }}</h4>
                                <button
                                    type="button"
                                    @click="validateEmailConfig"
                                    :disabled="validatingConfig"
                                    class="px-3 py-1 text-sm bg-card border border-input bg-card text-foreground rounded-md hover:bg-muted disabled:opacity-50"
                                >
                                    {{ validatingConfig ? $t('features.settings.emailTest.validating') : $t('features.settings.emailTest.validate') }}
                                </button>
                            </div>
                            <div v-if="configValidation" class="mt-2">
                                <div v-if="configValidation.valid" class="text-sm text-green-600">
                                    ✓ {{ $t('features.settings.emailTest.valid') }}
                                </div>
                                <div v-else class="text-sm text-red-600">
                                    ✗ {{ $t('features.settings.emailTest.invalid') }}
                                </div>
                                <div v-if="configValidation.errors && configValidation.errors.length > 0" class="mt-2">
                                    <p class="text-xs font-medium text-red-600 mb-1">{{ $t('features.settings.emailTest.errors') }}</p>
                                    <ul class="text-xs text-red-600 list-disc list-inside">
                                        <li v-for="error in configValidation.errors" :key="error">{{ error }}</li>
                                    </ul>
                                </div>
                                <div v-if="configValidation.warnings && configValidation.warnings.length > 0" class="mt-2">
                                    <p class="text-xs font-medium text-yellow-600 mb-1">{{ $t('features.settings.emailTest.warnings') }}</p>
                                    <ul class="text-xs text-yellow-600 list-disc list-inside">
                                        <li v-for="warning in configValidation.warnings" :key="warning">{{ warning }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- SMTP Connection Test -->
                        <div class="mb-6 p-4 bg-muted rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-sm font-medium text-foreground">{{ $t('features.settings.emailTest.smtpTest') }}</h4>
                                <button
                                    type="button"
                                    @click="testSmtpConnection"
                                    :disabled="testingConnection"
                                    class="px-3 py-1 text-sm bg-card border border-input bg-card text-foreground rounded-md hover:bg-muted disabled:opacity-50"
                                >
                                    {{ testingConnection ? $t('features.settings.emailTest.testing') : $t('features.settings.emailTest.testConnection') }}
                                </button>
                            </div>
                            <div v-if="connectionResult" class="mt-2">
                                <div v-if="connectionResult.connected" class="text-sm text-green-600">
                                    ✓ {{ $t('features.settings.emailTest.connected', { host: connectionResult.host, port: connectionResult.port }) }}
                                </div>
                                <div v-else class="text-sm text-red-600">
                                    ✗ {{ $t('features.settings.emailTest.failed') }}
                                </div>
                            </div>
                        </div>

                        <!-- Send Test Email -->
                        <div class="mb-6 p-4 bg-muted rounded-lg">
                            <h4 class="text-sm font-medium text-foreground mb-3">{{ $t('features.settings.emailTest.sendTest') }}</h4>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-foreground mb-1">{{ $t('features.settings.emailTest.recipient') }}</label>
                                    <input
                                        v-model="testEmail.to"
                                        type="email"
                                        :placeholder="$t('features.settings.emailTest.recipientPlaceholder')"
                                        class="w-full px-3 py-2 text-sm border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-foreground mb-1">{{ $t('features.settings.emailTest.subject') }}</label>
                                    <input
                                        v-model="testEmail.subject"
                                        type="text"
                                        :placeholder="$t('features.settings.emailTest.subjectPlaceholder')"
                                        class="w-full px-3 py-2 text-sm border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-foreground mb-1">{{ $t('features.settings.emailTest.message') }}</label>
                                    <textarea
                                        v-model="testEmail.message"
                                        rows="3"
                                        :placeholder="$t('features.settings.emailTest.messagePlaceholder')"
                                        class="w-full px-3 py-2 text-sm border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                    />
                                </div>
                                <button
                                    type="button"
                                    @click="sendTestEmail"
                                    :disabled="sendingTestEmail || !testEmail.to"
                                    class="w-full px-4 py-2 text-sm bg-primary text-primary-foreground rounded-md hover:bg-primary/80 disabled:opacity-50"
                                >
                                    {{ sendingTestEmail ? $t('features.settings.emailTest.sending') : $t('features.settings.emailTest.sendTest') }}
                                </button>
                                <div v-if="testEmailResult" class="mt-2 text-sm" :class="testEmailResult.success ? 'text-green-600' : 'text-red-600'">
                                    {{ testEmailResult.message }}
                                </div>
                            </div>
                        </div>

                        <!-- Queue Status -->
                        <div class="mb-6 p-4 bg-muted rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-sm font-medium text-foreground">{{ $t('features.settings.emailTest.queueStatus') }}</h4>
                                <button
                                    type="button"
                                    @click="getQueueStatus"
                                    :disabled="loadingQueueStatus"
                                    class="px-3 py-1 text-sm bg-card border border-input bg-card text-foreground rounded-md hover:bg-muted disabled:opacity-50"
                                >
                                    {{ loadingQueueStatus ? $t('features.settings.loading') : $t('features.settings.emailTest.refresh') }}
                                </button>
                            </div>
                            <div v-if="queueStatus" class="mt-2 text-sm text-muted-foreground">
                                <p>{{ $t('features.settings.emailTest.driver') }}: <span class="font-medium">{{ queueStatus.driver }}</span></p>
                                <p>{{ $t('features.settings.emailTest.pending') }}: <span class="font-medium">{{ queueStatus.pending_jobs }}</span></p>
                                <p>{{ $t('features.settings.emailTest.failedJobs') }}: <span class="font-medium">{{ queueStatus.failed_jobs }}</span></p>
                            </div>
                        </div>

                        <!-- Recent Email Logs -->
                        <div class="p-4 bg-muted rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-sm font-medium text-foreground">{{ $t('features.settings.emailTest.recentLogs') }}</h4>
                                <button
                                    type="button"
                                    @click="getRecentLogs"
                                    :disabled="loadingLogs"
                                    class="px-3 py-1 text-sm bg-card border border-input bg-card text-foreground rounded-md hover:bg-muted disabled:opacity-50"
                                >
                                    {{ loadingLogs ? $t('features.settings.loading') : $t('features.settings.emailTest.refresh') }}
                                </button>
                            </div>
                            <div v-if="emailLogs && emailLogs.length > 0" class="mt-2 space-y-2">
                                <div
                                    v-for="log in emailLogs"
                                    :key="log.sent_at"
                                    class="p-2 bg-card rounded border border-border text-xs"
                                >
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-medium text-foreground">{{ log.to }}</p>
                                            <p class="text-muted-foreground">{{ log.subject }}</p>
                                        </div>
                                        <span class="text-muted-foreground">{{ formatDate(log.sent_at) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div v-else-if="emailLogs && emailLogs.length === 0" class="mt-2 text-sm text-muted-foreground">
                                {{ $t('features.settings.emailTest.noLogs') }}
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-4 pt-6 border-t">
                        <button
                            type="button"
                            @click="resetForm"
                            class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-foreground hover:bg-muted"
                        >
                            {{ $t('features.settings.reset') }}
                        </button>
                        <button
                            type="submit"
                            :disabled="saving"
                            class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/80 disabled:opacity-50"
                        >
                            {{ saving ? $t('features.settings.saving') : $t('features.settings.save') }}
                        </button>
                    </div>
                </form>
            </div>
            </Tabs>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { parseResponse, parseSingleResponse, ensureArray } from '../../../utils/responseParser';
import Tabs from '../../../components/ui/tabs.vue';
import TabsList from '../../../components/ui/tabs-list.vue';
import TabsTrigger from '../../../components/ui/tabs-trigger.vue';

const { t } = useI18n();
const loading = ref(false);
const saving = ref(false);
const activeTab = ref('general');
const settings = ref([]);
const formData = ref({});

// Email testing state
const validatingConfig = ref(false);
const configValidation = ref(null);
const testingConnection = ref(false);
const connectionResult = ref(null);
const sendingTestEmail = ref(false);
const testEmailResult = ref(null);
const testEmail = ref({
    to: '',
    subject: '',
    message: '',
});
const loadingQueueStatus = ref(false);
const queueStatus = ref(null);
const loadingLogs = ref(false);
const emailLogs = ref([]);

// Cache Management State
const cacheStatus = ref(null);
const loadingCacheStatus = ref(false);
const clearingCache = ref(false);
const warmingCache = ref(false);

const tabs = [
    { id: 'general', label: 'General' },
    { id: 'email', label: 'Email' },
    { id: 'seo', label: 'SEO' },
    { id: 'security', label: 'Security' },
    { id: 'performance', label: 'Performance' },
    { id: 'media', label: 'Media' },
];

const currentSettings = computed(() => {
    if (!settings.value || !Array.isArray(settings.value)) {
        return [];
    }
    return settings.value.filter(s => s && s.group === activeTab.value);
});

// SVG Icon Components
const ShieldCheckIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>`
};

const ClockIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`
};

const LockIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>`
};

// Security settings grouped by category
const securitySettingsGrouped = computed(() => {
    const securitySettings = settings.value.filter(s => s && s.group === 'security');
    
    const groups = [
        {
            id: 'authentication',
            title: 'Autentikasi & Password',
            description: 'Pengaturan keamanan untuk autentikasi pengguna',
            icon: ShieldCheckIcon,
            keys: ['password_min_length', 'enable_2fa', 'require_email_verification', 'enable_registration'],
            settings: [],
        },
        {
            id: 'session',
            title: 'Manajemen Sesi',
            description: 'Kontrol durasi dan batasan sesi login',
            icon: ClockIcon,
            keys: ['session_lifetime', 'single_session_enabled', 'max_concurrent_sessions', 'log_retention_days'],
            settings: [],
        },
        {
            id: 'access',
            title: 'Pembatasan Akses',
            description: 'Pengaturan untuk membatasi akses yang mencurigakan',
            icon: LockIcon,
            keys: ['login_attempts_limit', 'block_duration_minutes'],
            settings: [],
        },
    ];
    
    // Distribute settings to groups
    groups.forEach(group => {
        group.settings = securitySettings.filter(s => group.keys.includes(s.key));
    });
    
    // Filter out empty groups
    return groups.filter(group => group.settings.length > 0);
});

const fetchSettings = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/settings');
        const { data } = parseResponse(response);
        settings.value = ensureArray(data);
        initializeFormData();
    } catch (error) {
        settings.value = [];
    } finally {
        loading.value = false;
    }
};

const initializeFormData = () => {
    formData.value = {};
    settings.value.forEach(setting => {
        let value = setting.value;
        
        // Cast value based on type
        if (setting.type === 'boolean') {
            value = value === '1' || value === 1 || value === 'true' || value === true;
        } else if (setting.type === 'integer') {
            value = value ? parseInt(value) : null;
        } else if (setting.type === 'json') {
            if (typeof value === 'string') {
                try {
                    value = JSON.parse(value);
                    value = JSON.stringify(value, null, 2);
                } catch {
                    // Invalid JSON, keep original string value
                }
            } else {
                value = JSON.stringify(value, null, 2);
            }
        }
        
        formData.value[setting.key] = value;
    });
};

const resetForm = () => {
    initializeFormData();
};

const formatValue = (value, type) => {
    if (type === 'boolean') {
        return value ? t('common.labels.yes') : t('common.labels.no');
    } else if (type === 'json') {
        return typeof value === 'string' ? value : JSON.stringify(value);
    }
    return value;
};

// Security dropdown configuration with recommended options
const securityDropdownConfig = {
    password_min_length: [
        { value: 6, label: '6 - Minimum' },
        { value: 8, label: '8 - Standar (Direkomendasikan)' },
        { value: 10, label: '10 - Kuat' },
        { value: 12, label: '12 - Sangat Kuat' },
        { value: 16, label: '16 - Maksimum' },
    ],
    login_attempts_limit: [
        { value: 3, label: '3 - Ketat' },
        { value: 5, label: '5 - Standar (Direkomendasikan)' },
        { value: 10, label: '10 - Longgar' },
        { value: 15, label: '15 - Sangat Longgar' },
    ],
    block_duration_minutes: [
        { value: 5, label: '5 menit - Singkat' },
        { value: 15, label: '15 menit - Sedang' },
        { value: 30, label: '30 menit - Standar (Direkomendasikan)' },
        { value: 60, label: '1 jam - Ketat' },
        { value: 1440, label: '24 jam - Sangat Ketat' },
    ],
    session_lifetime: [
        { value: 30, label: '30 menit - Ketat' },
        { value: 60, label: '1 jam - Sedang' },
        { value: 120, label: '2 jam - Standar (Direkomendasikan)' },
        { value: 480, label: '8 jam - Satu Hari Kerja' },
        { value: 1440, label: '24 jam - Satu Hari Penuh' },
        { value: 10080, label: '7 hari - Satu Minggu' },
    ],
    max_concurrent_sessions: [
        { value: 0, label: '0 - Tidak Terbatas' },
        { value: 1, label: '1 - Satu Perangkat' },
        { value: 2, label: '2 - Desktop + Mobile' },
        { value: 3, label: '3 - Standar (Direkomendasikan)' },
        { value: 5, label: '5 - Fleksibel' },
        { value: 10, label: '10 - Sangat Fleksibel' },
    ],
    log_retention_days: [
        { value: 0, label: '0 - Simpan Selamanya' },
        { value: 30, label: '30 hari - 1 Bulan' },
        { value: 60, label: '60 hari - 2 Bulan' },
        { value: 90, label: '90 hari - 3 Bulan (Direkomendasikan)' },
        { value: 180, label: '180 hari - 6 Bulan' },
        { value: 365, label: '365 hari - 1 Tahun' },
    ],
};

const isSecurityDropdown = (key) => {
    return Object.keys(securityDropdownConfig).includes(key);
};

const getSecurityOptions = (key) => {
    return securityDropdownConfig[key] || [];
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        // Prepare settings array for bulk update
        const settingsToUpdate = currentSettings.value.map(setting => {
            let value = formData.value[setting.key];
            
            // Handle JSON type
            if (setting.type === 'json' && typeof value === 'string') {
                try {
                    value = JSON.parse(value);
                } catch {
                    // Invalid JSON, keep original value
                }
            }
            
            return {
                key: setting.key,
                value: value,
                type: setting.type,
                group: setting.group,
            };
        });

        await api.post('/admin/cms/settings/bulk-update', {
            settings: settingsToUpdate,
        });
        
        alert(t('features.settings.saved'));
        await fetchSettings();
    } catch (error) {
        alert(error.response?.data?.message || t('features.settings.failed'));
    } finally {
        saving.value = false;
    }
};

// Email testing functions
const validateEmailConfig = async () => {
    validatingConfig.value = true;
    configValidation.value = null;
    try {
        const response = await api.get('/admin/cms/email-test/validate-config');
        const { data } = parseResponse(response);
        configValidation.value = data;
    } catch (error) {
        configValidation.value = {
            valid: false,
            errors: [error.response?.data?.message || t('features.settings.emailTest.failed')],
            warnings: [],
        };
    } finally {
        validatingConfig.value = false;
    }
};

const testSmtpConnection = async () => {
    testingConnection.value = true;
    connectionResult.value = null;
    try {
        const response = await api.post('/admin/cms/email-test/test-connection');
        const { data } = parseResponse(response);
        connectionResult.value = data;
    } catch (error) {
        connectionResult.value = {
            connected: false,
            host: 'unknown',
            port: 'unknown',
            error: error.response?.data?.message || t('features.settings.emailTest.failed'),
        };
    } finally {
        testingConnection.value = false;
    }
};

const sendTestEmail = async () => {
    if (!testEmail.value.to) {
        testEmailResult.value = {
            success: false,
            message: t('features.settings.emailTest.recipientRequired'),
        };
        return;
    }

    sendingTestEmail.value = true;
    testEmailResult.value = null;
    try {
        const response = await api.post('/admin/cms/email-test/send-test', {
            to: testEmail.value.to,
            subject: testEmail.value.subject || undefined,
            message: testEmail.value.message || undefined,
        });
        const { data, message } = parseResponse(response);
        testEmailResult.value = {
            success: true,
            message: message || t('features.settings.emailTest.sentSuccess'),
        };
        // Clear form
        testEmail.value.subject = '';
        testEmail.value.message = '';
        // Refresh logs
        await getRecentLogs();
    } catch (error) {
        testEmailResult.value = {
            success: false,
            message: error.response?.data?.message || t('features.settings.emailTest.sendFailed'),
        };
    } finally {
        sendingTestEmail.value = false;
    }
};

const getQueueStatus = async () => {
    loadingQueueStatus.value = true;
    try {
        const response = await api.get('/admin/cms/email-test/queue-status');
        const { data } = parseResponse(response);
        queueStatus.value = data;
    } catch (error) {
        queueStatus.value = {
            driver: 'unknown',
            connection: 'unknown',
            pending_jobs: 'error',
            failed_jobs: 'error',
        };
    } finally {
        loadingQueueStatus.value = false;
    }
};

const getRecentLogs = async () => {
    loadingLogs.value = true;
    try {
        const response = await api.get('/admin/cms/email-test/recent-logs?limit=10');
        const { data } = parseResponse(response);
        emailLogs.value = data.logs || [];
    } catch (error) {
        emailLogs.value = [];
    } finally {
        loadingLogs.value = false;
    }
};

// Cache Management Methods
const getCacheStatus = async () => {
    loadingCacheStatus.value = true;
    try {
        const response = await api.get('/admin/cms/system/cache-status');
        cacheStatus.value = parseSingleResponse(response);
    } catch (error) {
        console.error('Failed to get cache status:', error);
    } finally {
        loadingCacheStatus.value = false;
    }
};

const clearSystemCache = async () => {
    if (!confirm('Are you sure you want to clear the system cache?')) return;
    
    clearingCache.value = true;
    try {
        await api.post('/admin/cms/system/cache/clear');
        alert(t('features.settings.cache.cleared'));
        getCacheStatus();
    } catch (error) {
        alert(error.response?.data?.message || t('features.settings.failed'));
    } finally {
        clearingCache.value = false;
    }
};

const warmSystemCache = async () => {
    warmingCache.value = true;
    try {
        await api.post('/admin/cms/system/cache/warm');
        alert(t('features.settings.cache.warmed'));
        getCacheStatus();
    } catch (error) {
        alert(error.response?.data?.message || t('features.settings.failed'));
    } finally {
        warmingCache.value = false;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        return date.toLocaleString(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch {
        return dateString;
    }
};

watch(activeTab, (newTab) => {
    // Reset form when switching tabs
    initializeFormData();
    // Load email test data when switching to email tab
    if (newTab === 'email') {
        getQueueStatus();
        getRecentLogs();
    } else if (newTab === 'performance') {
        getCacheStatus();
    }
});

onMounted(() => {
    fetchSettings();
    // Load email test data if email tab is active
    if (activeTab.value === 'email') {
        getQueueStatus();
        getRecentLogs();
    } else if (activeTab.value === 'performance') {
        getCacheStatus();
    }
});
</script>
