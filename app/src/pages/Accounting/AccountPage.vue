<template>
  <q-page class="row">
    <ErIndex
      v-if="!$q.platform.is.mobile || !accountStore.current"
      :class="[
        $q.platform.is.mobile ? 'col-12 q-pa-none' : 'col-4 q-pa-md',
        '',
      ]"
    />
    <ErEdit
      v-if="accountStore.current"
      :class="[
        $q.platform.is.mobile ? 'col-12 q-pa-none' : 'col-8 q-pa-md',
        '',
      ]"
    />
  </q-page>
</template>

<script setup lang="ts">
import { useAccountingAccountStore } from 'stores/accounting/account'
import ErIndex from 'components/Accounting/Account/ErIndex.vue'
import ErEdit from 'components/Accounting/Account/ErEdit.vue'

const accountStore = useAccountingAccountStore()
</script>
