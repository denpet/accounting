<template>
  <q-table
    class="ellipsis"
    :columns="columns"
    :rows="accountStore.index ?? []"
    row-key="id"
    :rows-per-page-options="[25, 50, 0]"
    dense
    @row-click="onShow"
  >
    <template #top-left>
      <div class="q-table__title">Accounts</div>
      <q-btn
        @click.stop="onCreate"
        icon="mdi-plus"
        label="Account"
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
import { useAccountingAccountStore } from 'stores/accounting/account'
import { QTableColumn } from 'quasar'

const accountStore = useAccountingAccountStore()
accountStore.fetchIndex()

const onCreate = () => accountStore.create()

const onShow = (event: Event, row: { id: number }) => {
  accountStore.show(row.id)
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
  {
    name: 'type',
    label: 'Type',
    field: 'type',
    align: 'left',
    sortable: true,
  },
]
</script>
