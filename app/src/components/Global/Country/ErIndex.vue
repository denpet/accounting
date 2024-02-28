<template>
  <q-table
    class="q-ma-md ellipsis"
    :columns="columns"
    :rows="countryStore.index ?? []"
    row-key="country"
    :rows-per-page-options="[0]"
    dense
  >
    <template #top-left>
      <div class="q-table__title">Countries</div>
      <q-btn
        @click.stop="onCreateCountry"
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

    <template #header="header">
      <q-tr :props="header">
        <q-th auto-width />
        <q-th
          v-for="col in header.cols"
          :key="col.name"
          :props="header"
          :style="col.style"
        >
          {{ col.label }}
        </q-th>
        <q-th />
      </q-tr>
      <ErCountryCreate
        v-for="node in countryStore.created"
        :key="node[0]"
        :id="node[0]"
      />
    </template>

    <template #body="body">
      <q-tr
        :props="body"
        :class="body.expand ? '' : 'cursor-pointer'"
        @click="body.expand = true"
      >
        <q-td auto-width style="vertical-align: top">
          <q-btn
            color="accent"
            size="sm"
            round
            dense
            :icon="body.expand ? 'mdi-arrow-down' : 'mdi-arrow-right'"
            @click.stop="body.expand = !body.expand"
          />
        </q-td>
        <template v-if="!body.expand">
          <q-td v-for="col in body.cols" :key="col.name">
            {{ col.value }}
          </q-td>
          <q-td auto-width>
            <q-btn
              color="negative"
              icon="mdi-delete"
              size="sm"
              round
              flat
              @click="onDestroy(body.key)"
            />
          </q-td>
        </template>
        <ErCountryEdit
          v-else
          :country-id="body.key"
          @changed="body.expand = false"
          class="q-mr node"
        />
      </q-tr>
    </template>
  </q-table>
</template>

<script setup lang="ts">
import { useGlobalCountryStore } from 'stores/global/country'
import ErCountryEdit from './ErEdit.vue'
import ErCountryCreate from './ErCreate.vue'
import { QTableColumn, useQuasar } from 'quasar'

const $q = useQuasar()

const countryStore = useGlobalCountryStore()
countryStore.fetchIndex()

const onCreateCountry = () => countryStore.create()

const onDestroy = (countryId: string) => {
  $q.dialog({
    title: 'Confirm',
    message: 'Are you sure you want to delete the row?',
    cancel: true,
    persistent: true,
  }).onOk(() => {
    countryStore.destroy(countryId)
  })
}

const columns: QTableColumn[] = [
  {
    name: 'country',
    label: 'Country',
    field: 'country',
    align: 'left',
    style: 'width: 5rem',
    sortable: true,
  },
  {
    name: 'country_name',
    label: 'Country',
    field: 'country_name',
    align: 'left',
    sortable: true,
  },
]
</script>
