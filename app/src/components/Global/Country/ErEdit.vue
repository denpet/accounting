<template>
  <q-form
    v-if="countryStore.current"
    @submit="onSubmit"
    @reset="countryStore.current = undefined"
    class="q-gutter-md"
  >
    <q-chip
      v-if="countryStore.current.id"
      :label="countryStore.current.id"
      icon="mdi-identifier"
      color="orange-6"
    />
    <q-chip v-else label="New " icon="mdi-identifier" color="orange-6" />
    <q-input
      label="Code"
      v-model="countryStore.current.code"
      :error="countryStore.errors?.code !== undefined"
      :error-message="countryStore.errors?.code?.toString()"
    />
    <q-input
      label="Name"
      v-model="countryStore.current.name"
      :error="countryStore.errors?.name !== undefined"
      :error-message="countryStore.errors?.name?.toString()"
    />
    <q-toolbar>
      <q-btn label="Submit" type="submit" color="positive" />
      <q-space />
      <q-btn label="Cancel" type="reset" color="warning" />
      <q-space />
      <q-btn
        v-if="countryStore.current?.id"
        label="Delete"
        @click="onDestroy"
        color="negative"
      />
    </q-toolbar>
  </q-form>
</template>

<script setup lang="ts">
import { useQuasar } from 'quasar'
import { useGlobalCountryStore } from 'stores/global/country'

const $q = useQuasar()

const countryStore = useGlobalCountryStore()

const onSubmit = () => {
  if (countryStore.current?.id) {
    countryStore.update(countryStore.current.id).then(() => {
      countryStore.current = undefined
    })
  } else {
    countryStore.store().then(() => {
      countryStore.current = undefined
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
    countryStore.destroy(countryStore.current?.id ?? 0).then(() => {
      countryStore.current = undefined
    })
  })
}
</script>
