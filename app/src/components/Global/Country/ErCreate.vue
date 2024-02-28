<template>
  <q-tr v-if="country !== undefined">
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
      <q-input
        v-model="country.country"
        label="Country"
        :error="errors?.country !== undefined"
        :error-message="errors?.country?.toString()"
      />
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
import { useGlobalCountryStore } from 'stores/global/country'
import { computed } from 'vue'

const props = defineProps({
  id: { type: String, default: '' },
})

const countryStore = useGlobalCountryStore()

const country = computed(() => countryStore.created.get(props.id))

const errors = computed(() => countryStore.createdErrors.get(props.id))

const onSubmit = () => {
  countryStore.store(props.id).then(() => {
    countryStore.created.delete(props.id)
    countryStore.createdErrors.delete(props.id)
  })
}

const onCancel = () => {
  countryStore.created.delete(props.id)
  countryStore.createdErrors.delete(props.id)
}
</script>
