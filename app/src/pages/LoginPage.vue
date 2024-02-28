<template>
  <q-layout>
    <q-page-container>
      <q-page class="flex flex-center">
        <q-card
          :style="
            $q.screen.lt.sm
              ? { width: '80%' }
              : { width: '30%', maxWidth: '400px' }
          "
        >
          <q-card-section>
            <q-avatar size="103px" class="absolute-center shadow-10">
              <img src="profile.svg" />
            </q-avatar>
          </q-card-section>
          <q-card-section>
            <div class="text-center q-pt-lg">
              <div class="col text-h6 ellipsis">Login to Eden</div>
            </div>
          </q-card-section>
          <q-card-section>
            <q-form class="q-gutter-md" @submit="onLogin">
              <q-input
                filled
                v-model="auth.current.email"
                label="E-mail"
                lazy-rules
                :error="auth.errors.email !== undefined"
                :error-message="auth.errors.email"
                autocomplete="username"
              />

              <q-input
                type="password"
                filled
                v-model="auth.current.password"
                label="Password"
                lazy-rules
                autocomplete="current-password"
                :error="auth.errors.password !== undefined"
                :error-message="auth.errors.password"
              />

              <div>
                <q-btn label="Login" type="submit" color="primary" />
              </div>
            </q-form>
          </q-card-section>
        </q-card>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { useAuthStore } from 'src/stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()

const router = useRouter()

const onLogin = () => {
  auth.login().then(() => {
    router.push('/')
  })
}
</script>
