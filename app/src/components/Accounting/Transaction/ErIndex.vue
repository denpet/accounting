<template>
  <q-table
    ref="table"
    class="ellipsis"
    :columns="columns"
    :visible-columns="['id', 'date', 'note', 'amount']"
    :rows="transactionStore.index ?? []"
    row-key="id"
    :rows-per-page-options="[25, 50, 0]"
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
      <q-input
        v-model="transactionStore.filter.note"
        class="q-ml-md"
        label="Search"
        :error="transactionStore.errors?.supplier_id !== undefined"
        :error-message="transactionStore.errors?.supplier_id?.toString()"
        @update:model-value="transactionStore.fetchIndex()"
        debounce="500"
      />
    </template>
  </q-table>
</template>

<script setup lang="ts">
import {
  useAccountingTransactionStore,
  TransactionObject,
} from 'stores/accounting/transaction'
import { QTable, QTableColumn } from 'quasar'
import { onMounted, Ref, ref } from 'vue'

const transactionStore = useAccountingTransactionStore()
transactionStore.filter.date = '2024%'
transactionStore.fetchIndex()
const table: Ref<QTable | null> = ref(null)

onMounted(() => {
  if (table.value) table.value.sort('updated_at')
})

const onCreate = () => {
  transactionStore.create(<TransactionObject>{
    id: null,
    date: new Date().toLocaleDateString('sv'),
    from_account_id: 1,
    to_account_id: 12,
    note: '',
    amount: null,
    vat: null,
    supplier_id: null,
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
  {
    name: 'updated_at',
    label: 'Updated',
    field: 'updated_at',
    align: 'left',
    sortable: true,
    sortOrder: 'da',
  },
]
</script>
