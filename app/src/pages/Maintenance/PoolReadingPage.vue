<template>
  <q-page class="row">
    <ErIndex
      v-if="!$q.platform.is.mobile || !poolReadingStore.current"
      :class="[
        $q.platform.is.mobile ? 'col-12 q-pa-none' : 'col-4 q-pa-md',
        '',
      ]"
    />
    <ErEdit
      v-if="poolReadingStore.current"
      :class="[
        $q.platform.is.mobile ? 'col-12 q-pa-none' : 'col-8 q-pa-md',
        '',
      ]"
    />
  </q-page>
</template>

<script setup lang="ts">
import { useMaintenancePoolReadingStore } from 'stores/maintenance/pool-reading'
import ErIndex from 'components/Maintenance/PoolReading/ErIndex.vue'
import ErEdit from 'components/Maintenance/PoolReading/ErEdit.vue'

const poolReadingStore = useMaintenancePoolReadingStore()
</script>
