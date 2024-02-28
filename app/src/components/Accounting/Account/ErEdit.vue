<template>
  <q-form v-if="account !== undefined" @submit.prevent="onSubmit">
    <q-card class="row">
      <q-card-section class="col-12">
        <q-chip :label="id" icon="mdi-identifier" color="orange-6" />
      </q-card-section>
      <q-card-section class="col-12">
        <q-input
          v-model="account.name"
          label="Name"
          :error="errors?.name !== undefined"
          :error-message="errors?.name?.toString()"
        />
      </q-card-section>
      <q-card-section class="col-12">
        <q-select
          v-model="account.type"
          label="Parent"
          style="width: 100%; max-width: 40vh"
          clearable
          :options="[
            { value: 'A', label: 'Asset' },
            { value: 'L', label: 'Liability' },
            { value: 'E', label: 'Expenses' },
            { value: 'I', label: 'Income' },
            { value: 'C', label: 'Costs?' },
          ]"
          map-options
          emit-value
          :error="errors?.type !== undefined"
          :error-message="errors?.type?.toString()"
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
import { useAccountingAccountStore } from 'stores/accounting/account'
import { useQuasar } from 'quasar'
import { computed, onMounted, onUnmounted } from 'vue'

const $q = useQuasar()

const emit = defineEmits(['changed'])

const props = defineProps({
  id: { type: Number, default: null },
})

const accountStore = useAccountingAccountStore()

onMounted(() => accountStore.show(props.id))

onUnmounted(() => {
  accountStore.current.delete(props.id)
  accountStore.currentErrors.delete(props.id)
})

const account = computed(() => accountStore.current.get(props.id))

const errors = computed(() => accountStore.currentErrors.get(props.id))

const onSubmit = () => {
  accountStore.update(props.id).then(() => emit('changed'))
}

const onDestroy = () => {
  $q.dialog({
    title: 'Confirm',
    message: 'Are you sure you want to delete the row?',
    cancel: true,
    persistent: true,
  }).onOk(() => {
    accountStore.destroy(props.id).then(() => {
      emit('changed', null)
    })
  })
}
</script>
