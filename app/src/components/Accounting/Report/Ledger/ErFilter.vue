<template>
  <q-form class="row">
    <q-input
      label="From"
      filled
      v-model="reportStore.ledgerFilter.from"
      mask="####-##-##"
      class="col-5"
    >
      <template v-slot:append>
        <q-icon name="event" class="cursor-pointer">
          <q-popup-proxy cover transition-show="scale" transition-hide="scale">
            <q-date v-model="reportStore.ledgerFilter.from" mask="YYYY-MM-DD">
              <div class="row items-center justify-end">
                <q-btn v-close-popup label="Close" color="primary" flat />
              </div>
            </q-date>
          </q-popup-proxy>
        </q-icon>
      </template>
    </q-input>
    <q-input
      label="To"
      filled
      v-model="reportStore.ledgerFilter.to"
      mask="####-##-##"
      class="col-5 q-ml-md"
    >
      <template v-slot:append>
        <q-icon name="event" class="cursor-pointer">
          <q-popup-proxy cover transition-show="scale" transition-hide="scale">
            <q-date v-model="reportStore.ledgerFilter.to" mask="YYYY-MM-DD">
              <div class="row items-center justify-end">
                <q-btn v-close-popup label="Close" color="primary" flat />
              </div>
            </q-date>
          </q-popup-proxy>
        </q-icon>
      </template>
    </q-input>
  </q-form>
</template>

<script setup lang="ts">
import { useAccountingReportStore } from 'stores/accounting/report'

const reportStore = useAccountingReportStore()
</script>
