<template>
  <q-form
    v-if="supplierStore.current"
    @submit="onSubmit"
    @reset="supplierStore.current = undefined"
    class="q-gutter-md"
  >
    <q-chip
      v-if="supplierStore.current.id"
      :label="supplierStore.current.id"
      icon="mdi-identifier"
      color="orange-6"
    />
    <q-chip v-else label="New " icon="mdi-identifier" color="orange-6" />

    <div v-if="supplierStore.current.id">
      User Id <b>{{ supplierStore.current.id }}</b>
    </div>
    <q-input
      label="TIN"
      v-model="supplierStore.current.tin"
      mask="###-###-###-#####"
      unmasked-value
      :error="supplierStore.errors?.tin !== undefined"
      :error-message="supplierStore.errors?.tin?.toString()"
    />
    <q-input
      label="Name"
      v-model="supplierStore.current.name"
      :error="supplierStore.errors?.name !== undefined"
      :error-message="supplierStore.errors?.name?.toString()"
    />
    <q-input
      label="Address 1"
      v-model="supplierStore.current.address1"
      :error="supplierStore.errors?.address1 !== undefined"
      :error-message="supplierStore.errors?.address1?.toString()"
    />
    <q-input
      label="Address 2"
      v-model="supplierStore.current.address2"
      :error="supplierStore.errors?.address2 !== undefined"
      :error-message="supplierStore.errors?.address2?.toString()"
    />
    <q-input
      label="Postal Code"
      v-model="supplierStore.current.postal_code"
      :error="supplierStore.errors?.postal_code !== undefined"
      :error-message="supplierStore.errors?.postal_code?.toString()"
    />
    <q-input
      label="City"
      v-model="supplierStore.current.city"
      :error="supplierStore.errors?.city !== undefined"
      :error-message="supplierStore.errors?.city?.toString()"
    />
    <q-input
      label="Province"
      v-model="supplierStore.current.province"
      :error="supplierStore.errors?.province !== undefined"
      :error-message="supplierStore.errors?.province?.toString()"
    />
    <q-input
      label="Phone Number"
      v-model="supplierStore.current.phone_number"
      :error="supplierStore.errors?.phone_number !== undefined"
      :error-message="supplierStore.errors?.phone_number?.toString()"
    />
    <q-toolbar>
      <q-btn label="Submit" type="submit" color="positive" />
      <q-space />
      <q-btn label="Cancel" type="reset" color="warning" />
      <q-space />
      <q-btn
        v-if="supplierStore.current?.id"
        label="Delete"
        @click="onDestroy"
        color="negative"
      />
    </q-toolbar>
  </q-form>
</template>

<script setup lang="ts">
import { useQuasar } from 'quasar'
import { useAccountingSupplierStore } from 'stores/accounting/supplier'

const $q = useQuasar()

const supplierStore = useAccountingSupplierStore()

const onSubmit = () => {
  if (supplierStore.current?.id) {
    supplierStore.update(supplierStore.current.id).then(() => {
      supplierStore.current = undefined
    })
  } else {
    supplierStore.store().then(() => {
      supplierStore.current = undefined
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
    supplierStore.destroy(supplierStore.current?.id ?? 0).then(() => {
      supplierStore.current = undefined
    })
  })
}
</script>
