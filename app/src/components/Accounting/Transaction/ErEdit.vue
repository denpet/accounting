<template>
  <q-form
    v-if="transactionStore.current"
    @submit="onSubmit"
    @reset="transactionStore.current = undefined"
    class="q-gutter-md"
  >
    <q-chip
      v-if="transactionStore.current.id"
      :label="transactionStore.current.id"
      icon="mdi-identifier"
      color="orange-6"
    />
    <q-chip v-else label="New " icon="mdi-identifier" color="orange-6" />
    <q-input
      filled
      v-model="transactionStore.current.date"
      mask="####-##-##"
      :error="transactionStore.errors?.date !== undefined"
      :error-message="transactionStore.errors?.date?.toString()"
    >
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
    <div class="row">
      <q-select
        class="col-6 q-pr-xs"
        label="From"
        v-model="transactionStore.current.from_account_id"
        :options="fromAccountOptions"
        map-options
        emit-value
        :error="transactionStore.errors?.from_account_id !== undefined"
        :error-message="transactionStore.errors?.from_account_id?.toString()"
      />
      <q-select
        label="To"
        class="col-6"
        v-model="transactionStore.current.to_account_id"
        :options="toAccountOptions"
        map-options
        emit-value
        :error="transactionStore.errors?.to_account_id !== undefined"
        :error-message="transactionStore.errors?.to_account_id?.toString()"
      />
    </div>
    <q-input
      label="Description"
      v-model="transactionStore.current.note"
      :error="transactionStore.errors?.note !== undefined"
      :error-message="transactionStore.errors?.note?.toString()"
    />
    <div class="row">
      <q-input
        class="col-6 q-pr-xs"
        label="Amount"
        v-model="transactionStore.current.amount"
        :error="transactionStore.errors?.amount !== undefined"
        :error-message="transactionStore.errors?.amount?.toString()"
      />
      <q-input
        label="VAT"
        v-model="transactionStore.current.vat"
        class="col-6"
        :error="transactionStore.errors?.vat !== undefined"
        :error-message="transactionStore.errors?.vat?.toString()"
      />
    </div>
    <q-input
      label="TIN"
      v-model="transactionStore.current.tin"
      :error="transactionStore.errors?.tin !== undefined"
      :error-message="transactionStore.errors?.tin?.toString()"
    />
    <q-input
      label="o.r."
      v-model="transactionStore.current.official_receipt"
      :error="transactionStore.errors?.official_receipt !== undefined"
      :error-message="transactionStore.errors?.official_receipt?.toString()"
    />
    <q-img
      v-for="(image, index) in transactionStore.current.images"
      :key="index"
      :src="image"
      spinner-color="white"
      style="height: 140px; max-width: 150px"
    />
    <q-uploader
      v-if="transactionStore.current.id"
      :factory="uploadFile"
      label="Upload receipts"
      auto-upload
      hide-upload-btn
      field-name="image"
      capture="user"
      no-thumbnails
      @uploaded="transactionStore.show(transactionStore.current?.id ?? 0)"
    />
    <q-toolbar>
      <q-btn label="Submit" type="submit" color="positive" />
      <q-space />
      <q-btn label="Cancel" type="reset" color="warning" />
      <q-space />
      <q-btn label="New" @click="onCreate" color="warning" />
    </q-toolbar>
  </q-form>
</template>

<script setup lang="ts">
import {
  useAccountingTransactionStore,
  TransactionObject,
} from 'stores/accounting/transaction'
import { useAccountingAccountStore } from 'stores/accounting/account'
import { computed, onMounted } from 'vue'
import { Cookies } from 'quasar'
import { useAuthStore } from 'src/stores/auth'

const uploadApi = process.env.API + '/api/accounting/transactions'
const transactionStore = useAccountingTransactionStore()
const auth = useAuthStore()

const onCreate = () => {
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
}

const accountStore = useAccountingAccountStore()
onMounted(() => {
  accountStore.fetchOptions()
})

const fromAccountOptions = computed(() => {
  return auth.current.id == 1
    ? accountStore.options.filter((value: { value: number }) => {
        return [1, 3, 33, 38, 39, 60].includes(value.value)
      })
    : accountStore.options.filter((value: { value: number }) => {
        return [1, 3].includes(value.value)
      })
})

const toAccountOptions = computed(() => {
  return auth.current.id == 1
    ? accountStore.options.filter((value: { value: number }) => {
        return [
          1, 3, 5, 9, 10, 11, 12, 13, 14, 15, 19, 20, 22, 23, 28, 29, 38, 39,
          44, 48, 49, 51, 53, 54, 60,
        ].includes(value.value)
      })
    : accountStore.options.filter((value: { value: number }) => {
        return [
          9, 10, 11, 12, 13, 15, 19, 20, 22, 23, 28, 29, 44, 48, 49, 51, 53, 54,
        ].includes(value.value)
      })
})

const onSubmit = () => {
  if (transactionStore.current?.id) {
    transactionStore.update(transactionStore.current.id).then(() => {
      transactionStore.current = undefined
    })
  } else {
    transactionStore.store().then(() => {
      transactionStore.current = undefined
    })
  }
}

const uploadFile = () => {
  return new Promise((resolve) => {
    resolve({
      url: `${uploadApi}/upload`,
      headers: [
        {
          name: 'Accept',
          value: 'application/json',
        },
        {
          name: 'X-XSRF-TOKEN',
          value: Cookies.get('XSRF-TOKEN'),
        },
      ],
      withCredentials: true,
      fieldName: 'image',
      formFields: [
        {
          name: 'id',
          value: transactionStore.current?.id,
        },
      ],
    })
  })
}
</script>
