<template>
  <template class="row">
    <q-table
      class="ellipsis col-5"
      :columns="columns"
      :rows="productStore.index ?? []"
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
            @click="onRegisterCycleCount(cell.row)"
          />
        </q-td>
      </template>
    </q-table>
  </template>
</template>

<script setup lang="ts">
import { useUnicentaProductStore } from 'stores/unicenta/product'
import { QTableColumn } from 'quasar'

const productStore = useUnicentaProductStore()
productStore.fetchPricebuyIndex()

const onRegisterCycleCount = (row: {
  id: string
  quantity: number
  $error: string | undefined
}) => {
  row.$error = undefined
  productStore
    .registerCycleCount(row.id, row.quantity)
    .then(() => {
      productStore.index = productStore.index.filter(
        (val: { id: string }) => val.id !== row.id,
      )
    })
    .catch((data: { message: string }) => {
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
    name: 'quantity',
    label: 'Quantity',
    field: 'quantity',
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
