<template>
  <q-page>
    <h1>Account Transactions</h1>
    <div class="row">
      <ErFilter class="col-3 q-pa-md" />
      <q-btn label="Show" @click="onShowAccountTransactions" />
    </div>
    <div class="row">
      <ErResult
        v-if="reportStore.accountTransactions !== undefined"
        class="col-12"
      />
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { useAccountingReportStore } from 'stores/accounting/report'
import ErFilter from 'components/Accounting/Report/AccountTransactions/ErFilter.vue'
import ErResult from 'components/Accounting/Report/AccountTransactions/ErResult.vue'

const reportStore = useAccountingReportStore()

const onShowAccountTransactions = () => {
  reportStore.fetchAccountTransactions()
}
</script>
