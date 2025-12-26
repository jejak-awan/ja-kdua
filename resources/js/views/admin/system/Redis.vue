<template>
  <div class="p-6 max-w-7xl mx-auto space-y-6">
    <div class="flex justify-between items-start">
      <div>
        <h1 class="text-2xl font-bold text-foreground">{{ $t('features.redis.title') }}</h1>
        <p class="text-muted-foreground">{{ $t('features.redis.description') }}</p>
      </div>
    </div>

    <!-- Shadcn Tabs -->
    <Tabs v-model="activeTab" class="w-full">
      <TabsList class="mb-6">
        <TabsTrigger v-for="tab in tabs" :key="tab.id" :value="tab.id">
          {{ $t(`features.redis.tabs.${tab.id}`) }}
        </TabsTrigger>
      </TabsList>

      <!-- Statistics Tab -->
      <TabsContent value="statistics" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <Card class="border-none bg-muted/20 shadow-none">
            <CardContent class="pt-6">
              <div class="flex items-center gap-4">
                <div class="p-3 rounded-lg bg-blue-500/10 text-blue-500">
                  <Database class="w-6 h-6" />
                </div>
                <div>
                  <p class="text-sm text-muted-foreground font-medium">{{ $t('features.redis.statistics.grid.version') }}</p>
                  <p class="text-2xl font-bold">{{ stats.version || '-' }}</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card class="border-none bg-muted/20 shadow-none">
            <CardContent class="pt-6">
              <div class="flex items-center gap-4">
                <div class="p-3 rounded-lg bg-emerald-500/10 text-emerald-500">
                  <HardDrive class="w-6 h-6" />
                </div>
                <div>
                  <p class="text-sm text-muted-foreground font-medium">{{ $t('features.redis.statistics.grid.memory') }}</p>
                  <p class="text-2xl font-bold">{{ stats.used_memory || '-' }}</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card class="border-none bg-muted/20 shadow-none">
            <CardContent class="pt-6">
              <div class="flex items-center gap-4">
                <div class="p-3 rounded-lg bg-amber-500/10 text-amber-500">
                  <Zap class="w-6 h-6" />
                </div>
                <div>
                  <p class="text-sm text-muted-foreground font-medium">{{ $t('features.redis.statistics.grid.keys') }}</p>
                  <p class="text-2xl font-bold">{{ stats.total_keys || 0 }}</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card class="border-none bg-muted/20 shadow-none">
            <CardContent class="pt-6">
              <div class="flex items-center gap-4">
                <div class="p-3 rounded-lg bg-orange-500/10 text-orange-500">
                  <Clock class="w-6 h-6" />
                </div>
                <div>
                  <p class="text-sm text-muted-foreground font-medium">{{ $t('features.redis.statistics.grid.uptime') }}</p>
                  <p class="text-2xl font-bold">{{ stats.uptime_days || '-' }}</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card class="border-none bg-muted/20 shadow-none">
            <CardContent class="pt-6">
              <div class="flex items-center gap-4">
                <div class="p-3 rounded-lg bg-purple-500/10 text-purple-500">
                  <Users class="w-6 h-6" />
                </div>
                <div>
                  <p class="text-sm text-muted-foreground font-medium">{{ $t('features.redis.statistics.grid.clients') }}</p>
                  <p class="text-2xl font-bold">{{ stats.connected_clients || 0 }}</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card class="border-none bg-muted/20 shadow-none">
            <CardContent class="pt-6">
              <div class="flex items-center gap-4">
                <div class="p-3 rounded-lg bg-indigo-500/10 text-indigo-500">
                  <Target class="w-6 h-6" />
                </div>
                <div>
                  <p class="text-sm text-muted-foreground font-medium">{{ $t('features.redis.statistics.grid.hitRate') }}</p>
                  <p class="text-2xl font-bold">{{ stats.hit_rate || '0%' }}</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card class="border-none bg-muted/20 shadow-none">
            <CardContent class="pt-6">
              <div class="flex items-center gap-4">
                <div class="p-3 rounded-lg bg-pink-500/10 text-pink-500">
                  <Activity class="w-6 h-6" />
                </div>
                <div>
                  <p class="text-sm text-muted-foreground font-medium">{{ $t('features.redis.statistics.grid.ops') }}</p>
                  <p class="text-2xl font-bold">{{ stats.operations_per_sec || 0 }}</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card class="border-none bg-muted/20 shadow-none">
            <CardContent class="pt-6">
              <div class="flex items-center gap-4">
                <div class="p-3 rounded-lg bg-slate-500/10 text-slate-500">
                  <BarChart3 class="w-6 h-6" />
                </div>
                <div>
                  <p class="text-sm text-muted-foreground font-medium">{{ $t('features.redis.statistics.grid.commands') }}</p>
                  <p class="text-2xl font-bold">{{ formatNumber(stats.total_commands) || 0 }}</p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <Card>
          <CardHeader class="px-6 py-4 border-b border-border/50 bg-muted/20">
            <CardTitle class="text-lg flex items-center gap-2">
              <Activity class="w-5 h-5 text-primary" />
              {{ $t('features.redis.statistics.hitMiss.title') }}
            </CardTitle>
          </CardHeader>
          <CardContent class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="flex justify-between items-center p-4 bg-muted/30 rounded-lg border border-border/50 shadow-sm">
                <span class="font-medium text-foreground">{{ $t('features.redis.statistics.hitMiss.hits') }}</span>
                <span class="text-xl font-bold text-emerald-600">{{ formatNumber(stats.hits) || 0 }}</span>
              </div>
              <div class="flex justify-between items-center p-4 bg-muted/30 rounded-lg border border-border/50 shadow-sm">
                <span class="font-medium text-foreground">{{ $t('features.redis.statistics.hitMiss.misses') }}</span>
                <span class="text-xl font-bold text-destructive">{{ formatNumber(stats.misses) || 0 }}</span>
              </div>
              <div class="flex justify-between items-center p-4 bg-muted/30 rounded-lg border border-border/50 shadow-sm">
                <span class="font-medium text-foreground">{{ $t('features.redis.statistics.hitMiss.hitRate') }}</span>
                <span class="text-xl font-bold text-blue-600">{{ stats.hit_rate || '0%' }}</span>
              </div>
            </div>
          </CardContent>
          <div class="px-6 py-4 border-t border-border/50 flex items-center justify-between">
            <Button 
              variant="outline"
              size="sm"
              @click="loadStats" 
              :disabled="loadingStats" 
            >
              <RefreshCw v-if="loadingStats" class="w-4 h-4 mr-2 animate-spin" />
              {{ loadingStats ? $t('features.redis.messages.loading') : $t('features.redis.statistics.refresh') }}
            </Button>
            <span class="text-xs text-muted-foreground">{{ $t('features.redis.statistics.autoRefresh') }}</span>
          </div>
        </Card>
      </TabsContent>

      <!-- Settings Tab -->
      <TabsContent value="settings" class="space-y-6">
        <!-- Global Driver Warning -->
        <Card v-if="cacheDriver !== 'redis'" class="border-amber-500/40 bg-amber-500/5">
          <CardContent class="pt-6">
            <div class="flex items-start gap-4">
              <div class="p-2 rounded-full bg-amber-500/20 text-amber-600">
                <AlertTriangle class="w-6 h-6" />
              </div>
              <div class="space-y-1">
                <h4 class="font-bold text-amber-800 dark:text-amber-200 uppercase tracking-wider text-sm">Redis Disabled</h4>
                <p class="text-sm text-amber-700 dark:text-amber-300 leading-relaxed">
                  Redis is currently disabled in system settings. To enable it, go to 
                  <router-link to="/admin/settings?tab=performance" class="underline font-semibold hover:text-amber-900 inline-flex items-center gap-1">
                    Settings <ArrowRight class="w-3 h-3" /> Performance
                  </router-link>
                  and select "Redis" as the Cache Driver.
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <form @submit.prevent="saveSettings" :inert="cacheDriver !== 'redis'">
          <Card :class="{'opacity-50 pointer-events-none': cacheDriver !== 'redis'}">
            <CardContent class="p-0">
              <div v-for="(group, index) in groupedSettings" :key="group.name" class="space-y-0">
                <!-- Group Header -->
                <div class="px-6 py-4 border-b border-border/50 bg-muted/20 flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-primary/10 text-primary">
                      <component :is="getGroupIcon(group.name)" class="w-5 h-5" />
                    </div>
                    <div>
                      <h3 class="text-base font-semibold text-foreground">{{ group.name }}</h3>
                      <p class="text-xs text-muted-foreground">{{ getGroupDescription(group.name) }}</p>
                    </div>
                  </div>
                  
                  <!-- Connection Test Button (only for Connection group) -->
                  <Button 
                    v-if="group.name === 'Connection'"
                    size="sm"
                    variant="outline"
                    type="button"
                    @click="testConnection" 
                    :disabled="testing"
                    class="h-8"
                  >
                    <Loader2 v-if="testing" class="w-4 h-4 mr-2 animate-spin" />
                    {{ testing ? $t('features.redis.settings.testing') : $t('features.redis.settings.test') }}
                  </Button>
                </div>

                <!-- Connection Status Alert (only for Connection group) -->
                <div v-if="group.name === 'Connection' && connectionStatus" class="px-6 pt-4">
                  <div :class="cn('rounded-lg p-4 text-sm border border-border/50 flex items-center gap-3', connectionStatus.type === 'success' ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-600' : 'bg-destructive/10 border-destructive/20 text-destructive')">
                    <div :class="cn('p-1 rounded-full', connectionStatus.type === 'success' ? 'bg-emerald-500/20' : 'bg-destructive/20')">
                      <zap v-if="connectionStatus.type === 'success'" class="w-4 h-4" />
                      <AlertTriangle v-else class="w-4 h-4" />
                    </div>
                    <span>{{ connectionStatus.message }}</span>
                  </div>
                </div>

                <!-- Settings Grid -->
                <div class="px-6 py-6 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                  <div v-for="setting in group.items" :key="setting.key" class="space-y-2">
                    <Label :for="setting.key" class="text-sm font-semibold text-foreground">
                      {{ formatLabel(setting.key) }}
                    </Label>
                    <p v-if="setting.description" class="text-xs text-muted-foreground">{{ setting.description }}</p>

                    <Input
                      v-if="setting.type === 'string' || setting.type === 'integer'"
                      :id="setting.key"
                      :type="setting.type === 'integer' ? 'number' : (setting.is_encrypted ? 'password' : 'text')"
                      v-model="settingsForm[setting.key]"
                    />

                    <Select
                      v-else-if="setting.type === 'boolean'"
                      :model-value="String(settingsForm[setting.key])"
                      @update:model-value="(val) => settingsForm[setting.key] = val === 'true'"
                    >
                      <SelectTrigger class="w-full">
                        <SelectValue :placeholder="$t('features.redis.settings.select')" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="true">{{ $t('features.redis.settings.enabled') }}</SelectItem>
                        <SelectItem value="false">{{ $t('features.redis.settings.disabled') }}</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>

                <!-- Divider between groups (except last) -->
                <div v-if="index < groupedSettings.length - 1" class="h-px bg-border/40"></div>
              </div>
            </CardContent>
          </Card>

          <!-- Action Buttons / Footer (Clean version) -->
          <div class="mt-6 flex justify-end items-center gap-3">
            <Button
                variant="ghost"
                type="button"
                @click="loadSettings"
                class="text-muted-foreground hover:text-foreground"
            >
                {{ $t('features.redis.settings.cancel') }}
            </Button>
            <Button 
              type="submit" 
              :disabled="saving"
              class="min-w-[140px]"
            >
              <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
              <Save v-else class="w-4 h-4 mr-2" />
              {{ saving ? $t('features.redis.settings.saving') : $t('features.redis.settings.save') }}
            </Button>
          </div>
        </form>
      </TabsContent>

      <!-- Cache Tab -->
      <TabsContent value="cache" class="space-y-6">
        <Card>
          <CardHeader class="px-6 py-4 border-b border-border/50 bg-muted/20">
            <CardTitle class="text-lg flex items-center gap-2">
              <Eraser class="w-5 h-5 text-primary" />
              {{ $t('features.redis.cache.title') }}
            </CardTitle>
            <CardDescription>Flush or warm up specific cache categories</CardDescription>
          </CardHeader>
          <CardContent class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <!-- Actions -->
              <Card class="border-2 border-primary/10 bg-primary/5 hover:border-primary/30 transition-all cursor-default group shadow-none">
                <CardContent class="pt-6 text-center space-y-4">
                   <div class="w-12 h-12 bg-primary/10 text-primary rounded-full flex items-center justify-center mx-auto group-hover:scale-110 transition-transform">
                    <Flame class="w-6 h-6" />
                  </div>
                  <div>
                    <h4 class="font-bold text-foreground">{{ $t('features.redis.cache.actions.warm.title') }}</h4>
                    <p class="text-xs text-muted-foreground px-2">{{ $t('features.redis.cache.actions.warm.desc') }}</p>
                  </div>
                  <Button 
                    @click="warmCache" 
                    :disabled="warming" 
                    class="w-full"
                  >
                    <Loader2 v-if="warming" class="w-4 h-4 mr-2 animate-spin" />
                    {{ warming ? $t('features.redis.cache.actions.warm.warming') : $t('features.redis.cache.actions.warm.button') }}
                  </Button>
                </CardContent>
              </Card>

              <Card class="border-destructive/10 bg-destructive/5 hover:border-destructive/30 transition-all cursor-default group shadow-none">
                <CardContent class="pt-6 text-center space-y-4">
                   <div class="w-12 h-12 bg-destructive/10 text-destructive rounded-full flex items-center justify-center mx-auto group-hover:scale-110 transition-transform">
                    <Trash2 class="w-6 h-6" />
                  </div>
                  <div>
                    <h4 class="font-bold text-foreground">{{ $t('features.redis.cache.actions.all.title') }}</h4>
                    <p class="text-xs text-muted-foreground px-2">{{ $t('features.redis.cache.actions.all.desc') }}</p>
                  </div>
                  <Button 
                    variant="destructive"
                    @click="flushCache('all')" 
                    :disabled="flushing" 
                    class="w-full"
                  >
                    {{ $t('features.redis.cache.actions.all.button') }}
                  </Button>
                </CardContent>
              </Card>

              <Card class="border-orange-500/10 bg-orange-500/5 hover:border-orange-500/30 transition-all cursor-default group shadow-none">
                <CardContent class="pt-6 text-center space-y-4">
                   <div class="w-12 h-12 bg-orange-500/10 text-orange-500 rounded-full flex items-center justify-center mx-auto group-hover:scale-110 transition-transform">
                    <HardDrive class="w-6 h-6" />
                  </div>
                  <div>
                    <h4 class="font-bold text-foreground">{{ $t('features.redis.cache.actions.cache.title') }}</h4>
                    <p class="text-xs text-muted-foreground px-2">{{ $t('features.redis.cache.actions.cache.desc') }}</p>
                  </div>
                  <Button 
                    variant="outline"
                    class="w-full border-orange-500/20 text-orange-600 hover:bg-orange-500 hover:text-white"
                    @click="flushCache('cache')" 
                    :disabled="flushing" 
                  >
                    {{ $t('features.redis.cache.actions.cache.button') }}
                  </Button>
                </CardContent>
              </Card>

              <Card class="border-indigo-500/10 bg-indigo-500/5 hover:border-indigo-500/30 transition-all cursor-default group shadow-none">
                <CardContent class="pt-6 text-center space-y-4">
                   <div class="w-12 h-12 bg-indigo-500/10 text-indigo-500 rounded-full flex items-center justify-center mx-auto group-hover:scale-110 transition-transform">
                    <Settings class="w-6 h-6" />
                  </div>
                  <div>
                    <h4 class="font-bold text-foreground">{{ $t('features.redis.cache.actions.config.title') }}</h4>
                    <p class="text-xs text-muted-foreground px-2">{{ $t('features.redis.cache.actions.config.desc') }}</p>
                  </div>
                  <Button 
                    variant="outline"
                    class="w-full border-indigo-500/20 text-indigo-600 hover:bg-indigo-500 hover:text-white"
                    @click="flushCache('config')" 
                    :disabled="flushing" 
                  >
                    {{ $t('features.redis.cache.actions.config.button') }}
                  </Button>
                </CardContent>
              </Card>

              <Card class="border-emerald-500/10 bg-emerald-500/5 hover:border-emerald-500/30 transition-all cursor-default group shadow-none">
                <CardContent class="pt-6 text-center space-y-4">
                   <div class="w-12 h-12 bg-emerald-500/10 text-emerald-500 rounded-full flex items-center justify-center mx-auto group-hover:scale-110 transition-transform">
                    <Route class="w-6 h-6" />
                  </div>
                  <div>
                    <h4 class="font-bold text-foreground">{{ $t('features.redis.cache.actions.route.title') }}</h4>
                    <p class="text-xs text-muted-foreground px-2">{{ $t('features.redis.cache.actions.route.desc') }}</p>
                  </div>
                  <Button 
                    variant="outline"
                    class="w-full border-emerald-500/20 text-emerald-600 hover:bg-emerald-500 hover:text-white"
                    @click="flushCache('route')" 
                    :disabled="flushing" 
                  >
                    {{ $t('features.redis.cache.actions.route.button') }}
                  </Button>
                </CardContent>
              </Card>

              <Card class="border-blue-500/10 bg-blue-500/5 hover:border-blue-500/30 transition-all cursor-default group shadow-none">
                <CardContent class="pt-6 text-center space-y-4">
                   <div class="w-12 h-12 bg-blue-500/10 text-blue-500 rounded-full flex items-center justify-center mx-auto group-hover:scale-110 transition-transform">
                    <Eye class="w-6 h-6" />
                  </div>
                  <div>
                    <h4 class="font-bold text-foreground">{{ $t('features.redis.cache.actions.view.title') }}</h4>
                    <p class="text-xs text-muted-foreground px-2">{{ $t('features.redis.cache.actions.view.desc') }}</p>
                  </div>
                  <Button 
                    variant="outline"
                    class="w-full border-blue-500/20 text-blue-600 hover:bg-blue-500 hover:text-white"
                    @click="flushCache('view')" 
                    :disabled="flushing" 
                  >
                    {{ $t('features.redis.cache.actions.view.button') }}
                  </Button>
                </CardContent>
              </Card>
            </div>
          </CardContent>
        </Card>

        <Card v-if="cacheStats">
          <CardHeader class="px-6 py-4 border-b border-border/50 bg-muted/20">
            <CardTitle class="text-lg flex items-center gap-2">
              <BarChart3 class="w-5 h-5 text-primary" />
              {{ $t('features.redis.cache.stats.title') }}
            </CardTitle>
          </CardHeader>
          <CardContent class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="flex justify-between items-center p-4 bg-muted/30 rounded-lg border border-border/50 shadow-sm">
                <span class="font-medium text-foreground">{{ $t('features.redis.cache.stats.keys') }}</span>
                <span class="font-bold">{{ cacheStats.total_keys || 0 }}</span>
              </div>
              <div class="flex justify-between items-center p-4 bg-muted/30 rounded-lg border border-border/50 shadow-sm">
                <span class="font-medium text-foreground">{{ $t('features.redis.cache.stats.size') }}</span>
                <span class="font-bold text-blue-600">{{ cacheStats.cache_size || '-' }}</span>
              </div>
              <div class="flex justify-between items-center p-4 bg-muted/30 rounded-lg border border-border/50 shadow-sm">
                <span class="font-medium text-foreground">{{ $t('features.redis.cache.stats.expired') }}</span>
                <span class="font-bold">{{ cacheStats.expired_keys || 0 }}</span>
              </div>
            </div>

            <div v-if="cacheStats.top_keys?.length" class="space-y-4">
              <h4 class="font-bold text-sm uppercase tracking-wider text-muted-foreground">{{ $t('features.redis.cache.stats.topKeys') }}</h4>
              <div class="rounded-lg border border-border/50 overflow-hidden shadow-sm">
                <table class="w-full divide-y divide-border/50">
                  <thead class="bg-muted/50">
                    <tr>
                      <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider whitespace-nowrap">{{ $t('features.redis.cache.stats.table.key') }}</th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider whitespace-nowrap">{{ $t('features.redis.cache.stats.table.size') }}</th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider whitespace-nowrap">{{ $t('features.redis.cache.stats.table.ttl') }}</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-border/50 bg-card">
                    <tr v-for="(key, index) in cacheStats.top_keys" :key="index" class="hover:bg-muted/30 transition-colors">
                      <td class="px-4 py-3 font-mono text-xs break-all">{{ key.key }}</td>
                      <td class="px-4 py-3 text-sm text-blue-600 font-medium whitespace-nowrap">{{ key.size }}</td>
                      <td class="px-4 py-3 text-sm text-muted-foreground whitespace-nowrap">{{ key.ttl }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </CardContent>
        </Card>
      </TabsContent>
    </Tabs>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import { cn } from '@/lib/utils'
import Tabs from '@/components/ui/tabs.vue'
import TabsList from '@/components/ui/tabs-list.vue'
import TabsTrigger from '@/components/ui/tabs-trigger.vue'
import TabsContent from '@/components/ui/tabs-content.vue'
import Card from '@/components/ui/card.vue'
import CardHeader from '@/components/ui/card-header.vue'
import CardTitle from '@/components/ui/card-title.vue'
import CardDescription from '@/components/ui/card-description.vue'
import CardContent from '@/components/ui/card-content.vue'
import Button from '@/components/ui/button.vue'
import Input from '@/components/ui/input.vue'
import Label from '@/components/ui/label.vue'
import Badge from '@/components/ui/badge.vue'
import Select from '@/components/ui/select.vue'
import SelectContent from '@/components/ui/select-content.vue'
import SelectItem from '@/components/ui/select-item.vue'
import SelectTrigger from '@/components/ui/select-trigger.vue'
import SelectValue from '@/components/ui/select-value.vue'
import {
  RefreshCw,
  Activity,
  Database,
  Zap,
  Clock,
  Save,
  Trash2,
  HardDrive,
  Users,
  Target,
  BarChart3,
  Flame,
  Eraser,
  Settings,
  Route,
  Eye,
  ArrowRight,
  Loader2,
  AlertTriangle
} from 'lucide-vue-next'

const { t } = useI18n()
const activeTab = ref('statistics')
const tabs = [
  { id: 'statistics', label: 'Statistics' },
  { id: 'settings', label: 'Settings' },
  { id: 'cache', label: 'Cache Management' },
]

// Settings
const settings = ref({})
const settingsForm = ref({})
const cacheDriver = ref(null) // Global cache driver status

const groupedSettings = computed(() => {
  const groups = {}
  
  Object.entries(settings.value).forEach(([groupName, items]) => {
    // Filter out only redis_enabled (which was duplicative).
    const filteredItems = items.filter(item => item.key !== 'redis_enabled')
     if (filteredItems.length === 0) return

    // Merge session and queue into one group
    if (groupName === 'session' || groupName === 'queue') {
      if (!groups['session_queue']) {
        groups['session_queue'] = {
          name: 'Session & Queue',
          items: []
        }
      }
      groups['session_queue'].items.push(...filteredItems)
    } else {
      groups[groupName] = {
        name: formatGroupName(groupName),
        items: filteredItems
      }
    }
  })
  
  return Object.values(groups)
})

const saving = ref(false)
const testing = ref(false)
const connectionStatus = ref(null)

// Statistics
const stats = ref({})
const loadingStats = ref(false)
const statsInterval = ref(null)

// Cache
const cacheStats = ref(null)
const flushing = ref(false)
const warming = ref(false)

// Methods
const loadSettings = async () => {
  try {
    const response = await api.get('/admin/cms/redis/settings')
    settings.value = response.data.data
    
    // Flatten settings for form
    Object.values(settings.value).forEach(items => {
      items.forEach(item => {
        settingsForm.value[item.key] = item.value
      })
    })
  } catch (error) {
    console.error('Failed to load Redis settings:', error)
  }
}

const saveSettings = async () => {
  saving.value = true
  try {
    const settingsArray = Object.entries(settingsForm.value).map(([key, value]) => ({
      key,
      value
    }))

    await api.put('/admin/cms/redis/settings', {
      settings: settingsArray
    })

    connectionStatus.value = {
      type: 'success',
      message: t('features.redis.messages.saveSuccess')
    }

    setTimeout(() => {
      connectionStatus.value = null
    }, 3000)
  } catch (error) {
    connectionStatus.value = {
      type: 'error',
      message: error.response?.data?.message || t('features.redis.messages.saveFailed')
    }
  } finally {
    saving.value = false
  }
}

const testConnection = async () => {
  testing.value = true
  connectionStatus.value = null

  try {
    const response = await api.get('/admin/cms/redis/test-connection')
    connectionStatus.value = {
      type: 'success',
      message: `✅ ${response.data.data.message || t('features.redis.messages.testSuccess')} (${response.data.data.response_time})`
    }
  } catch (error) {
    connectionStatus.value = {
      type: 'error',
      message: `❌ ${error.response?.data?.message || t('features.redis.messages.testFailed')}`
    }
  } finally {
    testing.value = false
  }
}

const loadStats = async () => {
  loadingStats.value = true
  try {
    const response = await api.get('/admin/cms/redis/info')
    stats.value = response.data.data
  } catch (error) {
    console.error('Failed to load Redis stats:', error)
  } finally {
    loadingStats.value = false
  }
}

const loadCacheStats = async () => {
  try {
    const response = await api.get('/admin/cms/redis/cache-stats')
    cacheStats.value = response.data.data
  } catch (error) {
    console.error('Failed to load cache stats:', error)
  }
}

const getCacheStatus = async () => {
    try {
        const response = await api.get('/admin/cms/system/cache-status')
        const data = response.data.data
        cacheDriver.value = data.driver
    } catch (error) {
        console.error('Failed to get global cache status:', error)
    }
}

const flushCache = async (type) => {
  if (!confirm(t('features.redis.messages.flushConfirm', { type }))) {
    return
  }

  flushing.value = true
  try {
    await api.post('/admin/cms/redis/flush-cache', { type })
    alert(t('features.redis.messages.flushSuccess', { type }))
    loadCacheStats()
  } catch (error) {
    alert(error.response?.data?.message || t('features.redis.messages.flushFailed'))
  } finally {
    flushing.value = false
  }
}

const warmCache = async () => {
  if (!confirm(t('features.redis.messages.warmConfirm'))) {
    return
  }

  warming.value = true
  try {
    await api.post('/admin/cms/redis/warm-cache')
    alert(t('features.redis.messages.warmSuccess'))
    loadCacheStats()
    loadStats()
  } catch (error) {
    alert(error.response?.data?.message || t('features.redis.messages.warmFailed'))
  } finally {
    warming.value = false
  }
}

// Helpers
const formatLabel = (key) => {
  return key.split('_').map(word => 
    word.charAt(0).toUpperCase() + word.slice(1)
  ).join(' ')
}

const formatGroupName = (group) => {
  return group.charAt(0).toUpperCase() + group.slice(1)
}

const getGroupIcon = (groupName) => {
  const icons = {
    'Connection': Database,
    'Cache': Zap,
    'Session & Queue': Clock
  }
  return icons[groupName] || Database
}

const getGroupDescription = (groupName) => {
  const descriptions = {
    'Connection': 'Configure Redis server connection details',
    'Cache': 'Manage application cache settings',
    'Session & Queue': 'Configure session and queue driver settings'
  }
  return descriptions[groupName] || ''
}

const formatNumber = (num) => {
  if (!num) return 0
  return new Intl.NumberFormat().format(num)
}

// Lifecycle
onMounted(() => {
  loadSettings()
  loadStats()
  loadCacheStats()
  getCacheStatus()

  // Auto-refresh stats every 30 seconds
  statsInterval.value = setInterval(() => {
    if (activeTab.value === 'statistics') {
      loadStats()
    }
  }, 30000)
})

onUnmounted(() => {
  if (statsInterval.value) {
    clearInterval(statsInterval.value)
  }
})
</script>

<style scoped>
/* Custom transitions for cards */
.group:hover .group-hover\:scale-110 {
  transform: scale(1.1);
}
</style>
