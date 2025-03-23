<template>
  <q-table
    :rows="reportStore.stockDiaryReport"
    :columns="columns"
    :rows-per-page-options="[0]"
    dense
  >
    <template #body="body">
      <q-tr>
        <q-td v-for="col in body.cols" :key="col.name">
          <h5>{{ col.value }}</h5>
        </q-td>
      </q-tr>
      <q-tr>
        <q-table
          :rows="body.row.days"
          :columns="detailColumns"
          :rows-per-page-options="[0]"
        />
      </q-tr>
    </template>
  </q-table>
</template>

<script setup lang="ts">
import { QTableColumn } from 'quasar'
import { format } from 'boot/format'
import { useUnicentaReportStore } from 'stores/unicenta/report'

const reportStore = useUnicentaReportStore()

const columns: QTableColumn[] = [
  {
    name: 'name',
    label: 'Name',
    field: 'name',
    align: 'left',
  },
]

const detailColumns: QTableColumn[] = [
  {
    name: 'date',
    label: 'Date',
    field: 'date',
    align: 'left',
  },
  {
    name: 'purchase',
    label: 'Purchase',
    field: 'purchase',
    align: 'right',
    format: (val) => format.number(val, 0, true),
  },
  {
    name: 'sale',
    label: 'Sale',
    field: 'sale',
    align: 'right',
    format: (val) => format.number(val, 0, true),
  },
  {
    name: 'refund',
    label: 'Refund',
    field: 'refund',
    align: 'right',
    format: (val) => format.number(val, 0, true),
  },
  {
    name: 'break',
    label: 'Break',
    field: 'break',
    align: 'right',
    format: (val) => format.number(val, 0, true),
  },
  {
    name: 'movement',
    label: 'Movement',
    field: 'movement',
    align: 'right',
    format: (val) => format.number(val, 0, true),
  },
  {
    name: 'adjustment',
    label: 'Adjustment',
    field: 'adjustment',
    align: 'right',
    format: (val) => format.number(val, 0, true),
  },
  {
    name: 'other',
    label: 'Other',
    field: 'other',
    align: 'right',
    format: (val) => format.number(val, 0, true),
  },
  {
    name: 'stock',
    label: 'Stock',
    field: 'stock',
    align: 'right',
    format: (val) => format.number(val, 0, true),
  },
]
</script>
