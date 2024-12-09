<template>
  <q-table
    ref="table"
    class="ellipsis"
    :columns="columns"
    :rows="supplierStore.index ?? []"
    row-key="id"
    :rows-per-page-options="[25, 50, 0]"
    dense
    @row-click="onShow"
  >
    <template #top-left>
      <div class="q-table__title">Suppliers</div>
      <q-btn
        @click.stop="onCreate"
        icon="mdi-plus"
        label="Supplier"
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
  useAccountingSupplierStore,
  SupplierObject,
} from 'stores/accounting/supplier'
import { QTable, QTableColumn } from 'quasar'
import { onMounted } from 'vue'
import { format } from 'boot/format'

const supplierStore = useAccountingSupplierStore()

onMounted(() => {
  supplierStore.filter = { word: '' }
  supplierStore.fetchIndex()
})

const onCreate = () => {
  supplierStore.create(<SupplierObject>{
    id: null,
    tin: null,
    name: '',
    address1: null,
    address2: null,
    postal_code: null,
    city: null,
    province: null,
    phone_number: null,
  })
}

const onShow = (event: Event, row: { id: number }) => {
  supplierStore.show(row.id)
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
    name: 'tin',
    label: 'Tin',
    field: 'tin',
    align: 'left',
    format: (val) => format.tin(val),
    sortable: true,
  },
  {
    name: 'name',
    label: 'Name',
    field: 'name',
    align: 'left',
    sortable: true,
  },
]
</script>
