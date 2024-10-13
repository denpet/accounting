<template>
  <q-page>
    <router-link
      v-if="timeRecordStore.filter.period"
      :to="`/api/payroll/print/${timeRecordStore.filter.period}`"
      target="_blank"
      class="col-12"
    >
      <q-btn label="Download payroll" color="primary" icon="mdi-download-box" />
    </router-link>
    <div class="row">
      <ErIndex
        v-if="!$q.platform.is.mobile || timeRecordStore.current.length == 0"
        :class="[$q.platform.is.mobile ? 'col-12 q-pa-none' : 'col-3 q-px-md']"
      />
      <ErEdit
        v-if="timeRecordStore.current.length > 0"
        :class="[$q.platform.is.mobile ? 'col-12 q-pa-none' : 'col-9 q-px-md']"
      />
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { usePayrollTimeRecordStore } from 'stores/payroll/time-record'
import ErIndex from 'components/Payroll/TimeRecord/ErIndex.vue'
import ErEdit from 'components/Payroll/TimeRecord/ErEdit.vue'

const timeRecordStore = usePayrollTimeRecordStore()
</script>
