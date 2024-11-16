<template>
  <div class="col-3">
    <q-table :rows="reportStore.result.income" :columns="columns" hide-bottom />
    <q-table :rows="reportStore.result.cost" :columns="columns" hide-bottom />
    <q-table
      :rows="reportStore.result.unicenta"
      :columns="columns"
      hide-bottom
    />
  </div>
</template>

<script setup lang="ts">
import { QTableColumn } from 'quasar'
import { format } from 'boot/format'
import { useAccountingReportStore } from 'stores/accounting/report'

const reportStore = useAccountingReportStore()

const columns: QTableColumn[] = [
  {
    name: 'name',
    label: 'Account',
    field: 'name',
    align: 'left',
    sortable: true,
  },
  {
    name: 'amount',
    label: 'Amount',
    field: 'amount',
    align: 'right',
    sortable: true,
    format: (val) => format.number(val),
  },
]
</script>
