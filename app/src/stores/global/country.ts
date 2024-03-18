import { defineStore } from 'pinia'
import { Ref, ref } from 'vue'
import { Notify } from 'quasar'
import { api } from 'boot/axios'

export type CountryObject = {
  id: number
  code: string
  name: string
}

export type CountryErrors = {
  country?: string
  name?: string
}

type CountryFilter = {
  country?: string
  name?: string
}

export const useGlobalCountryStore = defineStore('global/country', () => {
  const index = ref(null)
  const options = ref(null)
  const filter: Ref<CountryFilter> = ref({})
  const current: Ref<Map<number, CountryObject>> = ref(new Map())
  const currentErrors: Ref<Map<number, CountryErrors>> = ref(new Map())
  const created: Ref<Map<string, CountryObject>> = ref(new Map())
  const createdErrors: Ref<Map<string, CountryErrors>> = ref(new Map())

  const fetchIndex = async (force = false) => {
    if (force || index.value === null) {
      const urlParams = new URLSearchParams(
        Object.entries(filter.value).filter((el) => el[1] !== undefined),
      )
      return api
        .get(`global/countries?${urlParams}`)
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
        .get(`global/countries/options?${urlParams}`)
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

  const show = async (id: number) => {
    return api
      .get(`global/countries/${id}`)
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

  const create = (prefill: CountryObject = <CountryObject>{}) => {
    const id = self.crypto.randomUUID()
    created.value.set(id, {
      ...{
        country: '',
        name: '',
      },
      ...prefill,
    })
    return id
  }

  const store = async (id: string) => {
    const country = created.value.get(id)
    if (!country) throw new Error('No country')
    return api
      .post('global/countries', country)
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

  const update = async (id: number) => {
    return api
      .put(`global/countries/${id}`, current.value.get(id))
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
        console.log('response')
        currentErrors.value.set(id, response.response.data.errors)
        throw new Error(response.response.data.message)
      })
  }

  const destroy = async (id: number) => {
    return api
      .delete(`global/countries/${id}`)
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
