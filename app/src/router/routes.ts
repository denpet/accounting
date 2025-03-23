import { RouteRecordRaw } from 'vue-router'

const routes: RouteRecordRaw[] = [
  {
    path: '/auth',
    component: () => import('layouts/LoginLayout.vue'),
    children: [
      {
        path: 'login',
        meta: { requiresAuth: false },
        component: () => import('pages/LoginPage.vue'),
      },
      {
        path: 'logout',
        meta: { requiresAuth: false },
        component: () => import('pages/LogoutPage.vue'),
      },
    ],
  },
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/IndexPage.vue') },
      {
        path: 'global',
        children: [
          {
            path: 'country',
            component: () => import('pages/Global/CountryPage.vue'),
          },
        ],
      },
      {
        path: 'accounting',
        children: [
          {
            path: 'account',
            component: () => import('pages/Accounting/AccountPage.vue'),
          },
          {
            path: 'supplier',
            component: () => import('pages/Accounting/SupplierPage.vue'),
          },
          {
            path: 'transaction',
            component: () => import('pages/Accounting/TransactionPage.vue'),
          },
          {
            path: 'cash',
            component: () => import('pages/Accounting/CashPage.vue'),
          },
          {
            path: 'report',
            children: [
              {
                path: 'balance',
                component: () =>
                  import('pages/Accounting/Report/BalancePage.vue'),
              },
              {
                path: 'result',
                component: () =>
                  import('pages/Accounting/Report/ResultPage.vue'),
              },
              {
                path: 'ledger',
                component: () =>
                  import('pages/Accounting/Report/LedgerPage.vue'),
              },
              {
                path: 'transactions',
                component: () =>
                  import('pages/Accounting/Report/TransactionPage.vue'),
              },
              {
                path: 'account-transactions',
                component: () =>
                  import('pages/Accounting/Report/AccountTransactionPage.vue'),
              },
              {
                path: 'closed-cash',
                component: () =>
                  import('pages/Accounting/Report/ClosedCashPage.vue'),
              },
            ],
          },
        ],
      },
      {
        path: 'user',
        children: [
          {
            path: 'user',
            component: () => import('pages/User/UserPage.vue'),
          },
        ],
      },
      {
        path: 'payroll',
        children: [
          {
            path: 'employee',
            component: () => import('pages/Payroll/EmployeePage.vue'),
          },
          {
            path: 'time-record',
            component: () => import('pages/Payroll/TimeRecordPage.vue'),
          },
        ],
      },
      {
        path: 'unicenta',
        children: [
          {
            path: 'reports',
            children: [
              {
                path: 'statement-of-account',
                component: () =>
                  import('pages/Unicenta/Report/StatementOfAccountPage.vue'),
              },
              {
                path: 'cost-income',
                component: () =>
                  import('pages/Unicenta/Report/CostIncomePage.vue'),
              },
              {
                path: 'stock-diary',
                component: () =>
                  import('pages/Unicenta/Report/StockDiaryPage.vue'),
              },
            ],
          },
          {
            path: 'product',
            children: [
              {
                path: '',
                component: () => import('pages/Unicenta/ProductPage.vue'),
              },
              {
                path: 'pricebuy',
                component: () => import('pages/Unicenta/PricebuyPage.vue'),
              },
              {
                path: 'purchase',
                component: () => import('pages/Unicenta/PurchasePage.vue'),
              },
              {
                path: 'cycle-count',
                component: () => import('pages/Unicenta/CycleCountPage.vue'),
              },
            ],
          },
        ],
      },
    ],
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/ErrorNotFound.vue') },
    ],
  },
]

export default routes
