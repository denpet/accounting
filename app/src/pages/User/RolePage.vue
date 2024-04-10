<template>
  <q-page class="row">
    <ErIndex
      v-if="!$q.platform.is.mobile || !userStore.current"
      :class="[
        $q.platform.is.mobile ? 'col-12 q-pa-none' : 'col-4 q-pa-md',
        '',
      ]"
    />
    <ErEdit
      v-if="userStore.current"
      :class="[
        $q.platform.is.mobile ? 'col-12 q-pa-none' : 'col-8 q-pa-md',
        '',
      ]"
    />
  </q-page>
</template>

<script setup lang="ts">
import { useUserUserStore } from 'stores/user/user'
import ErIndex from 'components/User/User/ErIndex.vue'
import ErEdit from 'components/User/User/ErEdit.vue'

const userStore = useUserUserStore()
</script>
