<template>
  <q-page class="row">
    <ErFilter class="col-4 q-pa-md" v-model="reportStore.ledgerFilter.date" />
    <ErResult v-if="reportStore.ledger" class="col-8 q-pa-md" />
  </q-page>
</template>

<script setup lang="ts">
import { useAccountingReportStore } from 'stores/accounting/report'
import ErFilter from 'components/Accounting/Report/Ledger/ErFilter.vue'
import ErResult from 'components/Accounting/Report/Ledger/ErResult.vue'

const reportStore = useAccountingReportStore()
</script>
