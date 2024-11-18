<template>
  <q-page>
    <q-btn
      v-if="auth.current.role_id === 1"
      label="Accounting"
      @click="onCreateTransaction"
      icon="mdi-cash-register"
      color="grey"
      style="width: 100%; height: 100px; margin-bottom: 50px"
    />
    <q-btn
      v-if="auth.current.role_id === 1"
      label="Report Cash"
      @click="onReportCash"
      icon="mdi-cash-multiple"
      color="grey"
      style="width: 100%; height: 100px"
    />
  </q-page>
</template>

<script setup lang="ts">
import {
  useAccountingTransactionStore,
  TransactionObject,
} from 'stores/accounting/transaction'
import { useRouter } from 'vue-router'
import { useAuthStore } from 'stores/auth'

const auth = useAuthStore()

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

const onReportCash = () => {
  router.push('/accounting/cash')
}
</script>
