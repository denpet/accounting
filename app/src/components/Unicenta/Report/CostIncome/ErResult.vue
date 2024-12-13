<template>
  <q-table
    :rows="reportStore.costIncomeReport"
    :columns="columns"
    :rows-per-page-options="[0]"
    dense
  />
</template>

<script setup lang="ts">
import { QTableColumn } from 'quasar'
import { format } from 'boot/format'
import { useUnicentaReportStore } from 'stores/unicenta/report'

const reportStore = useUnicentaReportStore()

const columns: QTableColumn[] = [
  {
    name: 'category_name',
    label: 'Category',
    field: 'category_name',
    align: 'left',
  },
  {
    name: 'name',
    label: 'Product',
    field: 'name',
    align: 'left',
  },
  {
    name: 'units',
    label: 'Units',
    field: 'units',
    align: 'right',
    format: (val) => format.number(val, 0, true),
  },
  {
    name: 'cost',
    label: 'Cost',
    field: 'cost',
    align: 'right',
    format: (val) => format.number(val, 0, true),
  },
  {
    name: 'income',
    label: 'Income',
    field: 'income',
    align: 'right',
    format: (val) => format.number(val, 0, true),
  },
]
</script>
