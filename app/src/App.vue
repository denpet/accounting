<template>
  <router-view />
</template>

<script setup lang="ts">
import { useAuthStore } from 'src/stores/auth'
import { useQuasar } from 'quasar'

/* Get current user, unless we are on the login page. auth store will redirect to /auth/login in case user isn't logged in */
if (window.location.pathname !== '/auth/login') useAuthStore().user()
const $q = useQuasar()
$q.dark.set(localStorage.getItem('color_scheme') == 'true' ? true : false)
</script>
