<template>
  <q-layout>
    <q-page-container>
      <q-page class="flex flex-center">
        <q-card :style="$q.screen.lt.sm ? { width: '80%' } : { width: '30%', maxWidth: '400px' }">
          <q-card-section>
            <q-avatar size="103px" class="absolute-center shadow-10">
              <img src="profile.svg" />
            </q-avatar>
          </q-card-section>
          <q-card-section>
            <div class="text-center q-pt-lg">
              <div class="col text-h6 ellipsis">Welcome back!</div>
            </div>
          </q-card-section>
        </q-card>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { useAuthStore } from 'src/stores/auth'
import { useRouter } from 'vue-router'

const router = useRouter()
const auth = useAuthStore()

auth.logout()
setTimeout(() => {
  router.push('/auth/login')
}, 3000)
</script>
