<template>
  <q-form class="row">
    <q-input
      label="From"
      filled
      v-model="reportStore.transactionsFilter.from"
      mask="####-##-##"
      class="col-5"
    >
      <template v-slot:append>
        <q-icon name="event" class="cursor-pointer">
          <q-popup-proxy cover transition-show="scale" transition-hide="scale">
            <q-date
              v-model="reportStore.transactionsFilter.from"
              mask="YYYY-MM-DD"
            >
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
      v-model="reportStore.transactionsFilter.to"
      mask="####-##-##"
      class="col-5 q-ml-md"
    >
      <template v-slot:append>
        <q-icon name="event" class="cursor-pointer">
          <q-popup-proxy cover transition-show="scale" transition-hide="scale">
            <q-date
              v-model="reportStore.transactionsFilter.to"
              mask="YYYY-MM-DD"
            >
              <div class="row items-center justify-end">
                <q-btn v-close-popup label="Close" color="primary" flat />
              </div>
            </q-date>
          </q-popup-proxy>
        </q-icon>
      </template>
    </q-input>
    <q-select
      v-model="reportStore.transactionsFilter.account"
      label="Account"
      :options="accountStore.options"
      map-options
      emit-value
      style="width: 100%; max-width: 40vh"
      clearable
    />
  </q-form>
</template>

<script setup lang="ts">
import { useAccountingReportStore } from 'stores/accounting/report'
import { useAccountingAccountStore } from 'stores/accounting/account'

const reportStore = useAccountingReportStore()
const accountStore = useAccountingAccountStore()
accountStore.fetchOptions()
</script>
