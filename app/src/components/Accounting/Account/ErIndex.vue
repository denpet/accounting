<template>
  <q-table
    class="q-ma-md ellipsis"
    :columns="columns"
    :rows="accountStore.index ?? []"
    row-key="id"
    :rows-per-page-options="[0]"
    dense
  >
    <template #top-left>
      <div class="q-table__title">Accounts</div>
      <q-btn
        @click.stop="onCreateAccount"
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

    <template #header="header">
      <q-tr :props="header">
        <q-th />
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
      <ErCreate
        v-for="node in accountStore.created"
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
        <q-td v-else colspan="6" style="padding: 0px">
          <ErEdit
            :id="body.key"
            @changed="body.expand = false"
            class="q-mr-none"
          />
        </q-td>
      </q-tr>
    </template>
  </q-table>
</template>

<script setup lang="ts">
import { useAccountingAccountStore } from 'stores/accounting/account'
import ErCreate from './ErCreate.vue'
import ErEdit from './ErEdit.vue'
import { QTableColumn, useQuasar } from 'quasar'

const $q = useQuasar()

const accountStore = useAccountingAccountStore()
accountStore.fetchIndex()

const onCreateAccount = () => accountStore.create()

const onDestroy = (id: 0) => {
  $q.dialog({
    title: 'Confirm',
    message: 'Are you sure you want to delete the row?',
    cancel: true,
    persistent: true,
  }).onOk(() => {
    accountStore.destroy(id)
  })
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
