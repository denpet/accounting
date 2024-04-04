import { defineStore } from 'pinia'
import { Ref, ref } from 'vue'
import { Notify } from 'quasar'
import { api } from 'boot/axios'

export type AccountObject = {
  id: number | null
  name: string
  type: string
}

export type AccountErrors = {
  id?: string
  name?: string
  type?: string
}

type AccountFilter = {
  name?: string
  type?: string
}

export const useAccountingAccountStore = defineStore(
  'accounting/account',
  () => {
    const index = ref([])
    const options = ref([])
    const current: Ref<Map<number, AccountObject>> = ref(new Map())
    const currentErrors: Ref<Map<number, AccountErrors>> = ref(new Map())
    const created: Ref<Map<string, AccountObject>> = ref(new Map())
    const createdErrors: Ref<Map<string, AccountErrors>> = ref(new Map())
    const filter: Ref<AccountFilter> = ref({})

    const fetchIndex = async () => {
      const urlParams = new URLSearchParams(
        Object.entries(filter.value).filter((el) => el[1] !== undefined),
      )
      return api
        .get(`accounting/accounts?${urlParams}`)
        .then((response) => {
          index.value = response.data.data
        })
        .catch((error) => {
          Notify.create({
            message: `Error reading. ${error.response?.data}`,
            type: 'negative',
            position: 'top-right',
            progress: true,
          })
        })
    }

    const fetchOptions = async () => {
      const urlParams = new URLSearchParams(
        Object.entries(filter.value).filter((el) => el[1] !== undefined),
      )
      return api
        .get(`accounting/accounts/options?${urlParams}`)
        .then((response) => {
          options.value = response.data
        })
        .catch((error) => {
          Notify.create({
            message: `Error reading. ${error.response?.data}`,
            type: 'negative',
            position: 'top-right',
            progress: true,
          })
        })
    }

    const show = async (id: number) => {
      return api
        .get(`accounting/accounts/${id}`)
        .then((response) => {
          current.value.set(id, response.data)
        })
        .catch((error) => {
          Notify.create({
            message: `Error reading. ${error.response?.data}`,
            type: 'negative',
            position: 'top-right',
            progress: true,
          })
        })
    }

    const create = (prefill: AccountObject = <AccountObject>{}) => {
      const id = self.crypto.randomUUID()
      created.value.set(id, {
        ...{
          name: '',
          type: '',
        },
        ...prefill,
      })
      return id
    }

    const store = async (id: string) => {
      const account = created.value.get(id)
      if (!account) throw new Error('No account')
      return api
        .post('accounting/accounts', account)
        .then((response) => {
          account.id = response.data.id
          if (index.value !== null) fetchIndex()
          if (options.value !== null) fetchOptions()
          Notify.create({
            message: 'Stored',
            type: 'positive',
            position: 'top-right',
            progress: true,
          })
        })
        .catch((response) => {
          createdErrors.value.set(id, response.response.data.errors)
          throw new Error(response.response.data.message)
        })
    }

    const update = async (id: number) => {
      return api
        .put(`accounting/accounts/${id}`, current.value.get(id))
        .then(() => {
          if (index.value !== null) fetchIndex()
          if (options.value !== null) fetchOptions()
          Notify.create({
            message: 'Updated',
            type: 'positive',
            position: 'top-right',
            progress: true,
          })
        })
        .catch((response) => {
          currentErrors.value.set(id, response.response.data.errors)
          throw new Error(response.response.data.message)
        })
    }

    const destroy = async (id: number) => {
      return api
        .delete(`accounting/accounts/${id}`)
        .then(() => {
          if (index.value !== null) fetchIndex()
          if (options.value !== null) fetchOptions()
          Notify.create({
            message: 'Updated',
            type: 'positive',
            position: 'top-right',
            progress: true,
          })
        })
        .catch((error) => {
          Notify.create({
            message: `Error deleting. ${error.response?.data}`,
            type: 'negative',
            position: 'top-right',
            progress: true,
          })
          throw new Error(error.response.data.message)
        })
    }

    return {
      fetchIndex,
      fetchOptions,
      show,
      create,
      store,
      update,
      destroy,
      index,
      filter,
      options,
      current,
      currentErrors,
      created,
      createdErrors,
    }
  },
)
