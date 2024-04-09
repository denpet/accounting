<template>
  <q-table
    class="ellipsis"
    :columns="columns"
    :rows="userStore.index ?? []"
    row-key="id"
    :rows-per-page-options="[0]"
    dense
    @row-click="onShow"
  >
    <template #top-left>
      <div class="q-table__title">Users</div>
      <q-btn
        @click.stop="userStore.create()"
        icon="mdi-plus"
        label="User"
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
import { useUserUserStore } from 'stores/user/user'
import { QTableColumn } from 'quasar'

const userStore = useUserUserStore()
userStore.fetchIndex()

const onShow = (event: Event, row: { id: number }) => {
  userStore.show(row.id)
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
    name: 'email',
    label: 'E-mail',
    field: 'email',
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
