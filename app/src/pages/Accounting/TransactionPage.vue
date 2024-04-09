<template>
  <q-page class="row">
    <ErIndex
      v-if="!$q.platform.is.mobile || !transactionStore.current"
      :class="[
        $q.platform.is.mobile ? 'col-12 q-pa-none' : 'col-4 q-pa-md',
        '',
      ]"
    />
    <ErEdit
      v-if="transactionStore.current"
      :class="[
        $q.platform.is.mobile ? 'col-12 q-pa-none' : 'col-8 q-pa-md',
        '',
      ]"
    />
  </q-page>
</template>

<script setup lang="ts">
import { useAccountingTransactionStore } from 'stores/accounting/transaction'
import ErIndex from 'components/Accounting/Transaction/ErIndex.vue'
import ErEdit from 'components/Accounting/Transaction/ErEdit.vue'

const transactionStore = useAccountingTransactionStore()
</script>
