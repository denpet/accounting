<template>
  <q-page class="row">
    <ErIndex
      v-if="!$q.platform.is.mobile || !employeeStore.current"
      :class="[
        $q.platform.is.mobile ? 'col-12 q-pa-none' : 'col-4 q-pa-md',
        '',
      ]"
    />
    <ErEdit
      v-if="employeeStore.current"
      :class="[
        $q.platform.is.mobile ? 'col-12 q-pa-none' : 'col-8 q-pa-md',
        '',
      ]"
    />
  </q-page>
</template>

<script setup lang="ts">
import { usePayrollEmployeeStore } from 'stores/payroll/employee'
import ErIndex from 'components/Payroll/Employee/ErIndex.vue'
import ErEdit from 'components/Payroll/Employee/ErEdit.vue'

const employeeStore = usePayrollEmployeeStore()
</script>
