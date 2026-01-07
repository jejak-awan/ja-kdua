<template>
    <div>
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <router-link to="/admin/logs-dashboard">
                        <ArrowLeft class="w-5 h-5" />
                    </router-link>
                </Button>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('features.security.title') }}</h1>
            </div>
            <div class="flex items-center space-x-2">
                <Button
                    variant="destructive"
                    @click="clearLogs"
                >
                    <Trash2 class="w-4 h-4 mr-2" />
                    {{ $t('features.system.logs.clear') }}
                </Button>
                <Button 
                    @click="refreshAll" 
                    :disabled="loading"
                >
                    <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                    <RefreshCw v-else class="w-4 h-4 mr-2" />
                    <span>{{ $t('common.actions.refresh') }}</span>
                </Button>
            </div>
        </div>

        <!-- Shadcn Tabs -->
        <Tabs v-model="activeTab" class="w-full">
            <TabsList class="mb-6">
                <TabsTrigger value="overview">{{ $t('features.security.tabs.overview') }}</TabsTrigger>
                <TabsTrigger value="blocklist">{{ $t('features.security.tabs.blocklist') }}</TabsTrigger>
                <TabsTrigger value="whitelist">{{ $t('features.security.tabs.whitelist') }}</TabsTrigger>
                <TabsTrigger value="csp-reports">{{ $t('features.security.tabs.cspReports') }}</TabsTrigger>
                <TabsTrigger value="slow-queries">{{ $t('features.security.tabs.slowQueries') }}</TabsTrigger>
                <TabsTrigger value="vulnerabilities">{{ $t('features.security.tabs.vulnerabilities') }}</TabsTrigger>
            </TabsList>

            <!-- Overview Tab -->
            <TabsContent value="overview">
            <!-- Statistics -->
            <div v-if="statistics" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-red-500/10 rounded-lg">
                                <ShieldAlert class="h-6 w-6 text-red-500" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-muted-foreground">{{ $t('features.security.statistics.events') }}</p>
                                <p class="text-2xl font-bold text-foreground">{{ statistics.total_events || 0 }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-yellow-500/10 rounded-lg">
                                <ShieldX class="h-6 w-6 text-yellow-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-muted-foreground">{{ $t('features.security.statistics.blockedIps') }}</p>
                                <p class="text-2xl font-bold text-foreground">{{ blocklist.length || 0 }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-orange-500/10 rounded-lg">
                                <UserX class="h-6 w-6 text-orange-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-muted-foreground">{{ $t('features.security.statistics.failedLogins') }}</p>
                                <p class="text-2xl font-bold text-foreground">{{ statistics.failed_logins || 0 }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-500/10 rounded-lg">
                                <ShieldCheck class="h-6 w-6 text-green-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-muted-foreground">{{ $t('features.security.whitelist.title') }}</p>
                                <p class="text-2xl font-bold text-foreground">{{ whitelist.length || 0 }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- IP Management - Single Row -->
            <Card class="mb-6">
                <CardHeader>
                    <CardTitle class="text-lg">{{ $t('features.security.ipManagement.title') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <Label>
                                {{ $t('features.security.ipManagement.block.label') }}
                            </Label>
                            <div class="flex space-x-2">
                                <Input
                                    v-model="ipToBlock"
                                    type="text"
                                    :placeholder="$t('features.security.ipManagement.block.placeholder')"
                                />
                                <Button
                                    variant="destructive"
                                    @click="blockIP"
                                    :disabled="!isValidBlockIp"
                                >
                                    {{ $t('features.security.ipManagement.block.button') }}
                                </Button>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label>
                                {{ $t('features.security.ipManagement.check.label') }}
                            </Label>
                            <div class="flex space-x-2">
                                <Input
                                    v-model="ipToCheck"
                                    type="text"
                                    :placeholder="$t('features.security.ipManagement.check.placeholder')"
                                />
                                <Button
                                    @click="checkIPStatus"
                                    :disabled="!isValidCheckIp"
                                >
                                    {{ $t('features.security.ipManagement.check.button') }}
                                </Button>
                            </div>
                            <div v-if="ipStatus" class="mt-2">
                                <Badge
                                    :variant="ipStatus.is_blocked ? 'destructive' : 'default'"
                                    class="w-full justify-center py-2 bg-muted/50 text-foreground"
                                >
                                    {{ $t('features.security.ipManagement.status.label') }}: {{ ipStatus.is_blocked ? $t('features.security.ipManagement.status.blocked') : $t('features.security.ipManagement.status.allowed') }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Security Logs -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between pb-4">
                    <div class="flex items-center space-x-4">
                        <CardTitle class="text-lg">{{ $t('features.security.logs.title') }}</CardTitle>
                        <Badge v-if="selectedLogIds.length > 0" variant="secondary" class="bg-muted/50 text-foreground">
                            {{ $t('features.security.bulkActions.selected', { count: selectedLogIds.length }) }}
                        </Badge>
                        <Button
                            v-if="selectedLogIds.length > 0"
                            variant="destructive"
                            size="sm"
                            @click="bulkBlockFromLogs"
                        >
                            {{ $t('features.security.bulkActions.blockSelected') }}
                        </Button>
                    </div>
                    <div class="flex items-center space-x-2">
                        <Select v-model="logFilter">
                            <SelectTrigger class="w-48">
                                <SelectValue :placeholder="$t('features.security.logs.all')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ $t('features.security.logs.all') }}</SelectItem>
                                <SelectItem value="login_failed">{{ $t('features.security.logs.failedLogin') }}</SelectItem>
                                <SelectItem value="ip_blocked">{{ $t('features.security.logs.blockedIp') }}</SelectItem>
                                <SelectItem value="suspicious_activity">{{ $t('features.security.logs.suspiciousActivity') }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <div class="relative w-64">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input
                                v-model="logSearch"
                                :placeholder="$t('features.security.logs.search')"
                                class="pl-10"
                            />
                        </div>
                        <Select v-model="logsPerPage">
                            <SelectTrigger class="w-20">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="10">10</SelectItem>
                                <SelectItem :value="25">25</SelectItem>
                                <SelectItem :value="50">50</SelectItem>
                                <SelectItem :value="100">100</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="relative w-full overflow-auto">
                        <table class="w-full divide-y divide-border">
                            <thead class="bg-muted/50">
                                <tr>
                                    <th class="w-12 px-4 py-3 text-left">
                                        <Checkbox 
                                            :checked="selectedLogIds.length === paginatedLogs.length && paginatedLogs.length > 0"
                                            @update:checked="toggleAllLogs"
                                        />
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.logs.table.event') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.logs.table.ip') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.logs.table.user') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.logs.table.details') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.logs.table.date') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-muted-foreground">{{ $t('features.security.logs.table.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-if="loading">
                                    <td colspan="7" class="h-24 text-center text-muted-foreground">
                                        <Loader2 class="w-6 h-6 animate-spin mx-auto mb-2" />
                                        {{ $t('features.security.logs.loading') }}
                                    </td>
                                </tr>
                                <tr v-else-if="paginatedLogs.length === 0">
                                    <td colspan="7" class="h-24 text-center text-muted-foreground">
                                        {{ $t('features.security.logs.empty') }}
                                    </td>
                                </tr>
                                <tr v-for="log in paginatedLogs" :key="log.id" class="hover:bg-muted/50 transition-colors">
                                    <td class="px-4 py-3">
                                        <Checkbox 
                                            :checked="selectedLogIds.includes(log.ip_address)"
                                            @update:checked="(checked) => handleSelectLog(checked, log.ip_address)"
                                        />
                                    </td>
                                    <td class="px-4 py-3">
                                        <Badge :class="getEventClass(log.event_type)" variant="outline">
                                            {{ getEventLabel(log.event_type) }}
                                        </Badge>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-sm">{{ log.ip_address }}</td>
                                    <td class="px-4 py-3 text-sm">{{ log.user?.name || '-' }}</td>
                                    <td class="px-4 py-3 max-w-xs truncate text-muted-foreground text-sm" :title="log.details">
                                        {{ log.details }}
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground whitespace-nowrap text-sm">
                                        {{ formatDate(log.created_at) }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="text-destructive hover:text-destructive hover:bg-destructive/10 h-8"
                                            @click="blockIPFromLog(log.ip_address)"
                                        >
                                            {{ $t('features.security.logs.actions.blockIp') }}
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
                <!-- Pagination -->
                <Pagination
                    v-if="filteredLogs.length > 0"
                    :current-page="logsCurrentPage"
                    :total-items="filteredLogs.length"
                    :per-page="Number(logsPerPage)"
                    @page-change="(val) => logsCurrentPage = val"
                    @update:per-page="(val) => { logsPerPage = val; logsCurrentPage = 1; }"
                    class="border-none shadow-none px-6 py-4"
                />
            </Card>
            </TabsContent>

            <!-- Blocklist Tab -->
            <TabsContent value="blocklist">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-4">
                        <div>
                            <CardTitle class="text-lg">{{ $t('features.security.blocklist.title') }}</CardTitle>
                            <CardDescription>{{ $t('features.security.blocklist.description') }}</CardDescription>
                        </div>
                        <div class="flex items-center space-x-2">
                             <Badge v-if="selectedBlocklistIds.length > 0" variant="secondary">
                                {{ $t('features.security.bulkActions.selected', { count: selectedBlocklistIds.length }) }}
                            </Badge>
                            <Button
                                v-if="selectedBlocklistIds.length > 0"
                                variant="outline"
                                size="sm"
                                @click="bulkUnblock"
                            >
                                <ShieldCheck class="w-4 h-4 mr-2 text-green-500" />
                                {{ $t('features.security.bulkActions.unblockSelected') }}
                            </Button>
                            <Select v-model="blocklistPerPage">
                                <SelectTrigger class="w-20">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="10">10</SelectItem>
                                    <SelectItem :value="25">25</SelectItem>
                                    <SelectItem :value="50">50</SelectItem>
                                    <SelectItem :value="100">100</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="relative w-full overflow-auto">
                            <table class="w-full divide-y divide-border">
                                <thead class="bg-muted/50">
                                    <tr>
                                        <th class="w-12 px-4 py-3 text-left">
                                            <Checkbox 
                                                :checked="selectedBlocklistIds.length === paginatedBlocklist.length && paginatedBlocklist.length > 0"
                                                @update:checked="toggleAllBlocklist"
                                            />
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.blocklist.table.ip') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.blocklist.table.reason') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.blocklist.table.createdBy') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.blocklist.table.date') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-muted-foreground">{{ $t('features.security.blocklist.table.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-if="paginatedBlocklist.length === 0">
                                        <td colspan="6" class="h-24 text-center text-muted-foreground">
                                            {{ $t('features.security.blocklist.empty') }}
                                        </td>
                                    </tr>
                                    <tr v-for="item in paginatedBlocklist" :key="item.id" class="hover:bg-muted/50 transition-colors">
                                        <td class="px-4 py-3">
                                            <Checkbox 
                                                :checked="selectedBlocklistIds.includes(item.ip_address)"
                                                @update:checked="(checked) => handleSelectBlocklist(checked, item.ip_address)"
                                            />
                                        </td>
                                        <td class="px-4 py-3 font-mono text-sm">{{ item.ip_address }}</td>
                                        <td class="px-4 py-3 max-w-xs truncate text-muted-foreground text-sm">{{ item.reason || '-' }}</td>
                                        <td class="px-4 py-3 text-sm">{{ item.creator?.name || '-' }}</td>
                                        <td class="px-4 py-3 text-muted-foreground whitespace-nowrap text-sm">
                                            {{ formatDate(item.created_at) }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex justify-end gap-2">
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    @click="moveToWhitelist(item.ip_address)"
                                                    class="h-8"
                                                >
                                                    <ShieldCheck class="w-4 h-4 mr-1 text-green-500" />
                                                    {{ $t('features.security.blocklist.actions.moveToWhitelist') }}
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    @click="removeFromBlocklist(item.ip_address)"
                                                    class="h-8 w-8 text-destructive hover:bg-destructive/10"
                                                >
                                                    <Trash2 class="w-4 h-4" />
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                    <!-- Pagination -->
                    <Pagination
                        v-if="blocklist.length > 0"
                        :current-page="blocklistCurrentPage"
                        :total-items="blocklist.length"
                        :per-page="Number(blocklistPerPage)"
                        @page-change="(val) => blocklistCurrentPage = val"
                        @update:per-page="(val) => { blocklistPerPage = val; blocklistCurrentPage = 1; }"
                        class="border-none shadow-none px-6 py-4"
                    />
                </Card>
            </TabsContent>

            <!-- Whitelist Tab -->
            <TabsContent value="whitelist">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-4">
                        <div>
                            <CardTitle class="text-lg">{{ $t('features.security.whitelist.title') }}</CardTitle>
                            <CardDescription>{{ $t('features.security.whitelist.description') }}</CardDescription>
                        </div>
                        <div class="flex items-center space-x-2">
                             <Badge v-if="selectedWhitelistIds.length > 0" variant="secondary">
                                {{ $t('features.security.bulkActions.selected', { count: selectedWhitelistIds.length }) }}
                            </Badge>
                            <Button
                                v-if="selectedWhitelistIds.length > 0"
                                variant="destructive"
                                size="sm"
                                @click="bulkRemoveWhitelist"
                            >
                                <Trash2 class="w-4 h-4 mr-2" />
                                {{ $t('features.security.bulkActions.removeSelected') }}
                            </Button>
                            <Select v-model="whitelistPerPage">
                                <SelectTrigger class="w-20">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="10">10</SelectItem>
                                    <SelectItem :value="25">25</SelectItem>
                                    <SelectItem :value="50">50</SelectItem>
                                    <SelectItem :value="100">100</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardHeader>
                    <div class="px-6 py-4 bg-muted/20">
                         <Label class="text-sm font-medium mb-2 block">
                            {{ $t('features.security.whitelist.addIp') }}
                        </Label>
                        <div class="flex space-x-2">
                            <Input
                                v-model="ipToWhitelist"
                                type="text"
                                placeholder="192.168.1.1"
                                class="max-w-md"
                            />
                            <Button
                                @click="addToWhitelist(ipToWhitelist)"
                                :disabled="!isValidWhitelistIp"
                            >
                                <Plus class="w-4 h-4 mr-2" />
                                {{ $t('common.actions.add') }}
                            </Button>
                        </div>
                    </div>
                    <CardContent class="p-0">
                        <div class="relative w-full overflow-auto">
                            <table class="w-full divide-y divide-border">
                                <thead class="bg-muted/50">
                                    <tr>
                                        <th class="w-12 px-4 py-3 text-left">
                                            <Checkbox 
                                                :checked="selectedWhitelistIds.length === paginatedWhitelist.length && paginatedWhitelist.length > 0"
                                                @update:checked="toggleAllWhitelist"
                                            />
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.whitelist.table.ip') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.whitelist.table.reason') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.whitelist.table.createdBy') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.whitelist.table.date') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-muted-foreground">{{ $t('features.security.whitelist.table.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-if="paginatedWhitelist.length === 0">
                                        <td colspan="6" class="h-24 text-center text-muted-foreground">
                                            {{ $t('features.security.whitelist.empty') }}
                                        </td>
                                    </tr>
                                    <tr v-for="item in paginatedWhitelist" :key="item.id" class="hover:bg-muted/50 transition-colors">
                                        <td class="px-4 py-3">
                                            <Checkbox 
                                                :checked="selectedWhitelistIds.includes(item.ip_address)"
                                                @update:checked="(checked) => handleSelectWhitelist(checked, item.ip_address)"
                                            />
                                        </td>
                                        <td class="px-4 py-3 font-mono text-sm">{{ item.ip_address }}</td>
                                        <td class="px-4 py-3 text-muted-foreground text-sm">{{ item.reason || '-' }}</td>
                                        <td class="px-4 py-3 text-sm">{{ item.creator?.name || '-' }}</td>
                                        <td class="px-4 py-3 text-muted-foreground whitespace-nowrap text-sm">
                                            {{ formatDate(item.created_at) }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                @click="removeFromWhitelist(item.ip_address)"
                                                class="text-destructive hover:text-destructive hover:bg-destructive/10 h-8 w-8"
                                            >
                                                <Trash2 class="w-4 h-4" />
                                            </Button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                    <!-- Pagination -->
                    <Pagination
                        v-if="whitelist.length > 0"
                        :current-page="whitelistCurrentPage"
                        :total-items="whitelist.length"
                        :per-page="Number(whitelistPerPage)"
                        @page-change="(val) => whitelistCurrentPage = val"
                        @update:per-page="(val) => { whitelistPerPage = val; whitelistCurrentPage = 1; }"
                        class="border-none shadow-none px-6 py-4"
                    />
                </Card>
            </TabsContent>

            <!-- CSP Reports Tab -->
            <TabsContent value="csp-reports">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-4">
                        <div>
                            <CardTitle class="text-lg">{{ $t('features.security.cspReports.title') }}</CardTitle>
                            <CardDescription>{{ $t('features.security.cspReports.description') }}</CardDescription>
                        </div>
                        <Button @click="fetchCspReports" variant="outline" size="sm" :disabled="cspLoading">
                            <Loader2 v-if="cspLoading" class="w-4 h-4 mr-2 animate-spin" />
                            <RefreshCw v-else class="w-4 h-4 mr-2" />
                            {{ $t('common.actions.refresh') }}
                        </Button>
                    </CardHeader>

                    <!-- CSP Statistics -->
                    <div class="px-6 pb-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-muted/30 rounded-lg p-4">
                            <p class="text-sm text-muted-foreground">{{ $t('features.security.cspReports.stats.total') }}</p>
                            <p class="text-2xl font-bold text-foreground">{{ cspStats.total || 0 }}</p>
                        </div>
                        <div class="bg-muted/30 rounded-lg p-4">
                            <p class="text-sm text-muted-foreground">{{ $t('features.security.cspReports.stats.new') }}</p>
                            <p class="text-2xl font-bold text-orange-600">{{ cspStats.new || 0 }}</p>
                        </div>
                        <div class="bg-muted/30 rounded-lg p-4">
                            <p class="text-sm text-muted-foreground">{{ $t('features.security.cspReports.stats.topViolation') }}</p>
                            <p class="text-sm font-medium text-foreground truncate">{{ cspTopViolation || 'None' }}</p>
                        </div>
                        <div class="bg-muted/30 rounded-lg p-4">
                            <p class="text-sm text-muted-foreground">{{ $t('features.security.cspReports.stats.last24h') }}</p>
                            <p class="text-2xl font-bold text-foreground">{{ cspRecentCount }}</p>
                        </div>
                    </div>

                    <!-- CSP Filters -->
                    <div class="px-6 pb-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <Label class="mb-2 block">{{ $t('features.security.cspReports.filters.status') }}</Label>
                                <Select v-model="cspFilters.status">
                                    <SelectTrigger>
                                        <SelectValue :placeholder="$t('common.labels.all')" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{{ $t('common.labels.all') }}</SelectItem>
                                        <SelectItem value="new">{{ $t('features.security.cspReports.status.new') }}</SelectItem>
                                        <SelectItem value="reviewed">{{ $t('features.security.cspReports.status.reviewed') }}</SelectItem>
                                        <SelectItem value="false_positive">{{ $t('features.security.cspReports.status.falsePositive') }}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <Label class="mb-2 block">{{ $t('features.security.cspReports.filters.directive') }}</Label>
                                <Input v-model="cspFilters.directive" placeholder="e.g. script-src" />
                            </div>
                            <div>
                                <Label class="mb-2 block">{{ $t('features.security.cspReports.filters.dateFrom') }}</Label>
                                <Input v-model="cspFilters.date_from" type="date" />
                            </div>
                            <div>
                                <Label class="mb-2 block">{{ $t('features.security.cspReports.filters.dateTo') }}</Label>
                                <Input v-model="cspFilters.date_to" type="date" />
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 mt-4">
                            <Button @click="applyCspFilters" size="sm">{{ $t('common.actions.apply') }}</Button>
                            <Button @click="resetCspFilters" variant="outline" size="sm">{{ $t('common.actions.reset') }}</Button>
                        </div>
                    </div>

                    <div v-if="selectedCspReports.length > 0" class="px-6 pb-4">
                        <div class="bg-muted/50 border border-border rounded-lg p-4 flex items-center justify-between transition-all duration-200">
                            <span class="text-sm font-medium text-foreground">
                                {{ $t('features.security.bulkActions.selected', { count: selectedCspReports.length }) }}
                            </span>
                            <div class="flex gap-2">
                                <Button @click="cspBulkAction('mark_reviewed')" variant="outline" size="sm">{{ $t('features.security.cspReports.actions.markReviewed') }}</Button>
                                <Button @click="cspBulkAction('mark_false_positive')" variant="outline" size="sm">{{ $t('features.security.cspReports.actions.markFalsePositive') }}</Button>
                                <Button @click="cspBulkAction('delete')" variant="destructive" size="sm">{{ $t('common.actions.delete') }}</Button>
                            </div>
                        </div>
                    </div>

                    <!-- CSP Table -->
                    <CardContent class="p-0">
                        <div class="relative w-full overflow-auto">
                            <table class="w-full divide-y divide-border">
                                <thead class="bg-muted/50">
                                    <tr>
                                        <th class="w-12 px-4 py-3 text-left">
                                            <Checkbox 
                                                :checked="selectedCspReports.length === cspReports.length && cspReports.length > 0"
                                                @update:checked="toggleAllCspReports"
                                            />
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.cspReports.table.directive') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.cspReports.table.blockedUri') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.cspReports.table.documentUri') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.cspReports.table.ip') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.cspReports.table.status') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.cspReports.table.date') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-if="cspLoading">
                                        <td colspan="7" class="h-24 text-center text-muted-foreground">
                                            <Loader2 class="w-6 h-6 animate-spin mx-auto mb-2" />
                                            {{ $t('common.labels.loading') }}
                                        </td>
                                    </tr>
                                    <tr v-else-if="cspReports.length === 0">
                                        <td colspan="7" class="h-24 text-center text-muted-foreground">
                                            {{ $t('features.security.cspReports.empty') }}
                                        </td>
                                    </tr>
                                    <tr v-for="report in cspReports" :key="report.id" class="hover:bg-muted/50 transition-colors">
                                        <td class="px-4 py-3">
                                            <Checkbox 
                                                :checked="selectedCspReports.includes(report.id)"
                                                @update:checked="(checked) => handleSelectCspReport(checked, report.id)"
                                            />
                                        </td>
                                        <td class="px-4 py-3">
                                            <Badge variant="destructive">{{ report.violated_directive }}</Badge>
                                        </td>
                                        <td class="px-4 py-3 text-sm max-w-xs truncate" :title="report.blocked_uri">{{ report.blocked_uri || 'N/A' }}</td>
                                        <td class="px-4 py-3 text-sm text-muted-foreground max-w-xs truncate" :title="report.document_uri">{{ report.document_uri }}</td>
                                        <td class="px-4 py-3 font-mono text-sm">{{ report.ip_address }}</td>
                                        <td class="px-4 py-3">
                                            <Badge :variant="getCspStatusVariant(report.status)">{{ getCspStatusLabel(report.status) }}</Badge>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-muted-foreground whitespace-nowrap">{{ formatDate(report.created_at) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                    <Pagination
                        v-if="cspPagination.total > 0"
                        :current-page="cspPagination.current_page"
                        :total-items="cspPagination.total"
                        :per-page="cspFilters.per_page"
                        @page-change="(val) => { cspFilters.page = val; fetchCspReports(); }"
                        class="border-none shadow-none px-6 py-4"
                    />
                </Card>
            </TabsContent>

            <!-- Slow Queries Tab -->
            <TabsContent value="slow-queries">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-4">
                        <div>
                            <CardTitle class="text-lg">{{ $t('features.security.slowQueries.title') }}</CardTitle>
                            <CardDescription>{{ $t('features.security.slowQueries.description') }}</CardDescription>
                        </div>
                        <Button @click="fetchSlowQueries" variant="outline" size="sm" :disabled="slowQueryLoading">
                            <Loader2 v-if="slowQueryLoading" class="w-4 h-4 mr-2 animate-spin" />
                            <RefreshCw v-else class="w-4 h-4 mr-2" />
                            {{ $t('common.actions.refresh') }}
                        </Button>
                    </CardHeader>

                    <!-- Slow Query Statistics -->
                    <div class="px-6 pb-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-muted/30 rounded-lg p-4">
                            <p class="text-sm text-muted-foreground">{{ $t('features.security.slowQueries.stats.total') }}</p>
                            <p class="text-2xl font-bold text-foreground">{{ slowQueryStats.total || 0 }}</p>
                        </div>
                        <div class="bg-muted/30 rounded-lg p-4">
                            <p class="text-sm text-muted-foreground">{{ $t('features.security.slowQueries.stats.avgDuration') }}</p>
                            <p class="text-2xl font-bold text-orange-600">{{ slowQueryStats.avg_duration || 0 }}ms</p>
                        </div>
                        <div class="bg-muted/30 rounded-lg p-4">
                            <p class="text-sm text-muted-foreground">{{ $t('features.security.slowQueries.stats.maxDuration') }}</p>
                            <p class="text-2xl font-bold text-red-600">{{ slowQueryStats.max_duration || 0 }}ms</p>
                        </div>
                        <div class="bg-muted/30 rounded-lg p-4">
                            <p class="text-sm text-muted-foreground">{{ $t('features.security.slowQueries.stats.today') }}</p>
                            <p class="text-2xl font-bold text-blue-600">{{ slowQueryStats.today || 0 }}</p>
                        </div>
                    </div>

                    <!-- Slow Query Filters -->
                    <div class="px-6 pb-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <Label class="mb-2 block">{{ $t('features.security.slowQueries.filters.route') }}</Label>
                                <Input v-model="slowQueryFilters.route" placeholder="e.g. api/v1/contents" />
                            </div>
                            <div>
                                <Label class="mb-2 block">{{ $t('features.security.slowQueries.filters.minDuration') }}</Label>
                                <Input v-model.number="slowQueryFilters.min_duration" type="number" placeholder="1000" />
                            </div>
                            <div>
                                <Label class="mb-2 block">{{ $t('features.security.slowQueries.filters.dateFrom') }}</Label>
                                <Input v-model="slowQueryFilters.date_from" type="date" />
                            </div>
                            <div>
                                <Label class="mb-2 block">{{ $t('features.security.slowQueries.filters.dateTo') }}</Label>
                                <Input v-model="slowQueryFilters.date_to" type="date" />
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 mt-4">
                            <Button @click="applySlowQueryFilters" size="sm">{{ $t('common.actions.apply') }}</Button>
                            <Button @click="resetSlowQueryFilters" variant="outline" size="sm">{{ $t('common.actions.reset') }}</Button>
                        </div>
                    </div>

                    <!-- Slow Query Table -->
                    <CardContent class="p-0">
                        <div class="relative w-full overflow-auto">
                            <table class="w-full divide-y divide-border">
                                <thead class="bg-muted/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.slowQueries.table.route') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.slowQueries.table.duration') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.slowQueries.table.user') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.slowQueries.table.query') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.slowQueries.table.date') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-if="slowQueryLoading">
                                        <td colspan="5" class="h-24 text-center text-muted-foreground">
                                            <Loader2 class="w-6 h-6 animate-spin mx-auto mb-2" />
                                            {{ $t('common.labels.loading') }}
                                        </td>
                                    </tr>
                                    <tr v-else-if="slowQueries.length === 0">
                                        <td colspan="5" class="h-24 text-center text-muted-foreground">
                                            <ShieldCheck class="w-12 h-12 mx-auto mb-2 text-green-500" />
                                            {{ $t('features.security.slowQueries.empty') }}
                                        </td>
                                    </tr>
                                    <tr v-for="query in slowQueries" :key="query.id" class="hover:bg-muted/50 transition-colors">
                                        <td class="px-4 py-3 font-mono text-sm">{{ query.route || 'N/A' }}</td>
                                        <td class="px-4 py-3">
                                            <Badge :variant="getSlowQueryDurationVariant(query.duration)">{{ query.duration }}ms</Badge>
                                        </td>
                                        <td class="px-4 py-3 text-sm">{{ query.user?.name || 'Guest' }}</td>
                                        <td class="px-4 py-3">
                                            <code class="text-xs bg-muted px-2 py-1 rounded block truncate max-w-md" :title="query.query">{{ query.query }}</code>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-muted-foreground whitespace-nowrap">{{ formatDate(query.created_at) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                    <Pagination
                        v-if="slowQueryPagination.total > 0"
                        :current-page="slowQueryPagination.current_page"
                        :total-items="slowQueryPagination.total"
                        :per-page="slowQueryFilters.per_page"
                        @page-change="(val) => { slowQueryFilters.page = val; fetchSlowQueries(); }"
                        class="border-none shadow-none px-6 py-4"
                    />
                </Card>
            </TabsContent>

            <!-- Dependency Vulnerabilities Tab -->
            <TabsContent value="vulnerabilities">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-4">
                        <div>
                            <CardTitle class="text-lg">{{ $t('features.security.vulnerabilities.title') }}</CardTitle>
                            <CardDescription>{{ $t('features.security.vulnerabilities.description') }}</CardDescription>
                        </div>
                        <div class="flex gap-2">
                            <Button @click="runDependencyAudit" variant="default" size="sm" :disabled="auditRunning">
                                <Loader2 v-if="auditRunning" class="w-4 h-4 mr-2 animate-spin" />
                                <ShieldAlert v-else class="w-4 h-4 mr-2" />
                                {{ $t('features.security.vulnerabilities.runAudit') }}
                            </Button>
                            <Button @click="fetchVulnerabilities" variant="outline" size="sm" :disabled="vulnLoading">
                                <RefreshCw class="w-4 h-4 mr-2" />
                                {{ $t('common.actions.refresh') }}
                            </Button>
                        </div>
                    </CardHeader>

                    <!-- Vulnerability Statistics -->
                    <div class="px-6 pb-4 grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div class="bg-muted/30 rounded-lg p-4">
                            <p class="text-sm text-muted-foreground">{{ $t('features.security.vulnerabilities.stats.total') }}</p>
                            <p class="text-2xl font-bold text-foreground">{{ vulnStats.total || 0 }}</p>
                        </div>
                        <div class="bg-muted/30 rounded-lg p-4">
                            <p class="text-sm text-muted-foreground">{{ $t('features.security.vulnerabilities.stats.critical') }}</p>
                            <p class="text-2xl font-bold text-foreground">{{ vulnStats.critical || 0 }}</p>
                        </div>
                        <div class="bg-muted/30 rounded-lg p-4">
                            <p class="text-sm text-muted-foreground">{{ $t('features.security.vulnerabilities.stats.high') }}</p>
                            <p class="text-2xl font-bold text-foreground">{{ vulnStats.high || 0 }}</p>
                        </div>
                        <div class="bg-muted/30 rounded-lg p-4">
                            <p class="text-sm text-muted-foreground">{{ $t('features.security.vulnerabilities.stats.medium') }}</p>
                            <p class="text-2xl font-bold text-foreground">{{ vulnStats.medium || 0 }}</p>
                        </div>
                        <div class="bg-muted/30 rounded-lg p-4">
                            <p class="text-sm text-muted-foreground">{{ $t('features.security.vulnerabilities.stats.low') }}</p>
                            <p class="text-2xl font-bold text-foreground">{{ vulnStats.low || 0 }}</p>
                        </div>
                    </div>

                    <!-- Vulnerability Filters -->
                    <div class="px-6 pb-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <Label class="mb-2 block">{{ $t('features.security.vulnerabilities.filters.source') }}</Label>
                                <Select v-model="vulnFilters.source">
                                    <SelectTrigger>
                                        <SelectValue :placeholder="$t('common.labels.all')" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{{ $t('common.labels.all') }}</SelectItem>
                                        <SelectItem value="composer">{{ $t('features.security.vulnerabilities.source.composer') }}</SelectItem>
                                        <SelectItem value="npm">{{ $t('features.security.vulnerabilities.source.npm') }}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <Label class="mb-2 block">{{ $t('features.security.vulnerabilities.filters.severity') }}</Label>
                                <Select v-model="vulnFilters.severity">
                                    <SelectTrigger>
                                        <SelectValue :placeholder="$t('common.labels.all')" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{{ $t('common.labels.all') }}</SelectItem>
                                        <SelectItem value="critical">{{ $t('features.security.vulnerabilities.severity.critical') }}</SelectItem>
                                        <SelectItem value="high">{{ $t('features.security.vulnerabilities.severity.high') }}</SelectItem>
                                        <SelectItem value="medium">{{ $t('features.security.vulnerabilities.severity.medium') }}</SelectItem>
                                        <SelectItem value="low">{{ $t('features.security.vulnerabilities.severity.low') }}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <Label class="mb-2 block">{{ $t('features.security.vulnerabilities.filters.status') }}</Label>
                                <Select v-model="vulnFilters.status">
                                    <SelectTrigger>
                                        <SelectValue :placeholder="$t('common.labels.all')" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{{ $t('common.labels.all') }}</SelectItem>
                                        <SelectItem value="new">{{ $t('features.security.vulnerabilities.status.new') }}</SelectItem>
                                        <SelectItem value="acknowledged">{{ $t('features.security.vulnerabilities.status.acknowledged') }}</SelectItem>
                                        <SelectItem value="patched">{{ $t('features.security.vulnerabilities.status.patched') }}</SelectItem>
                                        <SelectItem value="ignored">{{ $t('features.security.vulnerabilities.status.ignored') }}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <Label class="mb-2 block">{{ $t('features.security.vulnerabilities.filters.package') }}</Label>
                                <Input v-model="vulnFilters.package" :placeholder="$t('features.security.vulnerabilities.filters.searchPackage')" />
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 mt-4">
                            <Button @click="applyVulnFilters" size="sm">{{ $t('common.actions.apply') }}</Button>
                            <Button @click="resetVulnFilters" variant="outline" size="sm">{{ $t('common.actions.reset') }}</Button>
                        </div>
                    </div>

                    <!-- Vulnerability Table -->
                    <CardContent class="p-0">
                        <div class="relative w-full overflow-auto">
                            <table class="w-full divide-y divide-border">
                                <thead class="bg-muted/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.vulnerabilities.table.package') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.vulnerabilities.table.version') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.vulnerabilities.table.severity') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.vulnerabilities.table.cve') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.vulnerabilities.table.source') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.vulnerabilities.table.status') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.security.vulnerabilities.table.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-if="vulnLoading">
                                        <td colspan="7" class="h-24 text-center text-muted-foreground">
                                            <Loader2 class="w-6 h-6 animate-spin mx-auto mb-2" />
                                            {{ $t('common.labels.loading') }}
                                        </td>
                                    </tr>
                                    <tr v-else-if="vulnerabilities.length === 0">
                                        <td colspan="7" class="h-24 text-center text-muted-foreground">
                                            <ShieldCheck class="w-12 h-12 mx-auto mb-2 text-green-500" />
                                            {{ $t('features.security.vulnerabilities.empty') }}
                                        </td>
                                    </tr>
                                    <tr v-for="vuln in vulnerabilities" :key="vuln.id" class="hover:bg-muted/50 transition-colors">
                                        <td class="px-4 py-3 font-medium">{{ vuln.package_name }}</td>
                                        <td class="px-4 py-3 font-mono text-sm">{{ vuln.version }}</td>
                                        <td class="px-4 py-3">
                                            <Badge :variant="getVulnSeverityVariant(vuln.severity)">{{ $t('features.security.vulnerabilities.severity.' + vuln.severity) }}</Badge>
                                        </td>
                                        <td class="px-4 py-3">
                                            <a v-if="vuln.cve" :href="`https://nvd.nist.gov/vuln/detail/${vuln.cve}`" target="_blank" class="text-primary hover:underline text-sm">{{ vuln.cve }}</a>
                                         <span v-else class="text-muted-foreground">-</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <Badge variant="outline">{{ $t('features.security.vulnerabilities.source.' + vuln.source) }}</Badge>
                                        </td>
                                        <td class="px-4 py-3">
                                            <Badge :variant="getVulnStatusVariant(vuln.status)">{{ $t('features.security.vulnerabilities.status.' + vuln.status) }}</Badge>
                                        </td>
                                        <td class="px-4 py-3">
                                            <Select @update:model-value="(value) => updateVulnStatus(vuln, value)" :model-value="vuln.status">
                                                <SelectTrigger class="w-32 h-8">
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="new">{{ $t('features.security.vulnerabilities.status.new') }}</SelectItem>
                                                    <SelectItem value="acknowledged">{{ $t('features.security.vulnerabilities.status.acknowledged') }}</SelectItem>
                                                    <SelectItem value="patched">{{ $t('features.security.vulnerabilities.status.patched') }}</SelectItem>
                                                    <SelectItem value="ignored">{{ $t('features.security.vulnerabilities.status.ignored') }}</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                    <Pagination
                        v-if="vulnPagination.total > 0"
                        :current-page="vulnPagination.current_page"
                        :total-items="vulnPagination.total"
                        :per-page="vulnFilters.per_page"
                        @page-change="(val) => { vulnFilters.page = val; fetchVulnerabilities(); }"
                        class="border-none shadow-none px-6 py-4"
                    />
                </Card>
            </TabsContent>
        </Tabs>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { useToast } from '../../../composables/useToast';
import { useConfirm } from '../../../composables/useConfirm';
import { parseResponse, ensureArray, parseSingleResponse } from '../../../utils/responseParser';
import Tabs from '../../../components/ui/tabs.vue';
import TabsList from '../../../components/ui/tabs-list.vue';
import TabsTrigger from '../../../components/ui/tabs-trigger.vue';
import TabsContent from '../../../components/ui/tabs-content.vue';
import Card from '../../../components/ui/card.vue';
import CardHeader from '../../../components/ui/card-header.vue';
import CardTitle from '../../../components/ui/card-title.vue';
import CardDescription from '../../../components/ui/card-description.vue';
import CardContent from '../../../components/ui/card-content.vue';
import Pagination from '../../../components/ui/pagination.vue';
import Button from '../../../components/ui/button.vue';
import Input from '../../../components/ui/input.vue';
import Label from '../../../components/ui/label.vue';
import Checkbox from '../../../components/ui/checkbox.vue';
import Badge from '../../../components/ui/badge.vue';
import Select from '../../../components/ui/select.vue';
import SelectTrigger from '../../../components/ui/select-trigger.vue';
import SelectValue from '../../../components/ui/select-value.vue';
import SelectContent from '../../../components/ui/select-content.vue';
import SelectItem from '../../../components/ui/select-item.vue';
import Table from '../../../components/ui/table.vue';
import TableHeader from '../../../components/ui/table-header.vue';
import TableBody from '../../../components/ui/table-body.vue';
import TableHead from '../../../components/ui/table-head.vue';
import TableRow from '../../../components/ui/table-row.vue';
import TableCell from '../../../components/ui/table-cell.vue';
import { 
    ShieldAlert, 
    ShieldX, 
    ShieldCheck, 
    UserX, 
    Trash2, 
    RefreshCw, 
    Search, 
    Loader2, 
    ArrowLeft,
    Plus
} from 'lucide-vue-next';

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();
// Data
const logs = ref([]);
const statistics = ref(null);
const blocklist = ref([]);
const whitelist = ref([]);
const loading = ref(false);

// UI State
const activeTab = ref('overview');
const logFilter = ref('all');
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
const logsPerPage = ref(25);
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

// Validation
const isValidBlockIp = computed(() => !!ipToBlock.value?.trim());
const isValidCheckIp = computed(() => !!ipToCheck.value?.trim());
const isValidWhitelistIp = computed(() => !!ipToWhitelist.value?.trim());

// Tabs
const tabs = computed(() => [
    { key: 'overview', label: t('features.security.tabs.overview') },
    { key: 'blocklist', label: t('features.security.tabs.blocklist') },
    { key: 'whitelist', label: t('features.security.tabs.whitelist') },
]);

// Filtered logs
const filteredLogs = computed(() => {
    let filtered = logs.value;
    
    if (logFilter.value && logFilter.value !== 'all') {
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
        const response = await api.get('/admin/ja/security/logs', {
            params: { per_page: logsPerPage.value }
        });
        const result = parseResponse(response);
        logs.value = result.data || [];
    } catch (error) {
        console.error('Failed to fetch logs:', error);
    } finally {
        loading.value = false;
    }
};

const clearLogs = async () => {
    const confirmed = await confirm({
        title: t('features.system.logs.actions.clear'),
        message: t('features.system.logs.confirm.clear'),
        variant: 'danger',
        confirmText: t('common.actions.clear'),
    });

    if (!confirmed) return;

    try {
        await api.delete('/admin/ja/security/logs');
        toast.success.action(t('features.system.logs.messages.cleared'));
        fetchLogs();
    } catch (error) {
        console.error('Failed to clear logs:', error);
        toast.error.fromResponse(error);
    }
};

const fetchStats = async () => {
    try {
        const response = await api.get('/admin/ja/security/stats');
        statistics.value = parseSingleResponse(response) || {};
    } catch (error) {
        console.error('Failed to fetch stats:', error);
    }
};

const fetchBlocklist = async () => {
    try {
        const response = await api.get('/admin/ja/security/blocklist');
        blocklist.value = ensureArray(parseSingleResponse(response));
    } catch (error) {
        console.error('Failed to fetch blocklist:', error);
    }
};

const fetchWhitelist = async () => {
    try {
        const response = await api.get('/admin/ja/security/whitelist');
        whitelist.value = ensureArray(parseSingleResponse(response));
    } catch (error) {
        console.error('Failed to fetch whitelist:', error);
    }
};

// IP Actions
const blockIP = async () => {
    if (!ipToBlock.value) {
        toast.error(t('features.security.messages.enterIp'));
        return;
    }

    const confirmed = await confirm({
        title: t('features.security.ipManagement.block.button'),
        message: t('features.security.messages.confirmBlock', { ip: ipToBlock.value }),
        variant: 'danger',
        confirmText: t('features.security.ipManagement.block.button'),
    });

    if (!confirmed) return;

    try {
        await api.post('/admin/ja/security/block-ip', { ip_address: ipToBlock.value });
        toast.success.action(t('features.security.messages.blockSuccess'));
        ipToBlock.value = '';
        await fetchBlocklist();
        await fetchLogs();
    } catch (error) {
        console.error('Failed to block IP:', error);
        toast.error.fromResponse(error);
    }
};

const checkIPStatus = async () => {
    if (!ipToCheck.value) {
        toast.error(t('features.security.messages.enterIp'));
        return;
    }

    try {
        const response = await api.get('/admin/ja/security/check-ip', { params: { ip_address: ipToCheck.value } });
        ipStatus.value = parseSingleResponse(response) || {};
    } catch (error) {
        console.error('Failed to check IP status:', error);
        toast.error.fromResponse(error);
    }
};

const unblockIP = async () => {
    if (!ipToUnblock.value) {
        toast.error(t('features.security.messages.enterIp'));
        return;
    }

    const confirmed = await confirm({
        title: t('features.security.ipManagement.unblock.button'),
        message: t('features.security.messages.confirmUnblock', { ip: ipToUnblock.value }),
        variant: 'warning',
        confirmText: t('features.security.ipManagement.unblock.button'),
    });

    if (!confirmed) return;

    try {
        await api.post('/admin/ja/security/unblock-ip', { ip_address: ipToUnblock.value });
        await api.post('/admin/ja/security/clear-failed-attempts', { ip_address: ipToUnblock.value });
        toast.success.action(t('features.security.messages.unblockSuccess'));
        ipToUnblock.value = '';
        await fetchBlocklist();
        await fetchLogs();
    } catch (error) {
        console.error('Failed to unblock IP:', error);
        toast.error.fromResponse(error);
    }
};

const blockIPFromLog = async (ip) => {
    const confirmed = await confirm({
        title: t('features.security.logs.actions.blockIp'),
        message: t('features.security.messages.confirmBlock', { ip }),
        variant: 'danger',
        confirmText: t('features.security.logs.actions.blockIp'),
    });

    if (!confirmed) return;

    try {
        await api.post('/admin/ja/security/block-ip', { ip_address: ip });
        toast.success.action(t('features.security.messages.blockSuccess'));
        await fetchBlocklist();
        await fetchLogs();
    } catch (error) {
        console.error('Failed to block IP:', error);
        toast.error.fromResponse(error);
    }
};

// Bulk actions for logs
const bulkBlockFromLogs = async () => {
    if (selectedLogIds.value.length === 0) return;
    
    const confirmed = await confirm({
        title: t('features.security.bulkActions.blockSelected'),
        message: t('features.security.messages.confirmBulkBlock', { count: selectedLogIds.value.length }),
        variant: 'danger',
        confirmText: t('features.security.bulkActions.blockSelected'),
    });

    if (!confirmed) return;

    try {
        await api.post('/admin/ja/security/bulk-block', { ips: selectedLogIds.value });
        toast.success.action(t('features.security.messages.bulkBlockSuccess'));
        selectedLogIds.value = [];
        await fetchBlocklist();
        await fetchLogs();
    } catch (error) {
        console.error('Failed to bulk block:', error);
        toast.error.fromResponse(error);
    }
};

const toggleAllLogs = (checked) => {
    if (checked) {
        selectedLogIds.value = paginatedLogs.value.map(log => log.ip_address);
    } else {
        selectedLogIds.value = [];
    }
};

const handleSelectLog = (checked, ip) => {
    if (checked) {
        selectedLogIds.value.push(ip);
    } else {
        selectedLogIds.value = selectedLogIds.value.filter(id => id !== ip);
    }
};

// Blocklist Actions
const removeFromBlocklist = async (ip) => {
    const confirmed = await confirm({
        title: t('features.security.blocklist.actions.unblock'),
        message: t('features.security.messages.confirmUnblock', { ip }),
        variant: 'warning',
        confirmText: t('features.security.blocklist.actions.unblock'),
    });

    if (!confirmed) return;

    try {
        await api.post('/admin/ja/security/unblock-ip', { ip_address: ip });
        toast.success.action(t('features.security.messages.unblockSuccess'));
        await fetchBlocklist();
    } catch (error) {
        console.error('Failed to remove from blocklist:', error);
        toast.error.fromResponse(error);
    }
};

const moveToWhitelist = async (ip) => {
    const confirmed = await confirm({
        title: t('features.security.blocklist.actions.moveToWhitelist'),
        message: t('features.security.messages.confirmMoveToWhitelist', { ip }),
        variant: 'default',
        confirmText: t('common.actions.move'),
    });

    if (!confirmed) return;

    try {
        await api.post('/admin/ja/security/unblock-ip', { ip_address: ip });
        await api.post('/admin/ja/security/whitelist-ip', { ip_address: ip });
        toast.success.action(t('features.security.messages.movedToWhitelist'));
        await fetchBlocklist();
        await fetchWhitelist();
    } catch (error) {
        console.error('Failed to move to whitelist:', error);
        toast.error.fromResponse(error);
    }
};

const bulkUnblock = async () => {
    if (selectedBlocklistIds.value.length === 0) return;
    
    const confirmed = await confirm({
        title: t('features.security.bulkActions.unblockSelected'),
        message: t('features.security.messages.confirmBulkUnblock', { count: selectedBlocklistIds.value.length }),
        variant: 'warning',
        confirmText: t('features.security.bulkActions.unblockSelected'),
    });

    if (!confirmed) return;

    try {
        await api.post('/admin/ja/security/bulk-unblock', { ips: selectedBlocklistIds.value });
        toast.success.action(t('features.security.messages.bulkUnblockSuccess'));
        selectedBlocklistIds.value = [];
        await fetchBlocklist();
    } catch (error) {
        console.error('Failed to bulk unblock:', error);
        toast.error.fromResponse(error);
    }
};

const toggleAllBlocklist = (checked) => {
    if (checked) {
        selectedBlocklistIds.value = paginatedBlocklist.value.map(item => item.ip_address);
    } else {
        selectedBlocklistIds.value = [];
    }
};

const handleSelectBlocklist = (checked, ip) => {
    if (checked) {
        selectedBlocklistIds.value.push(ip);
    } else {
        selectedBlocklistIds.value = selectedBlocklistIds.value.filter(id => id !== ip);
    }
};

// Whitelist Actions
const addToWhitelist = async (ip) => {
    if (!ip) {
        toast.error(t('features.security.messages.enterIp'));
        return;
    }

    try {
        await api.post('/admin/ja/security/whitelist-ip', { ip_address: ip });
        toast.success.action(t('features.security.messages.whitelistSuccess'));
        ipToWhitelist.value = '';
        await fetchWhitelist();
    } catch (error) {
        console.error('Failed to add to whitelist:', error);
        toast.error.fromResponse(error);
    }
};

const removeFromWhitelist = async (ip) => {
    const confirmed = await confirm({
        title: t('features.security.whitelist.actions.remove'),
        message: t('features.security.messages.confirmRemoveWhitelist', { ip }),
        variant: 'destructive',
        confirmText: t('common.actions.remove'),
    });

    if (!confirmed) return;

    try {
        await api.post('/admin/ja/security/remove-whitelist', { data: { ip_address: ip } });
        toast.success.action(t('features.security.messages.whitelistRemoveSuccess'));
        await fetchWhitelist();
    } catch (error) {
        console.error('Failed to remove from whitelist:', error);
        toast.error.fromResponse(error);
    }
};

const bulkRemoveWhitelist = async () => {
    if (selectedWhitelistIds.value.length === 0) return;
    
    const confirmed = await confirm({
        title: t('features.security.bulkActions.removeSelected'),
        message: t('features.security.messages.confirmBulkRemoveWhitelist', { count: selectedWhitelistIds.value.length }),
        variant: 'destructive',
        confirmText: t('common.actions.remove'),
    });

    if (!confirmed) return;

    try {
        await api.post('/admin/ja/security/bulk-remove-whitelist', { ips: selectedWhitelistIds.value });
        toast.success.action(t('features.security.messages.bulkWhitelistRemoveSuccess'));
        selectedWhitelistIds.value = [];
        await fetchWhitelist();
    } catch (error) {
        console.error('Failed to bulk remove whitelist:', error);
        toast.error.fromResponse(error);
    }
};

const toggleAllWhitelist = (checked) => {
    if (checked) {
        selectedWhitelistIds.value = paginatedWhitelist.value.map(item => item.ip_address);
    } else {
        selectedWhitelistIds.value = [];
    }
};

const handleSelectWhitelist = (checked, ip) => {
    if (checked) {
        selectedWhitelistIds.value.push(ip);
    } else {
        selectedWhitelistIds.value = selectedWhitelistIds.value.filter(id => id !== ip);
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
        'login_failed': 'bg-orange-500/10 text-orange-600 dark:text-orange-400 border-orange-500/20',
        'login_success': 'bg-green-500/10 text-green-600 dark:text-green-400 border-green-500/20',
        'ip_blocked': 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20',
        'ip_unblocked': 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
        'suspicious_activity': 'bg-orange-500/10 text-orange-600 dark:text-orange-400 border-orange-500/20',
        'ip_whitelisted': 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
        'ip_whitelist_removed': 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
        'account_locked': 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20',
        'login_blocked': 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20',
        'ip_blocked_permanent': 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20',
        'ip_blocked_temp': 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20',
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

// ========================================
// CSP REPORTS TAB
// ========================================
const cspReports = ref([]);
const cspStats = ref({});
const cspLoading = ref(false);
const selectedCspReports = ref([]);
const cspFilters = ref({ status: 'all', directive: '', date_from: '', date_to: '', page: 1, per_page: 50 });
const cspPagination = ref({ total: 0, current_page: 1, last_page: 1 });

const cspTopViolation = computed(() => {
    if (!cspStats.value.by_directive || cspStats.value.by_directive.length === 0) return 'None';
    return cspStats.value.by_directive[0].violated_directive;
});

const cspRecentCount = computed(() => {
    if (!cspStats.value.recent_trend) return 0;
    const lastDay = cspStats.value.recent_trend[cspStats.value.recent_trend.length - 1];
    return lastDay ? lastDay.count : 0;
});

const fetchCspReports = async () => {
    cspLoading.value = true;
    try {
        const params = { ...cspFilters.value };
        if (params.status === 'all') params.status = '';
        
        const response = await api.get('/admin/ja/security/csp-reports', { params });
        const result = response.data?.data ? response.data.data : response.data;
        cspReports.value = result.data || [];
        cspPagination.value = {
            total: result.total || 0,
            current_page: result.current_page || 1,
            last_page: result.last_page || 1,
        };
    } catch (error) {
        console.error('Failed to fetch CSP reports:', error);
    } finally {
        cspLoading.value = false;
    }
};

const fetchCspStats = async () => {
    try {
        const response = await api.get('/admin/ja/security/csp-reports/statistics');
        cspStats.value = response.data?.data || {};
    } catch (error) {
        console.error('Failed to fetch CSP stats:', error);
    }
};

const applyCspFilters = () => { cspFilters.value.page = 1; fetchCspReports(); };
const resetCspFilters = () => { cspFilters.value = { status: 'all', directive: '', date_from: '', date_to: '', page: 1, per_page: 50 }; fetchCspReports(); };

const toggleAllCspReports = (checked) => {
    selectedCspReports.value = checked ? cspReports.value.map(r => r.id) : [];
};

const handleSelectCspReport = (checked, id) => {
    if (checked) {
        selectedCspReports.value.push(id);
    } else {
        selectedCspReports.value = selectedCspReports.value.filter(i => i !== id);
    }
};

const cspBulkAction = async (action) => {
    if (selectedCspReports.value.length === 0) return;
    const confirmed = await confirm({
        title: t('common.actions.confirm'),
        message: t('features.security.cspReports.confirmBulkAction', { count: selectedCspReports.value.length, action: action.replace('_', ' ') }),
        variant: 'danger',
        confirmText: t('common.actions.confirm'),
    });
    if (!confirmed) return;
    try {
        await api.post('/admin/ja/security/csp-reports/bulk-action', { ids: selectedCspReports.value, action });
        toast.success.action(t('common.messages.success.actionSuccess', { item: 'Reports', action: action.replace('_', ' ') }));
        selectedCspReports.value = [];
        fetchCspReports();
        fetchCspStats();
    } catch (error) {
        toast.error.fromResponse(error);
    }
};

const getCspStatusVariant = (status) => {
    const variants = { new: 'warning', reviewed: 'info', false_positive: 'secondary' };
    return variants[status] || 'secondary';
};

const getCspStatusLabel = (status) => {
    const labels = { new: t('features.security.cspReports.status.new'), reviewed: t('features.security.cspReports.status.reviewed'), false_positive: t('features.security.cspReports.status.falsePositive') };
    return labels[status] || status;
};

// ========================================
// SLOW QUERIES TAB
// ========================================
const slowQueries = ref([]);
const slowQueryStats = ref({});
const slowQueryLoading = ref(false);
const slowQueryFilters = ref({ route: '', min_duration: '', date_from: '', date_to: '', page: 1, per_page: 50 });
const slowQueryPagination = ref({ total: 0, current_page: 1, last_page: 1 });

const fetchSlowQueries = async () => {
    slowQueryLoading.value = true;
    try {
        const response = await api.get('/admin/ja/security/slow-queries', { params: slowQueryFilters.value });
        slowQueries.value = response.data?.data?.data || [];
        slowQueryPagination.value = {
            total: response.data?.data?.total || 0,
            current_page: response.data?.data?.current_page || 1,
            last_page: response.data?.data?.last_page || 1,
        };
    } catch (error) {
        console.error('Failed to fetch slow queries:', error);
    } finally {
        slowQueryLoading.value = false;
    }
};

const fetchSlowQueryStats = async () => {
    try {
        const response = await api.get('/admin/ja/security/slow-queries/statistics');
        slowQueryStats.value = response.data?.data || {};
    } catch (error) {
        console.error('Failed to fetch slow query stats:', error);
    }
};

const applySlowQueryFilters = () => { slowQueryFilters.value.page = 1; fetchSlowQueries(); };
const resetSlowQueryFilters = () => { slowQueryFilters.value = { route: '', min_duration: '', date_from: '', date_to: '', page: 1, per_page: 50 }; fetchSlowQueries(); };

const getSlowQueryDurationVariant = (duration) => {
    if (duration >= 5000) return 'destructive';
    if (duration >= 2000) return 'warning';
    return 'secondary';
};

// ========================================
// DEPENDENCY VULNERABILITIES TAB
// ========================================
const vulnerabilities = ref([]);
const vulnLoading = ref(false);
const auditRunning = ref(false);
const vulnFilters = ref({ source: 'all', severity: 'all', status: 'all', package: '', page: 1, per_page: 50 });
const vulnPagination = ref({ total: 0, current_page: 1, last_page: 1 });

const vulnStats = ref({
    total: 0,
    critical: 0,
    high: 0,
    medium: 0,
    low: 0
});

const fetchVulnStats = async () => {
    try {
        const response = await api.get('/admin/ja/security/dependency-vulnerabilities/statistics');
        vulnStats.value = response.data?.data || {};
    } catch (error) {
        console.error('Failed to fetch vulnerability stats:', error);
    }
};

const fetchVulnerabilities = async () => {
    vulnLoading.value = true;
    try {
        const params = { ...vulnFilters.value };
        if (params.source === 'all') params.source = '';
        if (params.severity === 'all') params.severity = '';
        if (params.status === 'all') params.status = '';

        const response = await api.get('/admin/ja/security/dependency-vulnerabilities', { params });
        const result = response.data?.data ? response.data.data : response.data;
        vulnerabilities.value = result.data || [];
        vulnPagination.value = {
            total: result.total || 0,
            current_page: result.current_page || 1,
            last_page: result.last_page || 1,
        };
    } catch (error) {
        console.error('Failed to fetch vulnerabilities:', error);
    } finally {
        vulnLoading.value = false;
    }
};

const runDependencyAudit = async () => {
    auditRunning.value = true;
    try {
        await api.post('/admin/ja/security/run-dependency-audit');
        toast.success.action(t('features.security.vulnerabilities.auditCompleted'));
        fetchVulnerabilities();
        fetchVulnStats();
    } catch (error) {
        toast.error.fromResponse(error);
    } finally {
        auditRunning.value = false;
    }
};

const updateVulnStatus = async (vuln, status) => {
    try {
        await api.put(`/admin/ja/security/dependency-vulnerabilities/${vuln.id}`, { status });
        vuln.status = status;
        toast.success.action(t('common.messages.success.updated', { item: 'Status' }));
    } catch (error) {
        toast.error.fromResponse(error);
    }
};

const applyVulnFilters = () => { vulnFilters.value.page = 1; fetchVulnerabilities(); };
const resetVulnFilters = () => { vulnFilters.value = { source: 'all', severity: 'all', status: 'all', package: '', page: 1, per_page: 50 }; fetchVulnerabilities(); };

const getVulnSeverityVariant = (severity) => {
    const variants = { critical: 'destructive', high: 'warning', medium: 'secondary', low: 'outline' };
    return variants[severity] || 'secondary';
};

const getVulnStatusVariant = (status) => {
    const variants = { new: 'destructive', acknowledged: 'warning', patched: 'secondary', ignored: 'outline' };
    return variants[status] || 'secondary';
};

// Watch for tab changes to load data on demand
watch(activeTab, (newTab) => {
    if (newTab === 'csp-reports' && cspReports.value.length === 0) {
        fetchCspReports();
        fetchCspStats();
    } else if (newTab === 'slow-queries' && slowQueries.value.length === 0) {
        fetchSlowQueries();
        fetchSlowQueryStats();
    } else if (newTab === 'vulnerabilities' && vulnerabilities.value.length === 0) {
        fetchVulnerabilities();
        fetchVulnStats();
    }
});
</script>
