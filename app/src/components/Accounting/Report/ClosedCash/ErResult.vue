<template>
  <q-table
    :rows="reportStore.closedCash"
    :columns="columns"
    :rows-per-page-options="[0]"
    dense
  >
    <template v-slot:bottom-row>
      <q-tr />
      <q-tr class="text-bold">
        <q-td>Total</q-td>
        <q-td colspan="3" />
        <q-td align="right">{{
          format.number(totalCashDiscrepancy, 0, true)
        }}</q-td>
        <q-td colspan="3" />
        <q-td align="right">{{
          format.number(totalEmergencyDiscrepancy, 0, true)
        }}</q-td>
      </q-tr>
    </template>
  </q-table>
</template>

<script setup lang="ts">
import { QTableColumn } from 'quasar'
import { format } from 'boot/format'
import { useAccountingReportStore } from 'stores/accounting/report'
import { computed } from 'vue'

const reportStore = useAccountingReportStore()

const totalCashDiscrepancy = computed(() => {
  if (reportStore.closedCash === undefined) return 0
  let sum = 0
  for (const row of reportStore.closedCash) {
    sum += row.cashDiscrepancy
  }
  return sum
})

const totalEmergencyDiscrepancy = computed(() => {
  if (reportStore.closedCash === undefined) return 0
  let sum = 0
  for (const row of reportStore.closedCash) {
    sum += row.emergencyDiscrepancy
  }
  return sum
})

const columns: QTableColumn[] = [
  {
    name: 'date',
    label: 'Date',
    field: 'date',
    align: 'left',
  },
  {
    name: 'cashChange',
    label: 'Cash Change',
    field: 'cashChange',
    align: 'right',
    format: (val) => format.number(val, 0, true),
  },
  {
    name: 'cashExpected',
    label: 'Expected',
    field: 'cashExpected',
    align: 'right',
    format: (val) => format.number(val, 0, true),
  },
  {
    name: 'cash',
    label: 'Reported',
    field: 'cash',
    align: 'right',
    format: (val) => format.number(val, 0, true),
  },
  {
    name: 'cashDiscrepancy',
    label: 'Discrepancy',
    field: 'cashDiscrepancy',
    align: 'right',
    format: (val) => format.number(val, 0, true),
  },
  {
    name: 'emergencyChange',
    label: 'Emergency Change',
    field: 'emergencyChange',
    align: 'right',
    format: (val) => format.number(val, 0, true),
  },
  {
    name: 'emergencyExpected',
    label: 'Expected',
    field: 'emergencyExpected',
    align: 'right',
    format: (val) => format.number(val, 0, true),
  },
  {
    name: 'emergency',
    label: 'Reported',
    field: 'emergency',
    align: 'right',
    format: (val) => format.number(val, 0, true),
  },
  {
    name: 'emergencyDiscrepancy',
    label: 'Discrepancy',
    field: 'emergencyDiscrepancy',
    align: 'right',
    format: (val) => format.number(val, 0, true),
  },
]
</script>
