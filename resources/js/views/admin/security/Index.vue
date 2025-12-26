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
                                >
                                    {{ $t('features.security.ipManagement.check.button') }}
                                </Button>
                            </div>
                            <div v-if="ipStatus" class="mt-2">
                                <Badge
                                    :variant="ipStatus.is_blocked ? 'destructive' : 'default'"
                                    class="w-full justify-center py-2"
                                    :class="!ipStatus.is_blocked ? 'bg-green-500/10 text-green-600 hover:bg-green-500/20' : ''"
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
                        <Badge v-if="selectedLogIds.length > 0" variant="secondary">
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
                                <SelectItem value="">{{ $t('features.security.logs.all') }}</SelectItem>
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
                                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ $t('features.security.logs.table.event') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ $t('features.security.logs.table.ip') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ $t('features.security.logs.table.user') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ $t('features.security.logs.table.details') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ $t('features.security.logs.table.date') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ $t('features.security.logs.table.actions') }}</th>
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
                <div class="px-6 py-4 border-t flex items-center justify-between">
                    <div class="text-sm text-muted-foreground">
                        {{ $t('common.pagination.showing') }} {{ logsStartIndex + 1 }} - {{ logsEndIndex }} {{ $t('common.pagination.of') }} {{ filteredLogs.length }}
                    </div>
                    <div class="flex items-center space-x-2">
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="logsCurrentPage === 1"
                            @click="logsCurrentPage--"
                        >
                            {{ $t('common.actions.previous') }}
                        </Button>
                        <Badge variant="secondary" class="h-8">
                            {{ logsCurrentPage }} / {{ logsTotalPages }}
                        </Badge>
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="logsCurrentPage >= logsTotalPages"
                            @click="logsCurrentPage++"
                        >
                            {{ $t('common.actions.next') }}
                        </Button>
                    </div>
                </div>
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
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ $t('features.security.blocklist.table.ip') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ $t('features.security.blocklist.table.reason') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ $t('features.security.blocklist.table.createdBy') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ $t('features.security.blocklist.table.date') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ $t('features.security.blocklist.table.actions') }}</th>
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
                    <div class="px-6 py-4 border-t flex items-center justify-between">
                        <div class="text-sm text-muted-foreground">
                            {{ $t('common.pagination.showing') }} {{ blocklistStartIndex + 1 }} - {{ blocklistEndIndex }} {{ $t('common.pagination.of') }} {{ blocklist.length }}
                        </div>
                        <div class="flex items-center space-x-2">
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="blocklistCurrentPage === 1"
                                @click="blocklistCurrentPage--"
                            >
                                {{ $t('common.actions.previous') }}
                            </Button>
                            <Badge variant="secondary" class="h-8">
                                {{ blocklistCurrentPage }} / {{ blocklistTotalPages }}
                            </Badge>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="blocklistCurrentPage >= blocklistTotalPages"
                                @click="blocklistCurrentPage++"
                            >
                                {{ $t('common.actions.next') }}
                            </Button>
                        </div>
                    </div>
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
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ $t('features.security.whitelist.table.ip') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ $t('features.security.whitelist.table.reason') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ $t('features.security.whitelist.table.createdBy') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ $t('features.security.whitelist.table.date') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ $t('features.security.whitelist.table.actions') }}</th>
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
                    <div class="px-6 py-4 border-t flex items-center justify-between">
                        <div class="text-sm text-muted-foreground">
                            {{ $t('common.pagination.showing') }} {{ whitelistStartIndex + 1 }} - {{ whitelistEndIndex }} {{ $t('common.pagination.of') }} {{ whitelist.length }}
                        </div>
                        <div class="flex items-center space-x-2">
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="whitelistCurrentPage === 1"
                                @click="whitelistCurrentPage--"
                            >
                                {{ $t('common.actions.previous') }}
                            </Button>
                            <Badge variant="secondary" class="h-8">
                                {{ whitelistCurrentPage }} / {{ whitelistTotalPages }}
                            </Badge>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="whitelistCurrentPage >= whitelistTotalPages"
                                @click="whitelistCurrentPage++"
                            >
                                {{ $t('common.actions.next') }}
                            </Button>
                        </div>
                    </div>
                </Card>
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
import Card from '../../../components/ui/card.vue';
import CardHeader from '../../../components/ui/card-header.vue';
import CardTitle from '../../../components/ui/card-title.vue';
import CardDescription from '../../../components/ui/card-description.vue';
import CardContent from '../../../components/ui/card-content.vue';
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
        const response = await api.get('/admin/cms/security/logs', {
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
    if (!confirm(t('features.system.logs.confirm.clear') || 'Are you sure you want to clear all logs?')) {
        return;
    }

    try {
        await api.post('/admin/cms/security/logs/clear');
        fetchLogs();
        fetchStats();
    } catch (error) {
        console.error('Failed to clear logs:', error);
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

const toggleAllLogs = (checked) => {
    if (checked) {
        selectedLogIds.value = paginatedLogs.value.map(log => log.ip_address);
    } else {
        selectedLogIds.value = [];
    }
};

const handleSelectLog = (checked, ip) => {
    if (checked) {
        if (!selectedLogIds.value.includes(ip)) {
            selectedLogIds.value.push(ip);
        }
    } else {
        selectedLogIds.value = selectedLogIds.value.filter(id => id !== ip);
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

const toggleAllBlocklist = (checked) => {
    if (checked) {
        selectedBlocklistIds.value = paginatedBlocklist.value.map(item => item.ip_address);
    } else {
        selectedBlocklistIds.value = [];
    }
};

const handleSelectBlocklist = (checked, ip) => {
    if (checked) {
        if (!selectedBlocklistIds.value.includes(ip)) {
            selectedBlocklistIds.value.push(ip);
        }
    } else {
        selectedBlocklistIds.value = selectedBlocklistIds.value.filter(id => id !== ip);
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

const toggleAllWhitelist = (checked) => {
    if (checked) {
        selectedWhitelistIds.value = paginatedWhitelist.value.map(item => item.ip_address);
    } else {
        selectedWhitelistIds.value = [];
    }
};

const handleSelectWhitelist = (checked, ip) => {
    if (checked) {
        if (!selectedWhitelistIds.value.includes(ip)) {
            selectedWhitelistIds.value.push(ip);
        }
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
