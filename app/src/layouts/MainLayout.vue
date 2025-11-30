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
      :mini="miniState"
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
              v-if="auth.current.role_id === 1"
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
              v-if="auth.current.role_id === 1"
              to="/accounting/supplier"
              active-class="q-item-no-link-highlighting"
            >
              <q-item-section avatar>
                <q-icon name="mdi-storefront-outline" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Supplier</q-item-label>
              </q-item-section>
            </q-item>
            <q-item
              v-if="auth.current.role_id === 1"
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
            <q-item
              to="/accounting/cash"
              active-class="q-item-no-link-highlighting"
            >
              <q-item-section avatar>
                <q-icon name="mdi-cash-multiple" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Cash</q-item-label>
              </q-item-section>
            </q-item>
            <q-expansion-item
              v-if="auth.current.role_id === 1"
              icon="mdi-note"
              label="Report"
            >
              <q-list class="q-pl-lg">
                <q-item
                  to="/accounting/report/balance"
                  active-class="q-item-no-link-highlighting"
                >
                  <q-item-section avatar>
                    <q-icon name="mdi-scale-balance" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>Balance</q-item-label>
                  </q-item-section>
                </q-item>
                <q-item
                  to="/accounting/report/result"
                  active-class="q-item-no-link-highlighting"
                >
                  <q-item-section avatar>
                    <q-icon name="mdi-cash" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>Result</q-item-label>
                  </q-item-section>
                </q-item>
                <q-item
                  to="/accounting/report/ledger"
                  active-class="q-item-no-link-highlighting"
                >
                  <q-item-section avatar>
                    <q-icon name="mdi-book" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>Ledger</q-item-label>
                  </q-item-section>
                </q-item>
                <q-item
                  to="/accounting/report/transactions"
                  active-class="q-item-no-link-highlighting"
                >
                  <q-item-section avatar>
                    <q-icon name="mdi-swap-horizontal" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>Transactions</q-item-label>
                  </q-item-section>
                </q-item>
                <q-item
                  to="/accounting/report/account-transactions"
                  active-class="q-item-no-link-highlighting"
                >
                  <q-item-section avatar>
                    <q-icon name="mdi-swap-horizontal" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>Account Transactions</q-item-label>
                  </q-item-section>
                </q-item>
                <q-item
                  to="/accounting/report/closed-cash"
                  active-class="q-item-no-link-highlighting"
                >
                  <q-item-section avatar>
                    <q-icon name="mdi-money" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>Closed Cash</q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>
            </q-expansion-item>
          </q-list>
        </q-expansion-item>
        <q-expansion-item
          v-if="auth.current.role_id === 1"
          icon="mdi-web"
          label="Global"
        >
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
        <q-expansion-item icon="mdi-cash-register" label="Unicenta">
          <q-list class="q-pl-lg">
            <q-item
              to="/unicenta/reports/statement-of-account"
              active-class="q-item-no-link-highlighting"
            >
              <q-item-section avatar>
                <q-icon name="mdi-receipt-text-outline" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Stament-of-account</q-item-label>
              </q-item-section>
            </q-item>
            <q-expansion-item
              v-if="auth.current.role_id === 1"
              icon="mdi-cart-variant"
              label="Product"
            >
              <q-list class="q-pl-lg">
                <q-item
                  to="/unicenta/reports/cost-income"
                  active-class="q-item-no-link-highlighting"
                >
                  <q-item-section avatar>
                    <q-icon name="mdi-cash" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>Cost / Income</q-item-label>
                  </q-item-section>
                </q-item>
                <q-item
                  to="/unicenta/product"
                  active-class="q-item-no-link-highlighting"
                >
                  <q-item-section avatar>
                    <q-icon name="mdi-cart-variant" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>Bundles</q-item-label>
                  </q-item-section>
                </q-item>
                <q-item
                  to="/unicenta/product/pricebuy"
                  active-class="q-item-no-link-highlighting"
                >
                  <q-item-section avatar>
                    <q-icon name="mdi-currency-usd" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>Cost</q-item-label>
                  </q-item-section>
                </q-item>
                <q-item
                  to="/unicenta/product/cycle-count-sheet"
                  active-class="q-item-no-link-highlighting"
                >
                  <q-item-section avatar>
                    <q-icon name="mdi-tally-mark-5" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>Cycle Count sheet</q-item-label>
                  </q-item-section>
                </q-item>
                <q-item
                  to="/unicenta/product/cycle-count"
                  active-class="q-item-no-link-highlighting"
                >
                  <q-item-section avatar>
                    <q-icon name="mdi-tally-mark-5" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>Cycle Count</q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>
            </q-expansion-item>
            <q-expansion-item
              v-if="auth.current.role_id === 1"
              icon="mdi-cart-variant"
              label="Reports"
            >
              <q-list class="q-pl-lg">
                <q-item
                  to="/unicenta/reports/stock-diary"
                  active-class="q-item-no-link-highlighting"
                >
                  <q-item-section avatar>
                    <q-icon name="mdi-cash" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>Stock diary</q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>
            </q-expansion-item>
          </q-list>
        </q-expansion-item>
        <q-expansion-item
          v-if="auth.current.role_id === 1"
          icon="mdi-account-clock"
          label="Payroll"
        >
          <q-list class="q-pl-lg">
            <q-item
              to="/payroll/employee"
              active-class="q-item-no-link-highlighting"
            >
              <q-item-section avatar>
                <q-icon name="mdi-account" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Employee</q-item-label>
              </q-item-section>
            </q-item>
            <q-item
              to="/payroll/time-record"
              active-class="q-item-no-link-highlighting"
            >
              <q-item-section avatar>
                <q-icon name="mdi-clipboard-text-clock-outline" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Time Record</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-expansion-item>
        <q-expansion-item
          v-if="auth.current.role_id === 1"
          icon="mdi-tools"
          label="Maintenance"
        >
          <q-list class="q-pl-lg">
            <q-item
              to="/maintenance/pool-reading"
              active-class="q-item-no-link-highlighting"
            >
              <q-item-section avatar>
                <q-icon name="mdi-pool" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Pool reading</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-expansion-item>
        <q-expansion-item
          v-if="auth.current.role_id === 1"
          icon="mdi-account"
          label="User"
        >
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
