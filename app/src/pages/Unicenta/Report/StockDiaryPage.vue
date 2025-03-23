<template>
  <q-page>
    <h1>Stock Diary</h1>
    <div class="row">
      <ErFilter class="col-3 q-pa-md" />
      <q-btn label="Show" @click="onShowStockDiaryReport" />
    </div>
    <div class="row">
      <ErResult
        v-if="reportStore.stockDiaryReport !== undefined"
        class="col-4"
      />
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { useUnicentaReportStore } from 'stores/unicenta/report'
import ErFilter from 'components/Unicenta/Report/StockDiary/ErFilter.vue'
import ErResult from 'components/Unicenta/Report/StockDiary/ErResult.vue'

const reportStore = useUnicentaReportStore()

const onShowStockDiaryReport = () => {
  reportStore.fetchStockDiaryReport()
}
</script>
