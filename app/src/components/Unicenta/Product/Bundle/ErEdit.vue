<template>
  <q-form
    v-if="productStore.current"
    @submit="onSubmit"
    @reset="productStore.current = undefined"
    class="q-gutter-md"
  >
    <q-chip
      v-if="productStore.current.id"
      :label="productStore.current.id"
      icon="mdi-identifier"
      color="orange-6"
    />
    <q-chip v-else label="New " icon="mdi-identifier" color="orange-6" />

    <q-input
      label="Name"
      v-model="productStore.current.name"
      :error="productStore.errors?.name !== undefined"
      :error-message="productStore.errors?.name?.toString()"
    />

    <q-toolbar>
      <q-btn label="Submit" type="submit" color="positive" />
      <q-space />
      <q-btn label="Cancel" type="reset" color="warning" />
      <q-space />
      <q-btn
        v-if="productStore.current?.id"
        label="Delete"
        @click="onDestroy"
        color="negative"
      />
    </q-toolbar>
  </q-form>
</template>

<script setup lang="ts">
import { useQuasar } from 'quasar'
import { useUnicentaProductStore } from 'stores/unicenta/product'

const $q = useQuasar()

const productStore = useUnicentaProductStore()

const onSubmit = () => {
  if (productStore.current?.id) {
    productStore.update(productStore.current.id).then(() => {
      productStore.current = undefined
    })
  } else {
    productStore.store().then(() => {
      productStore.current = undefined
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
    productStore.destroy(productStore.current?.id ?? '').then(() => {
      productStore.current = undefined
    })
  })
}
</script>
