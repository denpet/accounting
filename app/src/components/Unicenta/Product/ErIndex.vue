<template>
  <q-table
    class="ellipsis"
    :columns="columns"
    :rows="productStore.index ?? []"
    row-key="id"
    :rows-per-page-options="[0]"
    dense
    @row-click="onShow"
  >
    <template #top-left>
      <div class="q-table__title">Products</div>
      <q-select
        v-model="productStore.filter.category"
        label="Category"
        class="q-ml-lg"
        :options="categoryStore.options"
        map-options
        emit-value
        style="width: 25vh; max-width: 25vh"
        clearable
        @update:model-value="productStore.fetchIndex()"
      />
    </template>
  </q-table>
</template>

<script setup lang="ts">
import { useUnicentaProductStore } from 'stores/unicenta/product'
import { useUnicentaProductBundleStore } from 'stores/unicenta/product-bundle'
import { useUnicentaCategoryStore } from 'stores/unicenta/category'
import { QTableColumn } from 'quasar'

const productStore = useUnicentaProductStore()
const categoryStore = useUnicentaCategoryStore()
const productBundleStore = useUnicentaProductBundleStore()

productStore.fetchIndex()
categoryStore.fetchOptions()

const onShow = (event: Event, row: { id: string }) => {
  productStore.show(row.id)
  productBundleStore.filter.product = row.id
  productBundleStore.fetchIndex()
}

const columns: QTableColumn[] = [
  {
    name: 'name',
    label: 'Name',
    field: 'name',
    align: 'left',
    sortable: true,
  },
]
</script>
