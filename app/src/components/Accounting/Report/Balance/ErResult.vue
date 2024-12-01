<template>
  <div class="col-3">
    <q-table
      :rows="reportStore.balance.assets"
      :columns="columns"
      hide-bottom
      :rows-per-page-options="[0]"
      dense
    >
      <template #bottom-row>
        <q-td class="text-bold" style="border-top: solid 1px"> Total </q-td>
        <q-td class="text-bold" align="right" style="border-top: solid 1px">
          {{ totalAssets }}
        </q-td>
      </template>
    </q-table>
  </div>
  <div class="col-3 q-ml-md">
    <q-table
      :rows="reportStore.balance.liabilities"
      :columns="columns"
      hide-bottom
      :rows-per-page-options="[0]"
      dense
    />
    <q-table
      :rows="reportStore.balance.equity"
      :columns="columns"
      hide-header
      hide-bottom
      :rows-per-page-options="[0]"
      dense
    >
      <template #bottom-row>
        <q-td class="text-bold" style="border-top: solid 1px"> Total </q-td>
        <q-td class="text-bold" align="right" style="border-top: solid 1px">
          {{ totalLiabilities }}
        </q-td>
      </template>
    </q-table>
  </div>
</template>

<script setup lang="ts">
import { QTableColumn } from 'quasar'
import { format } from 'boot/format'
import { useAccountingReportStore } from 'stores/accounting/report'
import { computed } from 'vue'

const reportStore = useAccountingReportStore()

const totalAssets = computed(() => {
  return format.number(
    reportStore.balance.assets.reduce(
      (accumulator: number, currentValue: { balance: number }) => {
        return accumulator + currentValue.balance
      },
      0,
    ),
  )
})

const totalLiabilities = computed(() => {
  return format.number(
    reportStore.balance.liabilities.reduce(
      (accumulator: number, currentValue: { balance: number }) => {
        return accumulator + currentValue.balance
      },
      0,
    ) +
      reportStore.balance.equity.reduce(
        (accumulator: number, currentValue: { balance: number }) => {
          return accumulator + currentValue.balance
        },
        0,
      ),
  )
})

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
