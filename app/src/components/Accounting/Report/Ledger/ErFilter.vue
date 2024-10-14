<template>
  <q-form
    class="accounting"
    action="{url controller='accounting' action='balance-sheet'}"
    method="post"
  >
    <q-input filled v-model="reportStore.balance.filter.date" mask="####-##-##">
      <template v-slot:append>
        <q-icon name="event" class="cursor-pointer">
          <q-popup-proxy cover transition-show="scale" transition-hide="scale">
            <q-date
              v-close-popup
              v-model="reportStore.balance.filter.date"
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

    <q-input label="Date" v-model="reportStore.balance.filter.date" />
  </q-form>
</template>

<script setup lang="ts">
import { useAccountingReportStore } from 'stores/accounting/report'

const reportStore = useAccountingReportStore()
</script>
