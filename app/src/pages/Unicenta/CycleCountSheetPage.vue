<template>
  <q-page>
    <h5>Cycle Count sheet</h5>
    <div class="row">
      <q-select
        label="Category"
        class="row col-md-2 col-12 q-ml-md"
        v-model="category"
        :options="[
          'Bar Stock',
          'Beer',
          'Canned / Bottled',
          'Fruit',
          'Intern products',
          'Kitchen stock',
          'Stock Items',
          'Wine',
        ]"
        dense
        clearable
      />
    </div>
    <div class="row q-mt-xl">
      <q-btn
        :href="
          '/api/unicenta/products/cycle-count-sheet' +
          (category ? `?category=${category}` : '')
        "
        target="_blank"
        label="Download cycle count sheet"
        color="primary"
      />
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const category = ref(null)
</script>
