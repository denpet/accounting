import { defineStore } from 'pinia'
import { Ref, ref } from 'vue'
import { Notify } from 'quasar'
import { api } from 'boot/axios'

export type CustomerObject = {
  id: string | null
  name: string
}

export type CustomerErrors = {
  id?: string
  name?: string
}

type CustomerFilter = {
  name?: string
  visible?: string
}

export const useUnicentaCustomerStore = defineStore('unicenta/customer', () => {
  const index = ref([])
  const current: Ref<CustomerObject | undefined> = ref()
  const errors: Ref<CustomerErrors | undefined> = ref()
  const filter: Ref<CustomerFilter> = ref({})

  const fetchIndex = async () => {
    const urlParams = new URLSearchParams(
      Object.entries(filter.value).filter((el) => el[1] !== undefined),
    )
    return api
      .get(`unicenta/customers?${urlParams}`)
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

  const show = async (id: number) => {
    return api
      .get(`unicenta/customers/${id}`)
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

  const create = (prefill: CustomerObject = <CustomerObject>{}) => {
    current.value = {
      ...{ name: '', email: '' },
      ...prefill,
    }
  }

  const store = async () => {
    if (!current.value) throw new Error('No customer')
    errors.value = undefined
    return api
      .post('unicenta/customers', current.value)
      .then((response) => {
        if (current.value) current.value.id = response.data.id
        fetchIndex()
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
      .put(`unicenta/customers/${id}`, current.value)
      .then(() => {
        fetchIndex()
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
      .delete(`unicenta/customers/${id}`)
      .then(() => {
        fetchIndex()
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
    show,
    create,
    store,
    update,
    destroy,
    index,
    filter,
    current,
    errors,
  }
})
