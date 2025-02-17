<template>
  <q-page>
    <h1>Closed Cash</h1>
    <div class="row">
      <ErFilter class="col-3 q-pa-md" />
      <q-btn label="Show" @click="onShowClosedCash" />
    </div>
    <div class="row">
      <ErResult v-if="reportStore.closedCash !== undefined" class="col-12" />
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { useAccountingReportStore } from 'stores/accounting/report'
import ErFilter from 'components/Accounting/Report/ClosedCash/ErFilter.vue'
import ErResult from 'components/Accounting/Report/ClosedCash/ErResult.vue'

const reportStore = useAccountingReportStore()

const onShowClosedCash = () => {
  reportStore.fetchClosedCash()
}
</script>
