<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import { Card, CardContent, CardDescription, CardHeader, CardTitle, Button, Badge, Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui'
import ArrowLeft from 'lucide-vue-next/dist/esm/icons/arrow-left.js'
import Edit from 'lucide-vue-next/dist/esm/icons/pencil.js'
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js'
import Database from 'lucide-vue-next/dist/esm/icons/database.js'
import Network from 'lucide-vue-next/dist/esm/icons/network.js'
import Server from 'lucide-vue-next/dist/esm/icons/server.js'
import User from 'lucide-vue-next/dist/esm/icons/user.js'
import { useToast } from '@/composables/useToast'

interface IpPoolAddress {
  id: number
  ip_address: string
  status: 'available' | 'assigned' | 'reserved'
  assigned_at: string | null
  notes: string | null
  customer?: { id: number; name: string } | null
}

interface IpPool {
  id: number
  name: string
  network: string
  gateway: string | null
  dns_primary: string | null
  dns_secondary: string | null
  vlan_id: number | null
  status: 'active' | 'inactive'
  description: string | null
  router?: { id: number; name: string } | null
  addresses: IpPoolAddress[]
}

interface Stats {
  total: number
  available: number
  assigned: number
  reserved: number
  usage_percent: number
}

const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const toast = useToast()

const pool = ref<IpPool | null>(null)
const stats = ref<Stats | null>(null)
const loading = ref(true)
const filter = ref<string>('')

const filteredAddresses = computed(() => {
  if (!pool.value) return []
  if (!filter.value) return pool.value.addresses
  return pool.value.addresses.filter(a => a.status === filter.value)
})

const fetchPool = async () => {
  loading.value = true
  try {
    const { data } = await api.get(`/admin/janet/isp/ip-pools/${route.params.id}`)
    pool.value = data.data.pool
    stats.value = data.data.stats
  } catch {
    toast.error.default(t('isp.network.ip_pool.messages.pool_error'))
    router.push('/admin/isp/network/ip-pools')
  } finally {
    loading.value = false
  }
}

const regenerateAddresses = async () => {
  if (!confirm(t('isp.network.ip_pool.confirm_regenerate'))) return
  try {
    const { data } = await api.post(`/admin/janet/isp/ip-pools/${route.params.id}/regenerate`)
    toast.success.default(t('isp.network.ip_pool.messages.regenerate_success', { count: data.data.generated }))
    fetchPool()
  } catch {
    toast.error.default(t('isp.network.ip_pool.messages.regenerate_error'))
  }
}

const getStatusBadge = (status: string): { variant: 'outline' | 'default' | 'secondary', label: string } => {
    switch (status) {
        case 'available': return { variant: 'outline', label: t('isp.network.ip_pool.status.available') }
        case 'assigned': return { variant: 'default', label: t('isp.network.ip_pool.status.assigned') }
        case 'reserved': return { variant: 'secondary', label: t('isp.network.ip_pool.status.reserved') }
        default: return { variant: 'outline', label: status }
    }
}

onMounted(fetchPool)
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <Button variant="ghost" size="icon" class="rounded-xl" @click="router.push('/admin/isp/network/ip-pools')" :aria-label="t('common.actions.back')">
          <ArrowLeft class="h-5 w-5" />
        </Button>
        <div>
          <h1 class="text-2xl font-bold">{{ pool?.name || 'Loading...' }}</h1>
          <p class="text-muted-foreground">
            <code v-if="pool">{{ pool.network }}</code>
          </p>
        </div>
      </div>
      <div class="flex gap-2">
        <Button variant="outline" size="sm" class="rounded-xl" @click="regenerateAddresses" :aria-label="t('isp.network.ip_pool.regenerate')">
          <RefreshCw class="mr-2 h-4 w-4" />
          {{ t('isp.network.ip_pool.regenerate') }}
        </Button>
        <Button size="sm" class="rounded-xl" @click="router.push(`/admin/isp/network/ip-pools/${route.params.id}/edit`)">
          <Edit class="mr-2 h-4 w-4" />
          {{ t('common.actions.edit') }}
        </Button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div v-if="stats" class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <Card class="cursor-pointer hover:bg-accent/50 border border-border/40 shadow-sm rounded-xl transition-all" @click="filter = ''">
        <CardContent class="pt-4">
          <div class="flex items-center gap-3">
            <Database class="h-8 w-8 text-muted-foreground" />
            <div>
              <p class="text-2xl font-bold">{{ stats.total }}</p>
              <p class="text-sm text-muted-foreground">{{ t('isp.network.ip_pool.stats.total') }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
      <Card class="cursor-pointer hover:bg-accent/50 border border-border/40 shadow-sm rounded-xl transition-all" @click="filter = 'available'">
        <CardContent class="pt-4">
          <div class="flex items-center gap-3">
            <Network class="h-8 w-8 text-green-500" />
            <div>
              <p class="text-2xl font-bold text-green-600">{{ stats.available }}</p>
              <p class="text-sm text-muted-foreground">{{ t('isp.network.ip_pool.stats.available') }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
      <Card class="cursor-pointer hover:bg-accent/50" @click="filter = 'assigned'">
        <CardContent class="pt-4">
          <div class="flex items-center gap-3">
            <User class="h-8 w-8 text-blue-500" />
            <div>
              <p class="text-2xl font-bold text-blue-600">{{ stats.assigned }}</p>
              <p class="text-sm text-muted-foreground">{{ t('isp.network.ip_pool.stats.assigned') }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
      <Card class="cursor-pointer hover:bg-accent/50" @click="filter = 'reserved'">
        <CardContent class="pt-4">
          <div class="flex items-center gap-3">
            <Server class="h-8 w-8 text-yellow-500" />
            <div>
              <p class="text-2xl font-bold text-yellow-600">{{ stats.reserved }}</p>
              <p class="text-sm text-muted-foreground">{{ t('isp.network.ip_pool.stats.reserved') }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Pool Info -->
    <Card v-if="pool" class="border border-border/40 shadow-sm rounded-xl">
      <CardHeader>
        <CardTitle>{{ t('isp.network.ip_pool.info') }}</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
          <div>
            <p class="text-muted-foreground">{{ t('isp.network.ip_pool.fields.gateway') }}</p>
            <p class="font-medium">{{ pool.gateway || '-' }}</p>
          </div>
          <div>
            <p class="text-muted-foreground">DNS</p>
            <p class="font-medium">{{ pool.dns_primary || '-' }}</p>
          </div>
          <div>
            <p class="text-muted-foreground">VLAN</p>
            <p class="font-medium">{{ pool.vlan_id || '-' }}</p>
          </div>
          <div>
            <p class="text-muted-foreground">{{ t('isp.network.ip_pool.fields.router') }}</p>
            <p class="font-medium">{{ pool.router?.name || '-' }}</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Addresses Table -->
    <Card class="border border-border/40 shadow-sm rounded-xl overflow-hidden">
      <CardHeader>
        <div class="flex items-center justify-between">
          <div>
            <CardTitle>{{ t('isp.network.ip_pool.address.title') }}</CardTitle>
            <CardDescription>
              {{ filter ? `${t('isp.network.ip_pool.address.filter')}: ${filter}` : t('isp.network.ip_pool.address.all') }} 
              <Button v-if="filter" variant="link" size="sm" class="p-0 h-auto" @click="filter = ''">
                ({{ t('isp.network.ip_pool.address.reset') }})
              </Button>
            </CardDescription>
          </div>
        </div>
      </CardHeader>
      <CardContent>
        <div v-if="loading" class="flex justify-center py-8">
          <RefreshCw class="h-8 w-8 animate-spin text-muted-foreground" />
        </div>

        <Table v-else>
          <TableHeader>
            <TableRow>
              <TableHead>{{ t('isp.network.ip_pool.address.ip') }}</TableHead>
              <TableHead>{{ t('isp.network.ip_pool.address.status') }}</TableHead>
              <TableHead>{{ t('isp.network.ip_pool.address.customer') }}</TableHead>
              <TableHead>{{ t('isp.network.ip_pool.address.assigned_at') }}</TableHead>
              <TableHead>{{ t('isp.network.ip_pool.address.notes') }}</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="addr in filteredAddresses" :key="addr.id">
              <TableCell class="font-mono">{{ addr.ip_address }}</TableCell>
              <TableCell>
                <Badge :variant="getStatusBadge(addr.status).variant" class="rounded-xl">
                  {{ getStatusBadge(addr.status).label }}
                </Badge>
              </TableCell>
              <TableCell>
                <a 
                   v-if="addr.customer" 
                   :href="`/admin/isp/subscription/customers/${addr.customer.id}`" 
                   class="text-primary hover:underline"
                >
                  {{ addr.customer.name }}
                </a>
                <span v-else class="text-muted-foreground">-</span>
              </TableCell>
              <TableCell>
                {{ addr.assigned_at ? new Date(addr.assigned_at).toLocaleDateString('id-ID') : '-' }}
              </TableCell>
              <TableCell class="max-w-[200px] truncate">{{ addr.notes || '-' }}</TableCell>
            </TableRow>
            <TableRow v-if="!filteredAddresses.length">
              <TableCell colspan="5" class="text-center py-8 text-muted-foreground">
                {{ t('isp.network.ip_pool.empty_filter') }}
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </CardContent>
    </Card>
  </div>
</template>
