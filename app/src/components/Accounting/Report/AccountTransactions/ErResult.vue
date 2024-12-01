<template>
  <q-table
    :rows="reportStore.accountTransactions"
    :columns="columns"
    :rows-per-page-options="[0]"
    dense
  >
    <template #body="body">
      <template v-if="reportStore.accountTransactionsFilter.details === 'yes'">
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
    name: 'id',
    label: 'Id#',
    field: 'id',
    align: 'left',
  },
  {
    name: 'note',
    label: 'Note',
    field: 'note',
    align: 'left',
  },
  {
    name: 'from_account_name',
    label: 'From',
    field: 'from_account_name',
    align: 'left',
  },
  {
    name: 'to_account_name',
    label: 'To',
    field: 'to_account_name',
    align: 'left',
  },
  {
    name: 'amount',
    label: 'Amount',
    field: 'amount',
    align: 'right',
    format: (val) => format.number(val, 0, true),
  },
  {
    name: 'balance',
    label: 'Running Balance',
    field: 'balance',
    align: 'right',
    format: (val) => format.number(val, 0, true),
  },
  {
    name: 'reconcile',
    label: 'Reported',
    field: 'reconcile',
    align: 'right',
    format: (val) => format.number(val, 0, false),
  },
]
</script>
