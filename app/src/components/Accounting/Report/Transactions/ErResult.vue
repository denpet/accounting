<template>
  <q-table
    :rows="reportStore.transactions"
    :columns="columns"
    :rows-per-page-options="[0]"
    dense
  />
</template>

<script setup lang="ts">
import { QTableColumn } from 'quasar'
import { format } from 'boot/format'
import { useAccountingReportStore } from 'stores/accounting/report'

const reportStore = useAccountingReportStore()

const columns: QTableColumn[] = [
  {
    name: 'id',
    label: 'Id#',
    field: 'id',
    align: 'left',
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
    name: 'note',
    label: 'Note',
    field: 'note',
    align: 'left',
    sortable: true,
  },
  {
    name: 'from_account_name',
    label: 'From',
    field: 'from_account_name',
    align: 'left',
    sortable: true,
  },
  {
    name: 'to_account_name',
    label: 'To',
    field: 'to_account_name',
    align: 'left',
    sortable: true,
  },
  {
    name: 'amount',
    label: 'Amount',
    field: 'amount',
    align: 'right',
    sortable: true,
    format: (val) => format.number(val, 0, true),
  },
  {
    name: 'official_receipt',
    label: 'Official Receipt',
    field: 'official_receipt',
    align: 'left',
    sortable: true,
  },
]
</script>
