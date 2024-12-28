<template>
  <template class="row">
    <q-table
      class="ellipsis col-5"
      :columns="columns"
      :rows="productStore.pricebuyIndex ?? []"
      row-key="id"
      :rows-per-page-options="[0]"
      dense
    >
      <template #body-cell-quantity="cell">
        <q-td>
          <q-input
            v-model="cell.row.quantity"
            label="Quantity"
            input-class="text-right"
            @change="onRecalculatePricebuy(cell.row)"
            :hide-bottom-space="cell.row.$error === undefined"
            :error="cell.row.$error !== undefined"
            :error-message="cell.row.$error?.toString()"
          />
        </q-td>
      </template>
      <template #body-cell-amount="cell">
        <q-td>
          <q-input
            v-model="cell.row.amount"
            label="Amount"
            input-class="text-right"
            @change="onRecalculatePricebuy(cell.row)"
            :hide-bottom-space="cell.row.$error === undefined"
            :error="cell.row.$error !== undefined"
            :error-message="cell.row.$error?.toString()"
          />
        </q-td>
      </template>
      <template #body-cell-action="cell">
        <q-td>
          <q-btn
            v-if="cell.row.quantity && cell.row.amount"
            label="Register"
            icon="mdi-file"
            @click="onRegisterPurchase(cell.row)"
          />
        </q-td>
      </template>
    </q-table>
  </template>
</template>

<script setup lang="ts">
import { useUnicentaProductStore } from 'stores/unicenta/product'
import { QTableColumn } from 'quasar'
import { format } from 'boot/format'

const productStore = useUnicentaProductStore()
productStore.fetchPricebuyIndex()

const onRecalculatePricebuy = (row: {
  id: string
  pricebuy: number
  quantity: number
  amount: number
}) => {
  if (row.quantity && row.amount) {
    row.pricebuy = row.amount / row.quantity
  }
}

const onRegisterPurchase = (row: {
  id: string
  pricebuy: number
  quantity: number
  amount: number
  $error: string | undefined
}) => {
  row.pricebuy = row.amount / row.quantity
  row.$error = undefined
  productStore
    .registerPurchase(row.id, row.quantity, row.amount)
    .then(() => {
      productStore.pricebuyIndex = productStore.pricebuyIndex.filter(
        (val) => val.id !== row.id,
      )
    })
    .catch((data) => {
      row.$error = data.message
    })
}

const columns: QTableColumn[] = [
  {
    name: 'category_name',
    label: 'Category',
    field: 'category_name',
    align: 'left',
    sortable: true,
  },
  {
    name: 'name',
    label: 'Product',
    field: 'name',
    align: 'left',
    sortable: true,
  },
  {
    name: 'pricebuy',
    label: 'Cost',
    field: 'pricebuy',
    align: 'right',
    sortable: true,
    format: (val) => format.number(val, 2, true),
  },
  {
    name: 'quantity',
    label: 'Quantity',
    field: 'quantity',
    align: 'right',
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
    name: 'action',
    label: 'Action',
    field: 'action',
    align: 'left',
    sortable: false,
  },
]
</script>
