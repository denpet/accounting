<template>
  <template v-if="country !== undefined">
    <q-td>
      <q-chip :label="countryId" icon="mdi-identifier" />
    </q-td>
    <q-td>
      <q-input
        v-model="country.country_name"
        label="Name"
        :error="errors?.country_name !== undefined"
        :error-message="errors?.country_name?.toString()"
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
  countryId: { type: String, default: null },
})

const countryStore = useGlobalCountryStore()

onMounted(() => countryStore.show(props.countryId))

onUnmounted(() => {
  countryStore.current.delete(props.countryId)
  countryStore.currentErrors.delete(props.countryId)
})

const country = computed(() => countryStore.current.get(props.countryId))

const errors = computed(() => countryStore.currentErrors.get(props.countryId))

const onSubmit = () => {
  countryStore.update(props.countryId).then(() => emit('changed'))
}

const onDestroy = () => {
  $q.dialog({
    title: 'Confirm',
    message: 'Are you sure you want to delete the row?',
    cancel: true,
    persistent: true,
  }).onOk(() => {
    countryStore.destroy(props.countryId).then(() => {
      emit('changed', null)
    })
  })
}
</script>
