<template>
  <template class="row">
    <q-table
      class="ellipsis col-3"
      :columns="columns"
      :rows="productStore.pricebuyIndex ?? []"
      row-key="id"
      :rows-per-page-options="[0]"
      dense
    >
      <template #body-cell-pricebuy="cell">
        <q-input
          v-model="cell.row.pricebuy"
          label="Cost"
          @change="onUpdatePricebuy(cell.row)"
        />
      </template>
    </q-table>
  </template>
</template>

<script setup lang="ts">
import { useUnicentaProductStore } from 'stores/unicenta/product'
import { QTableColumn } from 'quasar'

const productStore = useUnicentaProductStore()
productStore.fetchPricebuyIndex()

const onUpdatePricebuy = (row: { id: string; pricebuy: number }) => {
  productStore.updatePricebuy(row.id, row.pricebuy)
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
  },
]
</script>
