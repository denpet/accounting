<template>
  <q-form
    v-if="employeeStore.current"
    @submit="onSubmit"
    @reset="employeeStore.current = undefined"
    class="q-gutter-md"
  >
    <q-chip
      v-if="employeeStore.current.id"
      :label="employeeStore.current.id"
      icon="mdi-identifier"
      color="orange-6"
    />
    <q-chip v-else label="New " icon="mdi-identifier" color="orange-6" />
    <q-input
      label="Name"
      v-model="employeeStore.current.name"
      :error="employeeStore.errors?.name !== undefined"
      :error-message="employeeStore.errors?.name?.toString()"
    />
    <q-input
      label="Rate"
      v-model="employeeStore.current.rate"
      :error="employeeStore.errors?.rate !== undefined"
      :error-message="employeeStore.errors?.rate?.toString()"
    />
    <q-select
      v-model="employeeStore.current.active"
      label="Type"
      :options="[
        { value: 0, label: 'Inactive' },
        { value: 1, label: 'Regular' },
        { value: 2, label: 'On-call' },
      ]"
      map-options
      emit-value
      style="width: 100%; max-width: 40vh"
      clearable
      :error="employeeStore.errors?.active !== undefined"
      :error-message="employeeStore.errors?.active?.toString()"
    />
    <q-toolbar>
      <q-btn label="Submit" type="submit" color="positive" />
      <q-space />
      <q-btn label="Cancel" type="reset" color="warning" />
      <q-space />
      <q-btn
        v-if="employeeStore.current?.id"
        label="Delete"
        @click="onDestroy"
        color="negative"
      />
    </q-toolbar>
  </q-form>
</template>

<script setup lang="ts">
import { useQuasar } from 'quasar'
import { usePayrollEmployeeStore } from 'stores/payroll/employee'

const $q = useQuasar()

const employeeStore = usePayrollEmployeeStore()

const onSubmit = () => {
  if (employeeStore.current?.id) {
    employeeStore.update(employeeStore.current.id).then(() => {
      employeeStore.current = undefined
    })
  } else {
    employeeStore.store().then(() => {
      employeeStore.current = undefined
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
    employeeStore.destroy(employeeStore.current?.id ?? 0).then(() => {
      employeeStore.current = undefined
    })
  })
}
</script>
