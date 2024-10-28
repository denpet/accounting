<template>
  <q-page>
    <h1>Ledger</h1>
    <div class="row">
      <ErFilter class="col-3 q-pa-md" />
      <q-btn label="Show" @click="onShowLedger" />
    </div>
    <div class="row">
      <ErResult v-if="reportStore.ledger !== undefined" class="col-12" />
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { useAccountingReportStore } from 'stores/accounting/report'
import ErFilter from 'components/Accounting/Report/Ledger/ErFilter.vue'
import ErResult from 'components/Accounting/Report/Ledger/ErResult.vue'

const reportStore = useAccountingReportStore()

const onShowLedger = () => {
  reportStore.fetchLedger()
}
</script>
