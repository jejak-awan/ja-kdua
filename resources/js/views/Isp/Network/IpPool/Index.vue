<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import { Card, CardContent, CardDescription, CardHeader, CardTitle, Button, Badge, Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui'
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js'
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js'
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js'
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js'
import Database from 'lucide-vue-next/dist/esm/icons/database.js'
import { useToast } from '@/composables/useToast'

interface IpPool {
  id: number
  name: string
  network: string
  gateway: string | null
  status: 'active' | 'inactive'
  addresses_count: number
  available_count: number
  assigned_count: number
  router?: { name: string } | null
}

const { t } = useI18n()
const router = useRouter()
const toast = useToast()

const pools = ref<IpPool[]>([])
const loading = ref(true)

const fetchPools = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/admin/janet/isp/ip-pools')
    pools.value = data.data
  } catch {
    toast.error.default(t('isp.network.ip_pool.messages.fetch_error'))
  } finally {
    loading.value = false
  }
}

const getUsagePercent = (pool: IpPool) => {
  if (!pool.addresses_count) return 0
  return Math.round((pool.assigned_count / pool.addresses_count) * 100)
}

const getUsageColor = (percent: number) => {
  if (percent >= 90) return 'bg-red-500'
  if (percent >= 70) return 'bg-yellow-500'
  return 'bg-green-500'
}

const deletePool = async (pool: IpPool) => {
  if (!confirm(t('isp.network.ip_pool.confirm_delete', { name: pool.name }))) return

  try {
    await api.delete(`/admin/janet/isp/ip-pools/${pool.id}`)
    toast.success.default(t('isp.network.ip_pool.messages.delete_success'))
    fetchPools()
  } catch (error: unknown) {
    const message = (error as { response?: { data?: { message?: string } } })?.response?.data?.message || t('isp.network.ip_pool.messages.delete_error')
    toast.error.default(message)
  }
}

onMounted(fetchPools)
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">{{ t('isp.network.ip_pool.title') }}</h1>
        <p class="text-muted-foreground">{{ t('isp.network.ip_pool.subtitle') }}</p>
      </div>
      <div class="flex gap-2">
        <Button variant="outline" size="sm" class="rounded-xl" @click="fetchPools" :aria-label="t('isp.network.ip_pool.refresh')">
          <RefreshCw class="mr-2 h-4 w-4" />
          {{ t('isp.network.ip_pool.refresh') }}
        </Button>
        <Button class="rounded-xl" @click="router.push('/admin/isp/network/ip-pools/create')">
          <Plus class="mr-2 h-4 w-4" />
          {{ t('isp.network.ip_pool.add') }}
        </Button>
      </div>
    </div>

    <Card class="border border-border/40 shadow-sm rounded-xl overflow-hidden">
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <Database class="h-5 w-5" />
          {{ t('isp.network.ip_pool.list_title') }}
        </CardTitle>
        <CardDescription>{{ t('isp.network.ip_pool.list_desc') }}</CardDescription>
      </CardHeader>
      <CardContent>
        <div v-if="loading" class="flex justify-center py-8">
          <RefreshCw class="h-8 w-8 animate-spin text-muted-foreground" />
        </div>

        <Table v-else>
          <TableHeader>
            <TableRow>
              <TableHead>{{ t('isp.network.ip_pool.table.name') }}</TableHead>
              <TableHead>{{ t('isp.network.ip_pool.table.network') }}</TableHead>
              <TableHead>{{ t('isp.network.ip_pool.table.gateway') }}</TableHead>
              <TableHead>{{ t('isp.network.ip_pool.table.router') }}</TableHead>
              <TableHead>{{ t('isp.network.ip_pool.table.status') }}</TableHead>
              <TableHead>{{ t('isp.network.ip_pool.table.usage') }}</TableHead>
              <TableHead class="text-right">{{ t('isp.network.ip_pool.table.actions') }}</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="pool in pools" :key="pool.id">
              <TableCell class="font-medium">{{ pool.name }}</TableCell>
              <TableCell><code class="text-sm">{{ pool.network }}</code></TableCell>
              <TableCell>{{ pool.gateway || '-' }}</TableCell>
              <TableCell>{{ pool.router?.name || '-' }}</TableCell>
              <TableCell>
                <Badge :variant="pool.status === 'active' ? 'default' : 'secondary'" class="rounded-xl">
                  {{ pool.status === 'active' ? t('isp.network.ip_pool.status.active') : t('isp.network.ip_pool.status.inactive') }}
                </Badge>
              </TableCell>
              <TableCell>
                <div class="flex items-center gap-2">
                  <div class="w-24 h-2 bg-muted rounded-full overflow-hidden">
                    <div 
                      :class="['h-full', getUsageColor(getUsagePercent(pool))]" 
                      :style="{ width: `${getUsagePercent(pool)}%` }"
                    />
                  </div>
                  <span class="text-sm text-muted-foreground">
                    {{ pool.assigned_count }}/{{ pool.addresses_count }}
                  </span>
                </div>
              </TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-1">
                  <Button variant="ghost" size="icon" @click="router.push(`/admin/isp/network/ip-pools/${pool.id}`)" :aria-label="t('common.actions.view')">
                    <Eye class="h-4 w-4" />
                  </Button>
                  <Button variant="ghost" size="icon" @click="deletePool(pool)" :aria-label="t('common.actions.delete')">
                    <Trash2 class="h-4 w-4 text-destructive" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-if="!pools.length && !loading">
              <TableCell colspan="7" class="text-center py-8 text-muted-foreground">
                {{ t('isp.network.ip_pool.empty') }}
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </CardContent>
    </Card>
  </div>
</template>
