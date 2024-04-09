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
  const index = ref()
  const options = ref()
  const filter: Ref<CountryFilter> = ref({})
  const current: Ref<CountryObject | undefined> = ref()
  const errors: Ref<CountryErrors | undefined> = ref()

  const fetchIndex = async () => {
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

  const fetchOptions = async () => {
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

  const show = async (id: number) => {
    return api
      .get(`global/countries/${id}`)
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

  const create = (prefill: CountryObject = <CountryObject>{}) => {
    const id = self.crypto.randomUUID()
    current.value = {
      ...{
        country: '',
        name: '',
      },
      ...prefill,
    }
    return id
  }

  const store = async () => {
    if (!current.value) throw new Error('No country')
    errors.value = undefined
    return api
      .post('global/countries', current.value)
      .then(() => {
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
      .put(`global/countries/${id}`, current.value)
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
      .delete(`global/countries/${id}`)
      .then(() => {
        fetchIndex()
        fetchOptions()
        current.value = undefined
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
    errors,
  }
})
