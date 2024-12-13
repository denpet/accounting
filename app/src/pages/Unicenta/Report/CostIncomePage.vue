<template>
  <q-page>
    <h1>Account Transactions</h1>
    <div class="row">
      <ErFilter class="col-3 q-pa-md" />
      <q-btn label="Show" @click="onShowCostIncomeReport" />
    </div>
    <div class="row">
      <ErResult
        v-if="reportStore.costIncomeReport !== undefined"
        class="col-4"
      />
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { useUnicentaReportStore } from 'stores/unicenta/report'
import ErFilter from 'components/Unicenta/Report/CostIncome/ErFilter.vue'
import ErResult from 'components/Unicenta/Report/CostIncome/ErResult.vue'

const reportStore = useUnicentaReportStore()

const onShowCostIncomeReport = () => {
  reportStore.fetchCostIncomeReport()
}
</script>
