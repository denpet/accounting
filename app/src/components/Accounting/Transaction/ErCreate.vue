<template>
  <q-tr v-if="account !== undefined">
    <q-td>
      <q-btn
        color="accent"
        size="sm"
        round
        dense
        icon="mdi-arrow-down"
        @click="onCancel"
      />
    </q-td>
    <q-td>
      <q-chip label="New " icon="mdi-identifier" color="orange-6" />
    </q-td>
    <q-td>
      <q-input
        v-model="account.name"
        label="Name"
        :error="errors?.name !== undefined"
        :error-message="errors?.name?.toString()"
      />
    </q-td>
    <q-td>
      <q-select
        v-model="account.type"
        label="Parent"
        :options="[
          { value: 'A', label: 'Asset' },
          { value: 'L', label: 'Liability' },
          { value: 'E', label: 'Expenses' },
          { value: 'I', label: 'Income' },
          { value: 'C', label: 'Costs?' },
        ]"
        map-options
        emit-value
        style="width: 100%; max-width: 40vh"
        clearable
        :error="errors?.type !== undefined"
        :error-message="errors?.type?.toString()"
      />
    </q-td>
    <q-td>
      <q-btn
        color="positive"
        icon="mdi-content-save"
        size="sm"
        round
        flat
        @click="onSubmit"
      />
    </q-td>
  </q-tr>
</template>

<script setup lang="ts">
import { useAccountingAccountStore } from 'stores/accounting/transaction'
import { computed } from 'vue'

const props = defineProps({
  id: { type: String, default: '' },
})

const accountStore = useAccountingAccountStore()

const account = computed(() => accountStore.created.get(props.id))

const errors = computed(() => accountStore.createdErrors.get(props.id))

const onSubmit = () => {
  accountStore.store(props.id).then(() => {
    accountStore.created.delete(props.id)
    accountStore.createdErrors.delete(props.id)
  })
}

const onCancel = () => {
  accountStore.created.delete(props.id)
  accountStore.createdErrors.delete(props.id)
}
</script>
