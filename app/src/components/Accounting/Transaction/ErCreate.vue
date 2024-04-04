<template>
  <q-form @submit="onSubmit" class="q-gutter-md">
    <q-input filled v-model="transactionStore.current.date" mask="####-##-##">
      <template v-slot:append>
        <q-icon name="event" class="cursor-pointer">
          <q-popup-proxy cover transition-show="scale" transition-hide="scale">
            <q-date
              v-close-popup
              v-model="transactionStore.current.date"
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
      label="From"
      v-model="transactionStore.current.from_account_id"
      :options="fromAccountOptions"
      map-options
    />
    <q-select
      label="To"
      v-model="transactionStore.current.to_account_id"
      :options="toAccountOptions"
      map-options
    />
    <q-input label="Description" v-model="transactionStore.current.note" />
    <q-input
      label="Amount"
      type="number"
      v-model.number="transactionStore.current.amount"
    />
    <q-input
      label="VAT"
      type="number"
      v-model.number="transactionStore.current.vat"
    />
    <q-input label="TIN" v-model.number="transactionStore.current.tin" />
    <q-input
      label="o.r."
      v-model.number="transactionStore.current.official_receipt"
    />
    <div>
      <q-btn label="Submit" type="submit" color="primary" />
      <q-btn label="Reset" type="reset" color="primary" flat class="q-ml-sm" />
    </div>
  </q-form>
</template>

<script setup lang="ts">
import {
  useAccountingTransactionStore,
  TransactionObject,
} from 'stores/accounting/transaction'
import { useAccountingAccountStore } from 'stores/accounting/account'
import { computed, onMounted } from 'vue'

const transactionStore = useAccountingTransactionStore()
transactionStore.create(<TransactionObject>{
  id: null,
  date: new Date().toLocaleDateString('sv'),
  from_account_id: 1,
  to_account_id: 12,
  note: '',
  amount: 0,
  vat: null,
  tin: null,
  official_receipt: null,
})

const accountStore = useAccountingAccountStore()
onMounted(() => {
  accountStore.fetchOptions()
})

const fromAccountOptions = computed(() => {
  return accountStore.options.filter((value) => {
    return [1, 3].includes(value.value)
  })
})

const toAccountOptions = computed(() => {
  return accountStore.options.filter((value) => {
    return [
      9, 10, 11, 12, 13, 14, 15, 19, 20, 22, 23, 24, 25, 26, 28, 29, 33, 36, 44,
      45, 48, 49, 50, 51, 53, 54,
    ].includes(value.value)
  })
})

const onSubmit = () => {
  transactionStore.store()
}
</script>
