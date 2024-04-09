<template>
  <q-table
    class="ellipsis"
    :columns="columns"
    :rows="customerStore.index ?? []"
    row-key="id"
    :rows-per-page-options="[0]"
    dense
    @row-click="onShow"
  />
</template>

<script setup lang="ts">
import { useUnicentaCustomerStore } from 'stores/unicenta/customer'
import { QTableColumn } from 'quasar'

const customerStore = useUnicentaCustomerStore()
customerStore.filter.visible = '1'
customerStore.fetchIndex()

const onShow = (event: Event, row: { id: number }) => {
  window.open(`/api/unicenta/reports/statement-of-account/${row.id}`)
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
    name: 'name',
    label: 'Name',
    field: 'name',
    align: 'left',
    sortable: true,
  },
]
</script>
