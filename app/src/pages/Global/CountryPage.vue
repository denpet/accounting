<template>
  <q-page class="row">
    <ErIndex
      v-if="!$q.platform.is.mobile || !countryStore.current"
      :class="[
        $q.platform.is.mobile ? 'col-12 q-pa-none' : 'col-4 q-pa-md',
        '',
      ]"
    />
    <ErEdit
      v-if="countryStore.current"
      :class="[
        $q.platform.is.mobile ? 'col-12 q-pa-none' : 'col-8 q-pa-md',
        '',
      ]"
    />
  </q-page>
</template>

<script setup lang="ts">
import { useGlobalCountryStore } from 'stores/global/country'
import ErIndex from 'components/Global/Country/ErIndex.vue'
import ErEdit from 'components/Global/Country/ErEdit.vue'

const countryStore = useGlobalCountryStore()
</script>
