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
    const index = ref()
    const options = ref([])
    const current: Ref<AccountObject | undefined> = ref()
    const errors: Ref<AccountErrors | undefined> = ref()
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
          current.value = response.data
          errors.value = undefined
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
      current.value = {
        ...{
          name: '',
          type: '',
        },
        ...prefill,
      }
      return id
    }

    const store = async () => {
      if (!current.value) throw new Error('No account')
      errors.value = undefined
      return api
        .post('accounting/accounts', current.value)
        .then((response) => {
          if (current.value) current.value.id = response.data.id
          fetchIndex()
          fetchOptions()
          Notify.create({
            message: 'Stored',
            type: 'positive',
            position: 'top-right',
            progress: true,
          })
        })
        .catch((response) => {
          errors.value = response.response.data.errors
          throw new Error(response.response.data.message)
        })
    }

    const update = async (id: number) => {
      errors.value = undefined
      return api
        .put(`accounting/accounts/${id}`, current.value)
        .then(() => {
          fetchIndex()
          fetchOptions()
          Notify.create({
            message: 'Updated',
            type: 'positive',
            position: 'top-right',
            progress: true,
          })
        })
        .catch((response) => {
          errors.value = response.response.data.errors
          throw new Error(response.response.data.message)
        })
    }

    const destroy = async (id: number) => {
      return api
        .delete(`accounting/accounts/${id}`)
        .then(() => {
          fetchIndex()
          fetchOptions()
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
      errors,
    }
  },
)
