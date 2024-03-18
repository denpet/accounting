<template>
  <template v-if="country !== undefined">
    <q-td>
      {{ country.code }}
      <q-chip :label="id" icon="mdi-identifier" />
    </q-td>
    <q-td>
      <q-input
        v-model="country.name"
        label="Name"
        :error="errors?.name !== undefined"
        :error-message="errors?.name?.toString()"
      />
    </q-td>
    <q-td class="q-mt-md">
      <q-btn color="negative" icon="mdi-delete" @click="onDestroy" round flat />
      <q-btn
        color="positive"
        icon="mdi-content-save"
        @click="onSubmit"
        round
        flat
      />
    </q-td>
  </template>
</template>

<script setup lang="ts">
import { useGlobalCountryStore } from 'stores/global/country'
import { useQuasar } from 'quasar'
import { computed, onMounted, onUnmounted } from 'vue'

const $q = useQuasar()

const emit = defineEmits(['changed'])

const props = defineProps({
  id: { type: Number, default: null },
})

const countryStore = useGlobalCountryStore()

onMounted(() => countryStore.show(props.id))

onUnmounted(() => {
  countryStore.current.delete(props.id)
  countryStore.currentErrors.delete(props.id)
})

const country = computed(() => countryStore.current.get(props.id))

const errors = computed(() => countryStore.currentErrors.get(props.id))

const onSubmit = () => {
  countryStore.update(props.id).then(() => emit('changed'))
}

const onDestroy = () => {
  $q.dialog({
    title: 'Confirm',
    message: 'Are you sure you want to delete the row?',
    cancel: true,
    persistent: true,
  }).onOk(() => {
    countryStore.destroy(props.id).then(() => {
      emit('changed', null)
    })
  })
}
</script>
