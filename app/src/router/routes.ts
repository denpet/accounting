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
