<template>
  <q-page>
    <h1>Transactions</h1>
    <div class="row">
      <ErFilter class="col-3 q-pa-md" />
      <q-btn label="Show" @click="onShowTransactions" />
    </div>
    <div class="row">
      <ErResult v-if="reportStore.transactions !== undefined" class="col-12" />
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { useAccountingReportStore } from 'stores/accounting/report'
import ErFilter from 'components/Accounting/Report/Transactions/ErFilter.vue'
import ErResult from 'components/Accounting/Report/Transactions/ErResult.vue'

const reportStore = useAccountingReportStore()

const onShowTransactions = () => {
  reportStore.fetchTransactions()
}
</script>
