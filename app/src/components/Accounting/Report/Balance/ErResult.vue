<template>
  <div class="col-3">
    <q-table
      :rows="reportStore.balance.assets"
      :columns="columns"
      hide-bottom
    />
  </div>
  <div class="col-3 q-ml-md">
    <q-table
      :rows="reportStore.balance.liabilities"
      :columns="columns"
      hide-bottom
    />
    <q-table
      :rows="reportStore.balance.equity"
      :columns="columns"
      hide-header
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
    name: 'balance',
    label: 'Amount',
    field: 'balance',
    align: 'right',
    sortable: true,
    format: (val) => format.number(val),
  },
]
</script>
