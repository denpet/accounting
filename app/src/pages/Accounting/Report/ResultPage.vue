<template>
  <q-page>
    <h1>Result</h1>
    <div class="row">
      <ErFilter class="col-3 q-pa-md" />
      <q-btn label="Show" @click="onShowResult" />
    </div>
    <div class="row">
      <ErResult v-if="reportStore.result !== undefined" />
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { useAccountingReportStore } from 'stores/accounting/report'
import ErFilter from 'components/Accounting/Report/Result/ErFilter.vue'
import ErResult from 'components/Accounting/Report/Result/ErResult.vue'

const reportStore = useAccountingReportStore()

const onShowResult = () => {
  reportStore.fetchResult()
}
</script>
