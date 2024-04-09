<template>
  <q-table
    class="ellipsis"
    :columns="columns"
    :rows="transactionStore.index ?? []"
    row-key="id"
    :rows-per-page-options="[0]"
    dense
    @row-click="onShow"
  >
    <template #top-left>
      <div class="q-table__title">Transactions</div>
      <q-btn
        @click.stop="onCreate"
        icon="mdi-plus"
        label="Transaction"
        color="secondary"
        size="sm"
        rounded
        dense
        align="left"
        style="width: 6rem"
        class="q-ml-md"
      />
    </template>
  </q-table>
</template>

<script setup lang="ts">
import {
  useAccountingTransactionStore,
  TransactionObject,
} from 'stores/accounting/transaction'
import { QTableColumn } from 'quasar'

const transactionStore = useAccountingTransactionStore()
transactionStore.filter.date = '2024%'
transactionStore.fetchIndex()

const onCreate = () => {
  transactionStore.create(<TransactionObject>{
    id: null,
    date: new Date().toLocaleDateString('sv'),
    from_account_id: 1,
    to_account_id: 12,
    note: '',
    amount: null,
    vat: null,
    tin: null,
    official_receipt: null,
  })
}

const onShow = (event: Event, row: { id: number }) => {
  transactionStore.show(row.id)
}

const columns: QTableColumn[] = [
  {
    name: 'id',
    label: 'Id#',
    field: 'id',
    align: 'left',
    style: 'width: 5rem',
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
    name: 'amount',
    label: 'Amount',
    field: 'amount',
    align: 'right',
    sortable: true,
  },
]
</script>
