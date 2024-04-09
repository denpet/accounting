<template>
  <q-page class="row">
    <ErIndex
      :class="[
        $q.platform.is.mobile ? 'col-12 q-pa-none' : 'col-4 q-pa-md',
        '',
      ]"
    />
  </q-page>
</template>

<script setup lang="ts">
import ErIndex from 'components/Unicenta/Customer/ErIndex.vue'
</script>
