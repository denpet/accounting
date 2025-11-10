<template>
  <q-table
    class="ellipsis"
    :columns="columns"
    :rows="poolReadingStore.index ?? []"
    row-key="id"
    :rows-per-page-options="[25, 50, 0]"
    dense
    @row-click="onShow"
  >
    <template #top-left>
      <div class="q-table__title">Pool Readings</div>
      <q-btn
        @click.stop="onCreate"
        icon="mdi-plus"
        label="Pool Reading"
        color="secondary"
        size="sm"
        rounded
        dense
        align="left"
        style="width: 6rem"
        class="q-ml-md"
      />
    </template>
  </q-table>
</template>

<script setup lang="ts">
import {
  PoolReadingObject,
  useMaintenancePoolReadingStore,
} from 'stores/maintenance/pool-reading'
import { QTableColumn } from 'quasar'
import { format } from 'boot/format'

const poolReadingStore = useMaintenancePoolReadingStore()
poolReadingStore.fetchIndex()

const onCreate = () => {
  poolReadingStore.create(<PoolReadingObject>{
    date: new Date().toLocaleDateString('sv'),
  })
}

const onShow = (event: Event, row: { id: number }) => {
  poolReadingStore.show(row.id)
}

const columns: QTableColumn[] = [
  {
    name: 'id',
    label: 'Id#',
    field: 'id',
    align: 'left',
    style: 'width: 5rem',
    sortable: true,
  },
  {
    name: 'date',
    label: 'Date',
    field: 'date',
    align: 'left',
    sortable: true,
  },
  {
    name: 'ph',
    label: 'pH',
    field: 'ph',
    align: 'right',
    sortable: true,
  },
  {
    name: 'free_chlorine',
    label: 'Free Chlorine',
    field: 'free_chlorine',
    align: 'right',
    sortable: true,
  },
  {
    name: 'total_chlorine',
    label: 'Total Chlorine',
    field: 'total_chlorine',
    align: 'right',
    sortable: true,
  },
  {
    name: 'cyanuric_acid',
    label: 'Cyanuric Acid',
    field: 'cyanuric_acid',
    align: 'right',
    sortable: true,
  },
  {
    name: 'alkalinity',
    label: 'Alkalinity',
    field: 'alkalinity',
    align: 'right',
    sortable: true,
  },
  {
    name: 'hardness',
    label: 'Hardness',
    field: 'hardness',
    align: 'right',
    sortable: true,
  },
  {
    name: 'created_at',
    label: 'Created',
    field: 'created_at',
    align: 'right',
    sortable: true,
    format: (val: string) => format.dateTime(val),
  },
  {
    name: 'updated_at',
    label: 'Changed',
    field: 'updated_at',
    align: 'right',
    sortable: true,
    format: (val: string) => format.dateTime(val),
  },
]
</script>
