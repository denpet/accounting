<template>
  <q-table
    class="ellipsis"
    :columns="columns"
    :rows="countryStore.index ?? []"
    row-key="id"
    :rows-per-page-options="[0]"
    dense
    @row-click="onShow"
  >
    <template #top-left>
      <div class="q-table__title">Countries</div>
      <q-btn
        @click.stop="countryStore.create()"
        icon="mdi-plus"
        label="Country"
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
import { useGlobalCountryStore } from 'stores/global/country'
import { QTableColumn } from 'quasar'

const countryStore = useGlobalCountryStore()
countryStore.fetchIndex()

const onShow = (event: Event, row: { id: number }) => {
  countryStore.show(row.id)
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
    name: 'code',
    label: 'Code',
    field: 'code',
    align: 'left',
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
