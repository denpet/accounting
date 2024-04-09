<template>
  <q-page>
    <q-btn
      label="Accounting"
      @click="onCreateTransaction"
      icon="mdi-cash-register"
      color="grey"
      style="width: 90%; height: 100px; margin: 5%"
    />
  </q-page>
</template>

<script setup lang="ts">
import {
  useAccountingTransactionStore,
  TransactionObject,
} from 'stores/accounting/transaction'
import { useRouter } from 'vue-router'

const router = useRouter()

const transactionStore = useAccountingTransactionStore()

const onCreateTransaction = () => {
  transactionStore.create(<TransactionObject>{
    id: null,
    date: new Date().toLocaleDateString('sv'),
    from_account_id: 1,
    to_account_id: 12,
    note: '',
    amount: null,
    vat: null,
    tin: null,
    official_receipt: null,
  })
  router.push('/accounting/transaction')
}
</script>
