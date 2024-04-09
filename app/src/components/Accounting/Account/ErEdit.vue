<template>
  <q-form
    v-if="accountStore.current"
    @submit="onSubmit"
    @reset="accountStore.current = undefined"
    class="q-gutter-md"
  >
    <q-chip
      v-if="accountStore.current.id"
      :label="accountStore.current.id"
      icon="mdi-identifier"
      color="orange-6"
    />
    <q-chip v-else label="New " icon="mdi-identifier" color="orange-6" />

    <div v-if="accountStore.current.id">
      User Id <b>{{ accountStore.current.id }}</b>
    </div>
    <q-input
      label="Name"
      v-model="accountStore.current.name"
      :error="accountStore.errors?.name !== undefined"
      :error-message="accountStore.errors?.name?.toString()"
    />
    <q-select
      v-model="accountStore.current.type"
      label="Type"
      :options="[
        { value: 'A', label: 'Asset' },
        { value: 'L', label: 'Liability' },
        { value: 'E', label: 'Expenses' },
        { value: 'I', label: 'Income' },
        { value: 'C', label: 'Costs?' },
      ]"
      map-options
      emit-value
      style="width: 100%; max-width: 40vh"
      clearable
      :error="accountStore.errors?.type !== undefined"
      :error-message="accountStore.errors?.type?.toString()"
    />
    <q-toolbar>
      <q-btn label="Submit" type="submit" color="positive" />
      <q-space />
      <q-btn label="Cancel" type="reset" color="warning" />
      <q-space />
      <q-btn
        v-if="accountStore.current?.id"
        label="Delete"
        @click="onDestroy"
        color="negative"
      />
    </q-toolbar>
  </q-form>
</template>

<script setup lang="ts">
import { useQuasar } from 'quasar'
import { useAccountingAccountStore } from 'stores/accounting/account'

const $q = useQuasar()

const accountStore = useAccountingAccountStore()

const onSubmit = () => {
  if (accountStore.current?.id) {
    accountStore.update(accountStore.current.id).then(() => {
      accountStore.current = undefined
    })
  } else {
    accountStore.store().then(() => {
      accountStore.current = undefined
    })
  }
}

const onDestroy = () => {
  $q.dialog({
    title: 'Confirm',
    message: 'Are you sure you want to delete the row?',
    cancel: true,
    persistent: true,
  }).onOk(() => {
    accountStore.destroy(accountStore.current?.id ?? 0).then(() => {
      accountStore.current = undefined
    })
  })
}
</script>
