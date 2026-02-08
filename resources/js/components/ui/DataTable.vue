<script setup lang="ts" generic="TData, TValue">
import {
  FlexRender,
  type Table as TanStackTable,
} from '@tanstack/vue-table'

import Table from './Table.vue'
import TableBody from './TableBody.vue'
import TableCell from './TableCell.vue'
import TableHead from './TableHead.vue'
import TableHeader from './TableHeader.vue'
import TableRow from './TableRow.vue'
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js'

interface DataTableProps {
  table: TanStackTable<TData>
  loading?: boolean
  emptyMessage?: string
}

defineProps<DataTableProps>()
</script>

<template>
  <div class="relative overflow-hidden rounded-xl border border-border/50 bg-card">
    <!-- Loading Overlay -->
    <div
      v-if="loading"
      class="absolute inset-0 z-50 flex items-center justify-center bg-background/50 backdrop-blur-[1px]"
    >
      <Loader2 class="h-8 w-8 animate-spin text-primary" />
    </div>

    <div class="overflow-x-auto">
      <Table>
        <TableHeader class="bg-muted/50">
          <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
            <TableHead
              v-for="header in headerGroup.headers"
              :key="header.id"
              class="h-11 px-4 py-3 text-xs font-bold uppercase tracking-wider text-muted-foreground"
              :style="{ width: header.getSize() !== 150 ? `${header.getSize()}px` : undefined }"
            >
              <FlexRender
                v-if="!header.isPlaceholder"
                :render="header.column.columnDef.header"
                :props="header.getContext()"
              />
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="table.getRowModel().rows?.length">
            <TableRow
              v-for="row in table.getRowModel().rows"
              :key="row.id"
              :data-state="row.getIsSelected() ? 'selected' : undefined"
              class="group hover:bg-muted/30 transition-colors border-border/40"
              @click="table.options.meta?.onRowClick?.(row.original)"
            >
              <TableCell
                v-for="cell in row.getVisibleCells()"
                :key="cell.id"
                class="px-4 py-3"
              >
                <FlexRender
                  :render="cell.column.columnDef.cell"
                  :props="cell.getContext()"
                />
              </TableCell>
            </TableRow>
          </template>
          <TableRow v-else>
            <TableCell
              :colspan="table.getAllColumns().length"
              class="h-24 text-center text-muted-foreground"
            >
              {{ emptyMessage || 'No results.' }}
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>
  </div>
</template>

<style scoped>
:deep(tr[data-state='selected']) {
  /* Using standard CSS instead of @apply to avoid linter confusion in some environments */
  background-color: hsl(var(--primary) / 0.05);
}
</style>
