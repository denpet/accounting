<template>
  <q-form v-if="transaction !== undefined" @submit.prevent="onSubmit">
    <q-card class="row">
      <q-card-section class="col-12">
        <q-chip :label="id" icon="mdi-identifier" color="orange-6" />
      </q-card-section>
      <q-card-section class="col-12">
        <q-date
          v-model="transaction.date"
          label="Date"
          :error="errors?.date !== undefined"
          :error-message="errors?.date?.toString()"
        />
      </q-card-section>
      <q-card-section class="col-12">
        <q-select
          v-model="transaction.from_account_id"
          label="Parent"
          style="width: 100%; max-width: 40vh"
          clearable
          :options="accountStore.options ?? []"
          map-options
          emit-value
          :error="errors?.from_account_id !== undefined"
          :error-message="errors?.from_account_id?.toString()"
        />
      </q-card-section>
      <q-card-actions class="col-12">
        <q-btn
          label="Save"
          type="submit"
          color="positive"
          icon="mdi-content-save"
        />
        <q-space />
        <q-btn
          label="Delete"
          color="negative"
          icon="mdi-delete"
          @click="onDestroy"
        />
      </q-card-actions>
    </q-card>
  </q-form>
</template>

<script setup lang="ts">
import { useAccountingTransactionStore } from 'stores/accounting/transaction'
import { useAccountingAccountStore } from 'stores/accounting/account'
import { useQuasar } from 'quasar'
import { computed, onMounted, onUnmounted } from 'vue'

const $q = useQuasar()

const emit = defineEmits(['changed'])

const props = defineProps({
  id: { type: Number, default: null },
})

const transactionStore = useAccountingTransactionStore()
const accountStore = useAccountingAccountStore()
accountStore.fetchOptions()

onMounted(() => transactionStore.show(props.id))

onUnmounted(() => {
  transactionStore.current.delete(props.id)
  transactionStore.currentErrors.delete(props.id)
})

const transaction = computed(() => transactionStore.current.get(props.id))

const errors = computed(() => transactionStore.currentErrors.get(props.id))

const onSubmit = () => {
  transactionStore.update(props.id).then(() => emit('changed'))
}

const onDestroy = () => {
  $q.dialog({
    title: 'Confirm',
    message: 'Are you sure you want to delete the row?',
    cancel: true,
    persistent: true,
  }).onOk(() => {
    transactionStore.destroy(props.id).then(() => {
      emit('changed', null)
    })
  })
}
</script>
