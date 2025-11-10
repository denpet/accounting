<template>
  <q-form
    v-if="poolReadingStore.current"
    @submit="onSubmit"
    @reset="poolReadingStore.current = undefined"
    class="q-gutter-md"
  >
    <q-chip
      v-if="poolReadingStore.current.id"
      :label="poolReadingStore.current.id"
      icon="mdi-identifier"
      color="orange-6"
    />
    <q-chip v-else label="New " icon="mdi-identifier" color="orange-6" />
    <div class="row">
      <q-input
        label="Date"
        class="col-4 q-pr-md"
        v-model="poolReadingStore.current.date"
        :error="poolReadingStore.errors?.date !== undefined"
        :error-message="poolReadingStore.errors?.date?.toString()"
      />
    </div>
    <div class="row">
      <q-input
        label="pH"
        class="col-4 q-pr-md"
        v-model="poolReadingStore.current.ph"
        :error="poolReadingStore.errors?.ph !== undefined"
        :error-message="poolReadingStore.errors?.ph?.toString()"
      />
    </div>
    <div class="row">
      <q-input
        label="Free Chlorine"
        class="col-4 q-pr-md"
        v-model="poolReadingStore.current.free_chlorine"
        :error="poolReadingStore.errors?.free_chlorine !== undefined"
        :error-message="poolReadingStore.errors?.free_chlorine?.toString()"
      />
      <q-input
        label="Total Chlorine"
        class="col-4 q-pr-md"
        v-model="poolReadingStore.current.total_chlorine"
        :error="poolReadingStore.errors?.total_chlorine !== undefined"
        :error-message="poolReadingStore.errors?.total_chlorine?.toString()"
      />
      <q-input
        label="Cyanuric Acid"
        class="col-4"
        v-model="poolReadingStore.current.cyanuric_acid"
        :error="poolReadingStore.errors?.cyanuric_acid !== undefined"
        :error-message="poolReadingStore.errors?.cyanuric_acid?.toString()"
      />
    </div>
    <div class="row">
      <q-input
        label="Alkalinity"
        class="col-4 q-pr-md"
        v-model="poolReadingStore.current.alkalinity"
        :error="poolReadingStore.errors?.alkalinity !== undefined"
        :error-message="poolReadingStore.errors?.alkalinity?.toString()"
      />
    </div>
    <div class="row">
      <q-input
        label="Hardness"
        class="col-4 q-pr-md"
        v-model="poolReadingStore.current.hardness"
        :error="poolReadingStore.errors?.hardness !== undefined"
        :error-message="poolReadingStore.errors?.hardness?.toString()"
      />
    </div>
    <q-toolbar>
      <q-btn label="Submit" type="submit" color="positive" />
      <q-space />
      <q-btn label="Cancel" type="reset" color="warning" />
      <q-space />
      <q-btn
        v-if="poolReadingStore.current?.id"
        label="Delete"
        @click="onDestroy"
        color="negative"
      />
    </q-toolbar>
  </q-form>
</template>

<script setup lang="ts">
import { useQuasar } from 'quasar'
import { useMaintenancePoolReadingStore } from 'stores/maintenance/pool-reading'

const $q = useQuasar()

const poolReadingStore = useMaintenancePoolReadingStore()

const onSubmit = () => {
  if (poolReadingStore.current?.id) {
    poolReadingStore.update(poolReadingStore.current.id).then(() => {
      poolReadingStore.current = undefined
    })
  } else {
    poolReadingStore.store().then(() => {
      poolReadingStore.current = undefined
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
    poolReadingStore.destroy(poolReadingStore.current?.id ?? 0).then(() => {
      poolReadingStore.current = undefined
    })
  })
}
</script>
