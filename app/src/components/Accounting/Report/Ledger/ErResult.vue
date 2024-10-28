<template>
  <q-table :rows="reportStore.ledger" :columns="columns" hide-bottom>
    <template #header="header">
      <q-tr :props="header">
        <q-th style="width: 1em !important" />
        <q-th
          v-for="col in header.cols"
          :key="col.name"
          :props="header"
          :style="col.style"
        >
          {{ col.label }}
        </q-th>
      </q-tr>
    </template>
    <template #body="body">
      <q-tr
        :props="body"
        :class="body.expand ? '' : 'cursor-pointer'"
        @click="body.expand = true"
      >
        <q-td class="items-center">
          <q-btn
            color="accent"
            round
            size="xs"
            :icon="body.expand ? 'mdi-arrow-down' : 'mdi-arrow-right'"
            @click.stop="body.expand = !body.expand"
            class=""
          />
        </q-td>
        <q-td v-for="col in body.cols" :key="col.name" :align="col.align">
          {{ col.value }}
        </q-td>
      </q-tr>
      <template v-if="body.expand">
        <q-tr>
          <q-th />
          <q-th align="center">Date</q-th>
          <q-th>Note</q-th>
          <q-th align="right">Amount</q-th>
        </q-tr>
        <q-tr
          v-for="transaction in body.row.transactions"
          :key="transaction.id"
        >
          <q-td />
          <q-td align="center">{{ transaction.date }}</q-td>
          <q-td>{{ transaction.note }}</q-td>
          <q-td align="right">{{ transaction.amount }}</q-td>
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
    name: 'action',
    label: '',
    field: 'action',
    align: 'left',
    sortable: true,
  },
  {
    name: 'name',
    label: 'Account',
    field: 'name',
    align: 'left',
    sortable: true,
  },
  {
    name: 'opening_balance',
    label: 'Opening balance',
    field: 'opening_balance',
    align: 'right',
    sortable: true,
    format: (val) => format.number(val, 0, true),
  },
  {
    name: 'closing_balance',
    label: 'Closing balance',
    field: 'closing_balance',
    align: 'right',
    sortable: true,
    format: (val) => format.number(val, 0, true),
  },
]
</script>
