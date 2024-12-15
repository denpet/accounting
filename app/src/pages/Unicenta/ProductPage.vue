<template>
  <q-page class="row">
    <ErIndex
      v-if="!$q.platform.is.mobile || !productStore.current"
      :class="[
        $q.platform.is.mobile ? 'col-12 q-pa-none' : 'col-4 q-pa-md',
        '',
      ]"
    />
    <ErEdit
      v-if="productStore.current"
      :class="[
        $q.platform.is.mobile ? 'col-12 q-pa-none' : 'col-8 q-pa-md',
        '',
      ]"
    />
  </q-page>
</template>

<script setup lang="ts">
import { useUnicentaProductStore } from 'stores/unicenta/product'
import ErIndex from 'components/Unicenta/Product/ErIndex.vue'
import ErEdit from 'components/Unicenta/Product/ErEdit.vue'

const productStore = useUnicentaProductStore()
</script>
