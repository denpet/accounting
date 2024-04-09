<template>
  <q-layout view="lHh Lpr lFf">
    <q-header
      elevated
      :class="
        $q.dark.isActive ? 'body--dark text-light' : 'body--light text-dark'
      "
    >
      <q-toolbar class="q-gutter-md">
        <q-btn
          flat
          dense
          round
          @click="toggleLeftDrawer"
          icon="menu"
          aria-label="Menu"
        />
        <q-toolbar-title :dark="false">Eden</q-toolbar-title>
        <q-space />
        <q-toggle
          v-model="$q.dark.isActive"
          label="Theme"
          left-label
          push
          glossy
          checked-icon="mdi-brightness-3"
          unchecked-icon="mdi-brightness-5"
          @click="onToggleColorScheme"
        />
        <q-btn
          flat
          :label="auth.current.user_name"
          no-caps
          icon="img:profile.svg"
        />
      </q-toolbar>
    </q-header>

    <q-drawer
      v-model="leftDrawerOpen"
      show-if-above
      bordered
      :minia="miniState"
      @mouseover="miniState = false"
      @mouseout="miniState = true"
      mini-to-overlaya
      behaviora="mobile"
    >
      <q-list>
        <q-item to="/" active-class="q-item-no-link-highlighting">
          <q-item-section avatar>
            <q-icon name="mdi-home" />
          </q-item-section>
          <q-item-section>
            <q-item-label>Home</q-item-label>
          </q-item-section>
        </q-item>
        <q-expansion-item icon="mdi-file-cabinet" label="Accounting">
          <q-list class="q-pl-lg">
            <q-item
              to="/accounting/account"
              active-class="q-item-no-link-highlighting"
            >
              <q-item-section avatar>
                <q-icon name="mdi-store" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Account</q-item-label>
              </q-item-section>
            </q-item>
            <q-item
              to="/accounting/transaction"
              active-class="q-item-no-link-highlighting"
            >
              <q-item-section avatar>
                <q-icon name="mdi-swap-horizontal" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Transaction</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-expansion-item>
        <q-expansion-item icon="mdi-web" label="Global">
          <q-list class="q-pl-lg">
            <q-item
              to="/global/country"
              active-class="q-item-no-link-highlighting"
            >
              <q-item-section avatar>
                <q-icon name="mdi-flag" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Country</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-expansion-item>
        <q-expansion-item icon="mdi-account" label="User">
          <q-list class="q-pl-lg">
            <q-item to="/user/user" active-class="q-item-no-link-highlighting">
              <q-item-section avatar>
                <q-icon name="mdi-account-edit" />
              </q-item-section>
              <q-item-section>
                <q-item-label>User</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-expansion-item>
        <q-item to="/auth/logout" active-class="q-item-no-link-highlighting">
          <q-item-section avatar>
            <q-icon name="mdi-logout" />
          </q-item-section>
          <q-item-section>
            <q-item-label>Logout</q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
    </q-drawer>

    <q-page-container class="q-ma-md">
      <q-ajax-bar size="5px" position="bottom" />
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useQuasar } from 'quasar'
import { useAuthStore } from 'stores/auth'

const auth = useAuthStore()

const $q = useQuasar()

const onToggleColorScheme = () => {
  $q.dark.set($q.dark.isActive) // Needed to trigger full change
  localStorage.setItem('color_scheme', `${$q.dark.isActive}`)
}

const leftDrawerOpen = ref<boolean>(false)
const miniState = ref<boolean>(true)

function toggleLeftDrawer() {
  leftDrawerOpen.value = !leftDrawerOpen.value
}
</script>

<style>
.body--light {
  background: #fff;
}

.body--dark {
  background: #000;
}
</style>
