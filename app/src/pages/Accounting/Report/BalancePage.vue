<template>
  <q-page>
    <h1>Balance</h1>
    <div class="row">
      <ErFilter class="col-2 q-pa-md" />
      <q-btn label="Show" @click="onShowBalance" color="primary" />
    </div>
    <div class="row">
      <ErResult v-if="reportStore.balance !== undefined" />
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { useAccountingReportStore } from 'stores/accounting/report'
import ErFilter from 'components/Accounting/Report/Balance/ErFilter.vue'
import ErResult from 'components/Accounting/Report/Balance/ErResult.vue'

const reportStore = useAccountingReportStore()

const onShowBalance = () => {
  reportStore.fetchBalance()
}
</script>
