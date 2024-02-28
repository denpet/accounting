import { defineStore } from 'pinia'
import { Ref, ref } from 'vue'
import { Notify } from 'quasar'
import { api } from 'boot/axios'

export type CurrencyObject = {
  currency: string
}

export type CurrencyErrors = {
  currency?: string
}

type CurrencyFilter = {
  currency?: string
}

export const useGlobalCurrencyStore = defineStore('global/currency', () => {
  const index = ref(null)
  const options = ref(null)
  const filter: Ref<CurrencyFilter> = ref({})
  const current: Ref<Map<string, CurrencyObject>> = ref(new Map())
  const currentErrors: Ref<Map<string, CurrencyErrors>> = ref(new Map())
  const created: Ref<Map<string, CurrencyObject>> = ref(new Map())
  const createdErrors: Ref<Map<string, CurrencyErrors>> = ref(new Map())

  const fetchIndex = async (force = false) => {
    if (force || index.value === null) {
      const urlParams = new URLSearchParams(
        Object.entries(filter.value).filter((el) => el[1] !== undefined),
      )
      return api
        .get(`global/currencies?${urlParams}`)
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
  }

  const fetchOptions = async (force = false) => {
    if (force || options.value === null) {
      const urlParams = new URLSearchParams(
        Object.entries(filter.value).filter((el) => el[1] !== undefined),
      )
      return api
        .get(`global/currencies/options?${urlParams}`)
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
  }

  const show = async (id: string) => {
    return api
      .get(`global/currencies/${id}`)
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

  const create = (prefill: CurrencyObject = <CurrencyObject>{}) => {
    const id = self.crypto.randomUUID()
    created.value.set(id, {
      ...{
        currency: '',
      },
      ...prefill,
    })
    return id
  }

  const store = async (id: string) => {
    const currency = created.value.get(id)
    if (!currency) throw new Error('No country')
    return api
      .post('global/currencies', currency)
      .then(() => {
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

  const update = async (id: string) => {
    return api
      .put(`global/currencies/${id}`, current.value.get(id))
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

  const destroy = async (id: string) => {
    return api
      .delete(`global/currencies/${id}`)
      .then(() => {
        if (index.value !== null) fetchIndex()
        if (options.value !== null) fetchOptions()
        current.value.delete(id)
        Notify.create({
          message: 'Deleted',
          type: 'positive',
          position: 'top-right',
          progress: true,
        })
      })
      .catch((response) => {
        Notify.create({
          message: `Error deleting row. ${response.response?.data}`,
          type: 'negative',
          position: 'top-right',
          progress: true,
        })
        throw new Error(response.response.data.message)
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
})
