<template>
  <q-table
    :rows="reportStore.closedCash"
    :columns="columns"
    :rows-per-page-options="[0]"
    dense
  >
    <template #body="body">
      <template v-if="reportStore.closedCashFilter.details === 'yes'">
        <q-tr>
          <q-td colspan="8" style="border-top: solid 2px; font-weight: bold">
            {{ body.row.date }}
          </q-td>
        </q-tr>
        <q-tr
          v-for="transaction in body.row.transactions"
          :key="transaction.id"
        >
          <q-td />
          <q-td align="left">{{ transaction.id }}</q-td>
          <q-td align="left">{{ transaction.note }}</q-td>
          <q-td align="left">{{ transaction.from_account_name }}</q-td>
          <q-td align="left">{{ transaction.to_account_name }}</q-td>
          <q-td align="right">{{ format.number(transaction.amount) }}</q-td>
          <q-td align="right">{{ format.number(transaction.balance) }}</q-td>
          <q-td />
        </q-tr>
        <q-tr>
          <q-td
            v-for="col in body.cols"
            :key="col.name"
            :align="col.align"
            style="
              border-top: double 1px;
              font-weight: bold;
              height: 50px;
              vertical-align: top;
            "
          >
            {{ col.value }}
          </q-td>
        </q-tr>
      </template>
      <template v-else>
        <q-tr
          :props="body"
          :class="body.expand ? '' : 'cursor-pointer'"
          @click="body.expand = true"
        >
          <q-td v-for="col in body.cols" :key="col.name" :align="col.align">
            {{ col.value }}
          </q-td>
        </q-tr>
      </template>
    </template>
  </q-table>
</template>

<script setup lang="ts">
import { QTableColumn } from 'quasar'
import { format } from 'boot/format'
import { useAccountingReportStore } from 'stores/accounting/report'

const reportStore = useAccountingReportStore()

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
