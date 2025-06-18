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
            <q-date v-model="transactionStore.current.date" mask="YYYY-MM-DD">
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
        dense
      >
        <template #option="scope">
          <q-item v-bind="scope.itemProps">
            <q-item-section avatar>
              <q-item-label>{{ scope.opt.type }}</q-item-label>
            </q-item-section>
            <q-item-section>
              <q-item-label>{{ scope.opt.name }}</q-item-label>
            </q-item-section>
          </q-item>
        </template>
        <template #selected-item="value">
          <q-item>
            <q-item-section>
              <q-item-label>{{ value.opt.type }}</q-item-label>
            </q-item-section>
            <q-item-section>
              <q-item-label>{{ value.opt.name }}</q-item-label>
            </q-item-section>
          </q-item>
        </template>
      </q-select>
      <q-select
        label="To"
        class="col-6"
        v-model="transactionStore.current.to_account_id"
        :options="toAccountOptions"
        map-options
        emit-value
        :error="transactionStore.errors?.to_account_id !== undefined"
        :error-message="transactionStore.errors?.to_account_id?.toString()"
        dense
      >
        <template #option="scope">
          <q-item v-bind="scope.itemProps">
            <q-item-section avatar>
              <q-item-label>{{ scope.opt.type }}</q-item-label>
            </q-item-section>
            <q-item-section>
              <q-item-label>{{ scope.opt.name }}</q-item-label>
            </q-item-section>
          </q-item>
        </template>
        <template #selected-item="value">
          <q-item>
            <q-item-section>
              <q-item-label>{{ value.opt.type }}</q-item-label>
            </q-item-section>
            <q-item-section>
              <q-item-label>{{ value.opt.name }}</q-item-label>
            </q-item-section>
          </q-item>
        </template>
      </q-select>
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
        @keypress.space="onComputeVat"
      />
    </div>
    <q-item v-if="transactionStore.current.supplier_id !== null">
      <q-item-section side>
        {{ format.tin(transactionStore.current.supplier.tin) }}
      </q-item-section>
      <q-item-section>
        {{ transactionStore.current.supplier.name }}
      </q-item-section>
      <q-btn
        icon="mdi-close"
        flat
        @click="transactionStore.current.supplier_id = null"
      />
    </q-item>
    <ErSupplierEdit v-else-if="supplierStore.current" />
    <template v-else>
      <q-input
        v-model="supplierStore.filter.word"
        class="col-2"
        label="Supplier"
        :error="transactionStore.errors?.supplier_id !== undefined"
        :error-message="transactionStore.errors?.supplier_id?.toString()"
        @update:model-value="onSupplierFilter"
        debounce="500"
      />
      <q-btn
        @click.stop="onCreateSupplier"
        icon="mdi-plus"
        label="Supplier"
        color="secondary"
        size="sm"
        rounded
        dense
        align="left"
        style="width: 6rem"
        class="q-ml-md"
      />
      <q-table
        ref="table"
        class="ellipsis"
        :columns="columns"
        :rows="supplierStore.index ?? []"
        row-key="id"
        :rows-per-page-options="[0]"
        dense
        @row-click="onSetSupplierId"
        hide-bottom
      />
    </template>
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
import { useAccountingSupplierStore } from 'stores/accounting/supplier'
import { useAccountingAccountStore } from 'stores/accounting/account'
import { computed, onMounted } from 'vue'
import { Cookies, QTableColumn } from 'quasar'
import { useAuthStore } from 'src/stores/auth'
import { format } from 'boot/format'
import ErSupplierEdit from 'components/Accounting/Supplier/ErEdit.vue'

const uploadApi = process.env.API + '/api/accounting/transactions'
const transactionStore = useAccountingTransactionStore()
const supplierStore = useAccountingSupplierStore()
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
  supplierStore.index = null
  supplierStore.filter = { word: '' }
})

const fromAccountOptions = computed(() => {
  return auth.current.id == 1
    ? accountStore.options
    : accountStore.options.filter((value: { value: number }) => {
        return [
          1,
          3,
          62,
          transactionStore.current?.from_account_id ?? 0,
        ].includes(value.value)
      })
})

const toAccountOptions = computed(() => {
  return auth.current.id == 1
    ? accountStore.options
    : accountStore.options.filter((value: { value: number }) => {
        return [
          1,
          9,
          10,
          11,
          12,
          13,
          15,
          19,
          20,
          22,
          23,
          28,
          29,
          44,
          48,
          49,
          51,
          53,
          54,
          62,
          transactionStore.current?.to_account_id ?? 0,
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

const onSupplierFilter = () => {
  if (supplierStore.filter.word && supplierStore.filter.word.length > 1) {
    supplierStore.fetchIndex()
  } else {
    supplierStore.index = null
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

const onSetSupplierId = (
  event: object,
  row: { id: number; tin: string; name: string },
) => {
  if (transactionStore.current) {
    transactionStore.current.supplier_id = row.id
    transactionStore.current.supplier = row
  }
}

const onCreateSupplier = () => {
  supplierStore.create({
    id: null,
    tin: isNaN(parseInt(supplierStore.filter.word ?? ''))
      ? null
      : supplierStore.filter.word ?? null,
    name: isNaN(parseInt(supplierStore.filter.word ?? ''))
      ? supplierStore.filter.word ?? ''
      : '',
    address1: null,
    address2: null,
    postal_code: null,
    city: null,
    province: null,
    phone_number: null,
  })
}

const onComputeVat = () => {
  if (transactionStore.current && transactionStore.current.amount) {
    transactionStore.current.vat =
      Math.round(100 * transactionStore.current.amount * (1 - 1 / 1.12)) / 100
  }
}
const columns: QTableColumn[] = [
  {
    name: 'tin',
    label: 'Tin',
    field: 'tin',
    align: 'left',
    format: (val) => format.tin(val),
    sortable: true,
  },
  {
    name: 'name',
    label: 'Name',
    field: 'name',
    align: 'left',
    sortable: true,
  },
]
</script>
