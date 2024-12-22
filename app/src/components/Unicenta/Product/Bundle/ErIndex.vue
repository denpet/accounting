<template>
  <q-table
    class="ellipsis"
    :columns="columns"
    :rows="productBundleStore.index ?? []"
    row-key="id"
    :rows-per-page-options="[0]"
    dense
  >
    <template #top-left>
      <div class="q-table__title">Ingredients</div>
      <q-btn
        @click.stop="onCreateBundle"
        icon="mdi-plus"
        label="Ingredient"
        color="secondary"
        size="sm"
        rounded
        dense
        align="left"
        style="width: 6rem"
        class="q-ml-md"
      />
    </template>
    <template #body-cell-product_bundle="cell">
      <q-td :props="cell">
        <q-select
          label="Product"
          v-model="cell.row.product_bundle"
          :options="productStore.options"
          map-options
          emit-value
          :hide-bottom-space="cell.row.$error === undefined"
          :error="cell.row.$error !== undefined"
          :error-message="cell.row.$error?.toString()"
          dense
          @blur="onUpdateBundle(cell.row)"
        />
      </q-td>
    </template>
    <template #body-cell-quantity="cell">
      <q-td :props="cell">
        <q-input
          v-model="cell.row.quantity"
          label="Quantity"
          @change="onUpdateBundle(cell.row)"
          :hide-bottom-space="cell.row.$error === undefined"
          :error="cell.row.$error !== undefined"
          :error-message="cell.row.$error?.toString()"
        />
      </q-td>
    </template>
    <template #body-cell-action="cell">
      <q-td :props="cell">
        <q-btn
          icon="mdi-delete"
          flat
          dense
          round
          color="negative"
          @click="onDeleteBundle(cell.row.id)"
        />
      </q-td>
    </template>
  </q-table>
</template>

<script setup lang="ts">
import { useUnicentaProductBundleStore } from 'stores/unicenta/product-bundle'
import { useUnicentaProductStore } from 'stores/unicenta/product'
import { QTableColumn, useQuasar } from 'quasar'

const $q = useQuasar()

const productStore = useUnicentaProductStore('bundle')
const productBundleStore = useUnicentaProductBundleStore()

productStore.filter.category =
  'in(' +
  '775b8dc6-b5b4-4bd6-a667-14bf89e61ee5,' +
  'b71fb4d2-89fb-4d59-8db0-dd1049410ff1,' +
  '3beaadb8-4ff2-441e-b5e9-a315278974f9,' +
  '916fc2a3-4c34-4c6d-bc07-9cac80feef4a,' +
  '65078fce-8f22-440e-a171-0c8848329256,' +
  '52a4962a-ab40-489c-9dcb-b3620fec388b,' +
  '6fca5bfe-f668-4789-b4c0-9e86a2e74187,' +
  'cbd33255-9801-4b98-9e79-9e39c61015a3,' +
  '869d56fc-0a1f-487e-a313-6aa2fcbeaf0b,' +
  'daf248c4-809c-470c-be63-8e84cc19181c,' +
  'b2539686-2ec6-4c73-b3f9-b11983bbe04a,' +
  '251c1032-8abd-4ab4-9f38-c5bd683d9e20,' +
  '5c87caaa-fca1-42bb-945b-24bbadd69fcf,' +
  'ae6eb9a6-b38d-4b5f-b6de-694e8764b878)'
// Stock Items
productStore.fetchOptions()

const onUpdateBundle = (row: {
  id: string
  product_bundle: string
  quantity: number
  $error: string | undefined
}) => {
  row.$error = undefined
  productBundleStore.current = {
    id: row.id,
    product: productBundleStore.filter.product ?? '',
    product_bundle: row.product_bundle,
    quantity: row.quantity,
  }
  productBundleStore.update(row.id).catch((data) => {
    row.$error = data.message
  })
}

const onDeleteBundle = (id: string) => {
  $q.dialog({
    title: 'Confirm',
    message: 'Are you sure you want to delete the row?',
    cancel: true,
    persistent: true,
  }).onOk(() => {
    productBundleStore.destroy(id).then(() => {
      productBundleStore.fetchIndex()
    })
  })
}

const onCreateBundle = () => {
  productBundleStore.create({
    id: null,
    product: productBundleStore.filter.product ?? '',
    product_bundle: 'xxx999_999xxx_x9x9x9',
    quantity: 0,
  })
  productBundleStore.store().then(() => productBundleStore.fetchIndex())
}
const columns: QTableColumn[] = [
  {
    name: 'product_bundle',
    label: 'Product',
    field: 'product_bundle',
    align: 'left',
    sortable: true,
  },
  {
    name: 'quantity',
    label: 'Quantity',
    field: 'quantity',
    align: 'left',
    sortable: true,
  },
  {
    name: 'action',
    label: '',
    field: 'action',
    align: 'left',
    sortable: true,
  },
]
</script>
