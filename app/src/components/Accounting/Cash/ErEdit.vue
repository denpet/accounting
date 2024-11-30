<template>
  <q-form
    v-if="cashStore.current"
    @submit="onSubmit"
    @reset="cashStore.current = undefined"
    class="q-gutter-md"
  >
    <q-chip
      v-if="cashStore.current.id"
      :label="cashStore.current.id"
      icon="mdi-identifier"
      color="orange-6"
    />
    <q-chip v-else label="New " icon="mdi-identifier" color="orange-6" />
    <q-input
      filled
      v-model="cashStore.current.date"
      mask="####-##-##"
      :error="cashStore.errors?.date !== undefined"
      :error-message="cashStore.errors?.date?.toString()"
    >
      <template v-slot:append>
        <q-icon name="event" class="cursor-pointer">
          <q-popup-proxy cover transition-show="scale" transition-hide="scale">
            <q-date v-model="cashStore.current.date" mask="YYYY-MM-DD">
              <div class="row items-center justify-end">
                <q-btn v-close-popup label="Close" color="primary" flat />
              </div>
            </q-date>
          </q-popup-proxy>
        </q-icon>
      </template>
    </q-input>
    <q-input
      class="q-pr-xs"
      label="0.01"
      dense
      v-model.number="money1"
      :rules="[
        (val) =>
          Number(val) === val || 'Field is required and must be a number',
      ]"
    />
    <q-input
      class="q-pr-xs"
      label="0.05"
      dense
      v-model.number="money5"
      :rules="[
        (val) =>
          Number(val) === val || 'Field is required and must be a number',
      ]"
    />
    <q-input
      class="q-pr-xs"
      label="0.10"
      dense
      v-model.number="money10"
      :rules="[
        (val) =>
          Number(val) === val || 'Field is required and must be a number',
      ]"
    />
    <q-input
      class="q-pr-xs"
      label="0.25"
      dense
      v-model.number="money25"
      :rules="[
        (val) =>
          Number(val) === val || 'Field is required and must be a number',
      ]"
    />
    <q-input
      class="q-pr-xs"
      label="1"
      dense
      v-model.number="money100"
      :rules="[
        (val) =>
          Number(val) === val || 'Field is required and must be a number',
      ]"
    />
    <q-input
      class="q-pr-xs"
      label="5"
      dense
      v-model.number="money500"
      :rules="[
        (val) =>
          Number(val) === val || 'Field is required and must be a number',
      ]"
    />
    <q-input
      class="q-pr-xs"
      label="10"
      dense
      v-model.number="money1000"
      :rules="[
        (val) =>
          Number(val) === val || 'Field is required and must be a number',
      ]"
    />
    <q-input
      class="q-pr-xs"
      label="20"
      dense
      v-model.number="money2000"
      :rules="[
        (val) =>
          Number(val) === val || 'Field is required and must be a number',
      ]"
    />
    <q-input
      class="q-pr-xs"
      label="50"
      dense
      v-model.number="money5000"
      :rules="[
        (val) =>
          Number(val) === val || 'Field is required and must be a number',
      ]"
    />
    <q-input
      class="q-pr-xs"
      label="100"
      dense
      v-model.number="money10000"
      :rules="[
        (val) =>
          Number(val) === val || 'Field is required and must be a number',
      ]"
    />
    <q-input
      class="q-pr-xs"
      label="200"
      dense
      v-model.number="money20000"
      :rules="[
        (val) =>
          Number(val) === val || 'Field is required and must be a number',
      ]"
    />
    <q-input
      class="q-pr-xs"
      label="500"
      dense
      v-model.number="money50000"
      :rules="[
        (val) =>
          Number(val) === val || 'Field is required and must be a number',
      ]"
    />
    <q-input
      class="q-pr-xs"
      label="1000"
      dense
      v-model.number="money100000"
      :rules="[
        (val) =>
          Number(val) === val || 'Field is required and must be a number',
      ]"
    />
    <q-input
      class="q-pr-xs"
      label="Foreign currency, PHP equivalent"
      dense
      v-model.number="foreign"
      :rules="[
        (val) =>
          Number(val) === val || 'Field is required and must be a number',
      ]"
    />
    <q-input
      class="q-pr-xs"
      label="In safe"
      dense
      v-model.number="cashStore.current.safe"
      :rules="[
        (val) =>
          Number(val) === val || 'Field is required and must be a number',
      ]"
    />
    <q-toolbar>
      <q-btn label="Submit" type="submit" color="positive" />
      <q-space />
      <q-btn label="Cancel" type="reset" color="warning" />
      <q-space />
    </q-toolbar>
  </q-form>
</template>

<script setup lang="ts">
import { useAccountingCashStore, CashObject } from 'stores/accounting/cash'
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

const money1 = ref(null)
const money5 = ref(null)
const money10 = ref(null)
const money25 = ref(null)
const money100 = ref(null)
const money500 = ref(null)
const money1000 = ref(null)
const money2000 = ref(null)
const money5000 = ref(null)
const money10000 = ref(null)
const money20000 = ref(null)
const money50000 = ref(null)
const money100000 = ref(null)
const foreign = ref(null)

const cashStore = useAccountingCashStore()
const router = useRouter()

onMounted(() => {
  cashStore.create(<CashObject>{
    id: null,
    date: new Date().toLocaleDateString('sv'),
    amount: null,
    safe: null,
  })
})

const onSubmit = () => {
  if (cashStore.current) {
    cashStore.current.amount =
      (money1.value ?? 0) * 0.01 +
      (money5.value ?? 0) * 0.05 +
      (money10.value ?? 0) * 0.1 +
      (money25.value ?? 0) * 0.25 +
      (money100.value ?? 0) * 1 +
      (money500.value ?? 0) * 5 +
      (money1000.value ?? 0) * 10 +
      (money2000.value ?? 0) * 20 +
      (money5000.value ?? 0) * 50 +
      (money10000.value ?? 0) * 100 +
      (money20000.value ?? 0) * 200 +
      (money50000.value ?? 0) * 500 +
      (money100000.value ?? 0) * 1000 +
      (foreign.value ?? 0)
    if (cashStore.current?.id) {
      cashStore.update(cashStore.current.id).then(() => {
        cashStore.current = undefined
      })
    } else {
      cashStore.store().then(() => {
        cashStore.current = undefined
        router.push('/')
      })
    }
  }
}
</script>
