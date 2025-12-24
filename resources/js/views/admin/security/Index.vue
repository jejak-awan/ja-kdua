<template>
    <div>
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.security.title') }}</h1>
            <button 
                @click="refreshAll" 
                :disabled="loading"
                class="flex items-center space-x-2 px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/80 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <svg class="h-4 w-4" :class="{'animate-spin': loading}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>{{ $t('common.actions.refresh') }}</span>
            </button>
        </div>

        <!-- Shadcn Tabs -->
        <Tabs v-model="activeTab" class="w-full">
            <TabsList class="mb-6">
                <TabsTrigger value="overview">{{ $t('features.security.tabs.overview') }}</TabsTrigger>
                <TabsTrigger value="blocklist">{{ $t('features.security.tabs.blocklist') }}</TabsTrigger>
                <TabsTrigger value="whitelist">{{ $t('features.security.tabs.whitelist') }}</TabsTrigger>
            </TabsList>

            <!-- Overview Tab -->
            <TabsContent value="overview">
            <!-- Statistics -->
            <div v-if="statistics" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-card border border-border rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.security.statistics.events') }}</p>
                            <p class="text-2xl font-semibold text-foreground">{{ statistics.total_events || 0 }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-card border border-border rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.security.statistics.blockedIps') }}</p>
                            <p class="text-2xl font-semibold text-foreground">{{ blocklist.length || 0 }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-card border border-border rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.security.statistics.failedLogins') }}</p>
                            <p class="text-2xl font-semibold text-foreground">{{ statistics.failed_logins || 0 }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-card border border-border rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.security.whitelist.title') }}</p>
                            <p class="text-2xl font-semibold text-foreground">{{ whitelist.length || 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- IP Management - Single Row -->
            <div class="bg-card border border-border rounded-lg p-6 mb-6">
                <h2 class="text-lg font-semibold text-foreground mb-4">{{ $t('features.security.ipManagement.title') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            {{ $t('features.security.ipManagement.block.label') }}
                        </label>
                        <div class="flex space-x-2">
                            <input
                                v-model="ipToBlock"
                                type="text"
                                :placeholder="$t('features.security.ipManagement.block.placeholder')"
                                class="flex-1 px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                            <button
                                @click="blockIP"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                            >
                                {{ $t('features.security.ipManagement.block.button') }}
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            {{ $t('features.security.ipManagement.check.label') }}
                        </label>
                        <div class="flex space-x-2">
                            <input
                                v-model="ipToCheck"
                                type="text"
                                :placeholder="$t('features.security.ipManagement.check.placeholder')"
                                class="flex-1 px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                            <button
                                @click="checkIPStatus"
                                class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/80"
                            >
                                {{ $t('features.security.ipManagement.check.button') }}
                            </button>
                        </div>
                        <div v-if="ipStatus" class="mt-2 p-3 rounded-md" :class="ipStatus.is_blocked ? 'bg-red-500/20 text-red-800' : 'bg-green-500/20 text-green-800'">
                            <p class="text-sm font-medium">
                                {{ $t('features.security.ipManagement.status.label') }}: {{ ipStatus.is_blocked ? $t('features.security.ipManagement.status.blocked') : $t('features.security.ipManagement.status.allowed') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Logs -->
            <div class="bg-card border border-border rounded-lg">
                <div class="px-6 py-4 border-b border-border">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex items-center space-x-4">
                            <h2 class="text-lg font-semibold text-foreground">{{ $t('features.security.logs.title') }}</h2>
                            <span v-if="selectedLogIds.length > 0" class="text-sm text-muted-foreground">
                                {{ $t('features.security.bulkActions.selected', { count: selectedLogIds.length }) }}
                            </span>
                            <button 
                                v-if="selectedLogIds.length > 0"
                                @click="bulkBlockFromLogs" 
                                class="px-3 py-1 bg-red-600 text-white text-sm rounded-md hover:bg-red-700"
                            >
                                {{ $t('features.security.bulkActions.blockSelected') }}
                            </button>
                        </div>
                        <div class="flex items-center space-x-4">
                            <select
                                v-model="logFilter"
                                class="px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">{{ $t('features.security.logs.all') }}</option>
                                <option value="login_failed">{{ $t('features.security.logs.failedLogin') }}</option>
                                <option value="ip_blocked">{{ $t('features.security.logs.blockedIp') }}</option>
                                <option value="suspicious_activity">{{ $t('features.security.logs.suspiciousActivity') }}</option>
                            </select>
                            <input
                                v-model="logSearch"
                                type="text"
                                :placeholder="$t('features.security.logs.search')"
                                class="px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                            <select
                                v-model="logsPerPage"
                                class="px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option :value="10">10</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input type="checkbox" @change="toggleAllLogs" :checked="selectedLogIds.length === paginatedLogs.length && paginatedLogs.length > 0" class="rounded">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">{{ $t('features.security.logs.table.event') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">{{ $t('features.security.logs.table.ip') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">{{ $t('features.security.logs.table.user') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">{{ $t('features.security.logs.table.details') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">{{ $t('features.security.logs.table.date') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">{{ $t('features.security.logs.table.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-card divide-y divide-border">
                            <tr v-if="loading">
                                <td colspan="7" class="px-6 py-4 text-center text-muted-foreground">{{ $t('features.security.logs.loading') }}</td>
                            </tr>
                            <tr v-else-if="paginatedLogs.length === 0">
                                <td colspan="7" class="px-6 py-4 text-center text-muted-foreground">{{ $t('features.security.logs.empty') }}</td>
                            </tr>
                            <tr v-for="log in paginatedLogs" :key="log.id">
                                <td class="px-6 py-4">
                                    <input type="checkbox" v-model="selectedLogIds" :value="log.ip_address" class="rounded">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full" :class="getEventClass(log.event_type)">
                                        {{ getEventLabel(log.event_type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ log.ip_address }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ log.user?.name || '-' }}</td>
                                <td class="px-6 py-4 text-sm text-muted-foreground max-w-xs truncate">{{ log.details }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">{{ formatDate(log.created_at) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button
                                        @click="blockIPFromLog(log.ip_address)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        {{ $t('features.security.logs.actions.blockIp') }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-border flex items-center justify-between">
                    <div class="text-sm text-muted-foreground">
                        {{ $t('common.pagination.showing') }} {{ logsStartIndex + 1 }} - {{ logsEndIndex }} {{ $t('common.pagination.of') }} {{ filteredLogs.length }}
                    </div>
                    <div class="flex items-center space-x-2">
                        <button 
                            @click="logsCurrentPage--" 
                            :disabled="logsCurrentPage === 1"
                            class="px-3 py-1 border border-input rounded-md text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-muted"
                        >
                            {{ $t('common.actions.previous') }}
                        </button>
                        <span class="text-sm text-muted-foreground">{{ logsCurrentPage }} / {{ logsTotalPages }}</span>
                        <button 
                            @click="logsCurrentPage++" 
                            :disabled="logsCurrentPage >= logsTotalPages"
                            class="px-3 py-1 border border-input rounded-md text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-muted"
                        >
                            {{ $t('common.actions.next') }}
                        </button>
                    </div>
                </div>
            </div>
            </TabsContent>

            <!-- Blocklist Tab -->
            <TabsContent value="blocklist">
            <div class="bg-card border border-border rounded-lg">
                <div class="px-6 py-4 border-b border-border">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-foreground">{{ $t('features.security.blocklist.title') }}</h2>
                            <p class="text-sm text-muted-foreground">{{ $t('features.security.blocklist.description') }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span v-if="selectedBlocklistIds.length > 0" class="text-sm text-muted-foreground">{{ $t('features.security.bulkActions.selected', { count: selectedBlocklistIds.length }) }}</span>
                            <button v-if="selectedBlocklistIds.length > 0" @click="bulkUnblock" class="px-3 py-1 bg-green-600 text-white text-sm rounded-md hover:bg-green-700">
                                {{ $t('features.security.bulkActions.unblockSelected') }}
                            </button>
                            <select
                                v-model="blocklistPerPage"
                                class="px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option :value="10">10</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input type="checkbox" @change="toggleAllBlocklist" :checked="selectedBlocklistIds.length === paginatedBlocklist.length && paginatedBlocklist.length > 0" class="rounded">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">{{ $t('features.security.blocklist.table.ip') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">{{ $t('features.security.blocklist.table.reason') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">{{ $t('features.security.blocklist.table.createdBy') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">{{ $t('features.security.blocklist.table.date') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">{{ $t('features.security.blocklist.table.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-card divide-y divide-border">
                            <tr v-if="paginatedBlocklist.length === 0">
                                <td colspan="6" class="px-6 py-4 text-center text-muted-foreground">{{ $t('features.security.blocklist.empty') }}</td>
                            </tr>
                            <tr v-for="item in paginatedBlocklist" :key="item.id">
                                <td class="px-6 py-4">
                                    <input type="checkbox" v-model="selectedBlocklistIds" :value="item.ip_address" class="rounded">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-foreground">{{ item.ip_address }}</td>
                                <td class="px-6 py-4 text-sm text-muted-foreground">{{ item.reason || '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">{{ item.creator?.name || '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">{{ formatDate(item.created_at) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                    <button @click="removeFromBlocklist(item.ip_address)" class="text-green-600 hover:text-green-900">
                                        {{ $t('features.security.blocklist.actions.remove') }}
                                    </button>
                                    <button @click="moveToWhitelist(item.ip_address)" class="text-blue-600 hover:text-blue-900">
                                        {{ $t('features.security.blocklist.actions.moveToWhitelist') }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-border flex items-center justify-between">
                    <div class="text-sm text-muted-foreground">
                        {{ $t('common.pagination.showing') }} {{ blocklistStartIndex + 1 }} - {{ blocklistEndIndex }} {{ $t('common.pagination.of') }} {{ blocklist.length }}
                    </div>
                    <div class="flex items-center space-x-2">
                        <button 
                            @click="blocklistCurrentPage--" 
                            :disabled="blocklistCurrentPage === 1"
                            class="px-3 py-1 border border-input rounded-md text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-muted"
                        >
                            {{ $t('common.actions.previous') }}
                        </button>
                        <span class="text-sm text-muted-foreground">{{ blocklistCurrentPage }} / {{ blocklistTotalPages }}</span>
                        <button 
                            @click="blocklistCurrentPage++" 
                            :disabled="blocklistCurrentPage >= blocklistTotalPages"
                            class="px-3 py-1 border border-input rounded-md text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-muted"
                        >
                            {{ $t('common.actions.next') }}
                        </button>
                    </div>
                </div>
            </div>
            </TabsContent>

            <!-- Whitelist Tab -->
            <TabsContent value="whitelist">
            <div class="bg-card border border-border rounded-lg">
                <div class="px-6 py-4 border-b border-border">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-foreground">{{ $t('features.security.whitelist.title') }}</h2>
                            <p class="text-sm text-muted-foreground">{{ $t('features.security.whitelist.description') }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span v-if="selectedWhitelistIds.length > 0" class="text-sm text-muted-foreground">{{ $t('features.security.bulkActions.selected', { count: selectedWhitelistIds.length }) }}</span>
                            <button v-if="selectedWhitelistIds.length > 0" @click="bulkRemoveWhitelist" class="px-3 py-1 bg-red-600 text-white text-sm rounded-md hover:bg-red-700">
                                {{ $t('features.security.bulkActions.removeSelected') }}
                            </button>
                            <select
                                v-model="whitelistPerPage"
                                class="px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option :value="10">10</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                            </select>
                        </div>
                    </div>
                    <!-- Add IP Form -->
                    <div class="mt-4 pt-4 border-t border-border">
                        <label class="block text-sm font-medium text-foreground mb-2">
                            {{ $t('features.security.whitelist.addIp') }}
                        </label>
                        <div class="flex space-x-2">
                            <input
                                v-model="ipToWhitelist"
                                type="text"
                                placeholder="192.168.1.1"
                                class="flex-1 max-w-md px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                            <button
                                @click="addToWhitelist(ipToWhitelist)"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                            >
                                {{ $t('common.actions.add') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input type="checkbox" @change="toggleAllWhitelist" :checked="selectedWhitelistIds.length === paginatedWhitelist.length && paginatedWhitelist.length > 0" class="rounded">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">{{ $t('features.security.whitelist.table.ip') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">{{ $t('features.security.whitelist.table.reason') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">{{ $t('features.security.whitelist.table.createdBy') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">{{ $t('features.security.whitelist.table.date') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">{{ $t('features.security.whitelist.table.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-card divide-y divide-border">
                            <tr v-if="paginatedWhitelist.length === 0">
                                <td colspan="6" class="px-6 py-4 text-center text-muted-foreground">{{ $t('features.security.whitelist.empty') }}</td>
                            </tr>
                            <tr v-for="item in paginatedWhitelist" :key="item.id">
                                <td class="px-6 py-4">
                                    <input type="checkbox" v-model="selectedWhitelistIds" :value="item.ip_address" class="rounded">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-foreground">{{ item.ip_address }}</td>
                                <td class="px-6 py-4 text-sm text-muted-foreground">{{ item.reason || '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">{{ item.creator?.name || '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">{{ formatDate(item.created_at) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button @click="removeFromWhitelist(item.ip_address)" class="text-red-600 hover:text-red-900">
                                        {{ $t('features.security.whitelist.actions.remove') }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-border flex items-center justify-between">
                    <div class="text-sm text-muted-foreground">
                        {{ $t('common.pagination.showing') }} {{ whitelistStartIndex + 1 }} - {{ whitelistEndIndex }} {{ $t('common.pagination.of') }} {{ whitelist.length }}
                    </div>
                    <div class="flex items-center space-x-2">
                        <button 
                            @click="whitelistCurrentPage--" 
                            :disabled="whitelistCurrentPage === 1"
                            class="px-3 py-1 border border-input rounded-md text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-muted"
                        >
                            {{ $t('common.actions.previous') }}
                        </button>
                        <span class="text-sm text-muted-foreground">{{ whitelistCurrentPage }} / {{ whitelistTotalPages }}</span>
                        <button 
                            @click="whitelistCurrentPage++" 
                            :disabled="whitelistCurrentPage >= whitelistTotalPages"
                            class="px-3 py-1 border border-input rounded-md text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-muted"
                        >
                            {{ $t('common.actions.next') }}
                        </button>
                    </div>
                </div>
            </div>
            </TabsContent>
        </Tabs>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { parseResponse, ensureArray, parseSingleResponse } from '../../../utils/responseParser';
import Tabs from '../../../components/ui/tabs.vue';
import TabsList from '../../../components/ui/tabs-list.vue';
import TabsTrigger from '../../../components/ui/tabs-trigger.vue';
import TabsContent from '../../../components/ui/tabs-content.vue';

const { t } = useI18n();

// Data
const logs = ref([]);
const statistics = ref(null);
const blocklist = ref([]);
const whitelist = ref([]);
const loading = ref(false);

// UI State
const activeTab = ref('overview');
const logFilter = ref('');
const logSearch = ref('');
const ipToBlock = ref('');
const ipToCheck = ref('');
const ipToUnblock = ref('');
const ipToWhitelist = ref('');
const ipStatus = ref(null);
const selectedLogIds = ref([]);
const selectedBlocklistIds = ref([]);
const selectedWhitelistIds = ref([]);

// Pagination State
const logsPerPage = ref(10);
const logsCurrentPage = ref(1);
const blocklistPerPage = ref(10);
const blocklistCurrentPage = ref(1);
const whitelistPerPage = ref(10);
const whitelistCurrentPage = ref(1);

// Reset pagination when filter changes
watch([logFilter, logSearch, logsPerPage], () => {
    logsCurrentPage.value = 1;
});
watch(blocklistPerPage, () => {
    blocklistCurrentPage.value = 1;
});
watch(whitelistPerPage, () => {
    whitelistCurrentPage.value = 1;
});

// Tabs
const tabs = computed(() => [
    { key: 'overview', label: t('features.security.tabs.overview') },
    { key: 'blocklist', label: t('features.security.tabs.blocklist') },
    { key: 'whitelist', label: t('features.security.tabs.whitelist') },
]);

// Filtered logs
const filteredLogs = computed(() => {
    let filtered = logs.value;
    
    if (logFilter.value) {
        filtered = filtered.filter(log => log.event_type === logFilter.value);
    }
    
    if (logSearch.value) {
        const searchLower = logSearch.value.toLowerCase();
        filtered = filtered.filter(log => 
            log.ip_address?.toLowerCase().includes(searchLower) ||
            log.details?.toLowerCase().includes(searchLower) ||
            log.user?.name?.toLowerCase().includes(searchLower)
        );
    }
    
    return filtered;
});

// Paginated data
const logsTotalPages = computed(() => Math.max(1, Math.ceil(filteredLogs.value.length / logsPerPage.value)));
const logsStartIndex = computed(() => (logsCurrentPage.value - 1) * logsPerPage.value);
const logsEndIndex = computed(() => Math.min(logsStartIndex.value + logsPerPage.value, filteredLogs.value.length));
const paginatedLogs = computed(() => filteredLogs.value.slice(logsStartIndex.value, logsEndIndex.value));

const blocklistTotalPages = computed(() => Math.max(1, Math.ceil(blocklist.value.length / blocklistPerPage.value)));
const blocklistStartIndex = computed(() => (blocklistCurrentPage.value - 1) * blocklistPerPage.value);
const blocklistEndIndex = computed(() => Math.min(blocklistStartIndex.value + blocklistPerPage.value, blocklist.value.length));
const paginatedBlocklist = computed(() => blocklist.value.slice(blocklistStartIndex.value, blocklistEndIndex.value));

const whitelistTotalPages = computed(() => Math.max(1, Math.ceil(whitelist.value.length / whitelistPerPage.value)));
const whitelistStartIndex = computed(() => (whitelistCurrentPage.value - 1) * whitelistPerPage.value);
const whitelistEndIndex = computed(() => Math.min(whitelistStartIndex.value + whitelistPerPage.value, whitelist.value.length));
const paginatedWhitelist = computed(() => whitelist.value.slice(whitelistStartIndex.value, whitelistEndIndex.value));

// Fetch functions
const fetchLogs = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/security/logs');
        const result = parseResponse(response);
        logs.value = result.data || [];
    } catch (error) {
        console.error('Failed to fetch logs:', error);
    } finally {
        loading.value = false;
    }
};

const fetchStats = async () => {
    try {
        const response = await api.get('/admin/cms/security/stats');
        statistics.value = parseSingleResponse(response) || {};
    } catch (error) {
        console.error('Failed to fetch stats:', error);
    }
};

const fetchBlocklist = async () => {
    try {
        const response = await api.get('/admin/cms/security/blocklist');
        blocklist.value = ensureArray(parseSingleResponse(response));
    } catch (error) {
        console.error('Failed to fetch blocklist:', error);
    }
};

const fetchWhitelist = async () => {
    try {
        const response = await api.get('/admin/cms/security/whitelist');
        whitelist.value = ensureArray(parseSingleResponse(response));
    } catch (error) {
        console.error('Failed to fetch whitelist:', error);
    }
};

// IP Actions
const blockIP = async () => {
    if (!ipToBlock.value) {
        alert(t('features.security.messages.enterIp'));
        return;
    }

    if (!confirm(t('features.security.messages.confirmBlock', { ip: ipToBlock.value }))) {
        return;
    }

    try {
        await api.post('/admin/cms/security/block-ip', { ip_address: ipToBlock.value });
        alert(t('features.security.messages.blockSuccess'));
        ipToBlock.value = '';
        await fetchBlocklist();
        await fetchLogs();
    } catch (error) {
        console.error('Failed to block IP:', error);
        alert(error.response?.data?.message || t('features.security.messages.blockFailed'));
    }
};

const checkIPStatus = async () => {
    if (!ipToCheck.value) {
        alert(t('features.security.messages.enterIp'));
        return;
    }

    try {
        const response = await api.get('/admin/cms/security/check-ip', { params: { ip_address: ipToCheck.value } });
        ipStatus.value = parseSingleResponse(response) || {};
    } catch (error) {
        console.error('Failed to check IP status:', error);
        alert(t('features.security.messages.checkFailed'));
    }
};

const unblockIP = async () => {
    if (!ipToUnblock.value) {
        alert(t('features.security.messages.enterIp'));
        return;
    }

    if (!confirm(t('features.security.messages.confirmUnblock', { ip: ipToUnblock.value }))) {
        return;
    }

    try {
        await api.post('/admin/cms/security/unblock-ip', { ip_address: ipToUnblock.value });
        await api.post('/admin/cms/security/clear-failed-attempts', { ip_address: ipToUnblock.value });
        alert(t('features.security.messages.unblockSuccess'));
        ipToUnblock.value = '';
        await fetchBlocklist();
        await fetchLogs();
    } catch (error) {
        console.error('Failed to unblock IP:', error);
        alert(error.response?.data?.message || t('features.security.messages.unblockFailed'));
    }
};

const blockIPFromLog = async (ip) => {
    if (!confirm(t('features.security.messages.confirmBlock', { ip }))) {
        return;
    }

    try {
        await api.post('/admin/cms/security/block-ip', { ip_address: ip });
        alert(t('features.security.messages.blockSuccess'));
        await fetchBlocklist();
        await fetchLogs();
    } catch (error) {
        console.error('Failed to block IP:', error);
        alert(t('features.security.messages.blockFailed'));
    }
};

// Bulk actions for logs
const bulkBlockFromLogs = async () => {
    if (selectedLogIds.value.length === 0) return;
    
    const uniqueIps = [...new Set(selectedLogIds.value)];
    
    try {
        await api.post('/admin/cms/security/bulk-block', { ip_addresses: uniqueIps });
        alert(t('features.security.messages.bulkBlockSuccess', { blocked: uniqueIps.length, skipped: 0 }));
        selectedLogIds.value = [];
        await fetchBlocklist();
        await fetchLogs();
    } catch (error) {
        console.error('Failed to bulk block:', error);
    }
};

const toggleAllLogs = (e) => {
    if (e.target.checked) {
        selectedLogIds.value = paginatedLogs.value.map(log => log.ip_address);
    } else {
        selectedLogIds.value = [];
    }
};

// Blocklist Actions
const removeFromBlocklist = async (ip) => {
    try {
        await api.post('/admin/cms/security/unblock-ip', { ip_address: ip });
        await fetchBlocklist();
    } catch (error) {
        console.error('Failed to remove from blocklist:', error);
    }
};

const moveToWhitelist = async (ip) => {
    try {
        await api.post('/admin/cms/security/whitelist', { ip_address: ip });
        await fetchBlocklist();
        await fetchWhitelist();
    } catch (error) {
        console.error('Failed to move to whitelist:', error);
    }
};

const bulkUnblock = async () => {
    if (selectedBlocklistIds.value.length === 0) return;
    
    try {
        await api.post('/admin/cms/security/bulk-unblock', { ip_addresses: selectedBlocklistIds.value });
        alert(t('features.security.messages.bulkUnblockSuccess', { count: selectedBlocklistIds.value.length }));
        selectedBlocklistIds.value = [];
        await fetchBlocklist();
    } catch (error) {
        console.error('Failed to bulk unblock:', error);
    }
};

const toggleAllBlocklist = (e) => {
    if (e.target.checked) {
        selectedBlocklistIds.value = paginatedBlocklist.value.map(item => item.ip_address);
    } else {
        selectedBlocklistIds.value = [];
    }
};

// Whitelist Actions
const addToWhitelist = async (ip) => {
    if (!ip) {
        alert(t('features.security.messages.enterIp'));
        return;
    }

    try {
        await api.post('/admin/cms/security/whitelist', { ip_address: ip });
        alert(t('features.security.messages.whitelistSuccess'));
        ipToWhitelist.value = '';
        await fetchWhitelist();
        await fetchBlocklist();
    } catch (error) {
        console.error('Failed to add to whitelist:', error);
        alert(t('features.security.messages.whitelistFailed'));
    }
};

const removeFromWhitelist = async (ip) => {
    if (!confirm(t('features.security.messages.confirmRemoveWhitelist', { ip }))) {
        return;
    }

    try {
        await api.delete('/admin/cms/security/whitelist', { data: { ip_address: ip } });
        await fetchWhitelist();
    } catch (error) {
        console.error('Failed to remove from whitelist:', error);
    }
};

const bulkRemoveWhitelist = async () => {
    if (selectedWhitelistIds.value.length === 0) return;
    
    try {
        await api.post('/admin/cms/security/bulk-remove-whitelist', { ip_addresses: selectedWhitelistIds.value });
        alert(t('features.security.messages.bulkRemoveSuccess', { count: selectedWhitelistIds.value.length }));
        selectedWhitelistIds.value = [];
        await fetchWhitelist();
    } catch (error) {
        console.error('Failed to bulk remove whitelist:', error);
    }
};

const toggleAllWhitelist = (e) => {
    if (e.target.checked) {
        selectedWhitelistIds.value = paginatedWhitelist.value.map(item => item.ip_address);
    } else {
        selectedWhitelistIds.value = [];
    }
};

// Helpers
const getEventLabel = (eventType) => {
    const key = `features.security.logs.eventTypes.${eventType}`;
    const translated = t(key);
    // If translation key doesn't exist, return formatted event type
    return translated !== key ? translated : eventType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getEventClass = (eventType) => {
    const classes = {
        'login_failed': 'bg-yellow-500/20 text-yellow-500 border border-yellow-500/30',
        'login_success': 'bg-green-500/20 text-green-500 border border-green-500/30',
        'ip_blocked': 'bg-red-500/20 text-red-500 border border-red-500/30',
        'ip_unblocked': 'bg-blue-500/20 text-blue-500 border border-blue-500/30',
        'suspicious_activity': 'bg-orange-500/20 text-orange-500 border border-orange-500/30',
        'ip_whitelisted': 'bg-emerald-500/20 text-emerald-500 border border-emerald-500/30',
        'ip_whitelist_removed': 'bg-amber-500/20 text-amber-500 border border-amber-500/30',
        'account_locked': 'bg-purple-500/20 text-purple-500 border border-purple-500/30',
        'login_blocked': 'bg-red-500/20 text-red-500 border border-red-500/30',
        'ip_blocked_permanent': 'bg-red-500/20 text-red-500 border border-red-500/30',
        'ip_blocked_temp': 'bg-red-500/20 text-red-500 border border-red-500/30',
    };
    return classes[eventType] || 'bg-muted/50 text-muted-foreground border border-border';
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Refresh all data
const refreshAll = async () => {
    await Promise.all([
        fetchLogs(),
        fetchStats(),
        fetchBlocklist(),
        fetchWhitelist()
    ]);
};

onMounted(() => {
    fetchLogs();
    fetchStats();
    fetchBlocklist();
    fetchWhitelist();
});
</script>
