import { defineStore } from 'pinia'
import { Ref, ref } from 'vue'
import { Notify } from 'quasar'
import { api } from 'boot/axios'

export type SupplierObject = {
  id: number | null
  tin: string | null
  name: string
  address1: string | null
  address2: string | null
  postal_code: string | null
  city: string | null
  province: string | null
  phone_number: string | null
}

export type SupplierErrors = {
  id?: string
  tin?: string
  name?: string
  address1?: string
  address2?: string
  postal_code?: string
  city?: string
  province?: string
  phone_number?: string
}

type SupplierFilter = {
  word?: string
}

export const useAccountingSupplierStore = defineStore(
  'accounting/supplier',
  () => {
    const index = ref()
    const current: Ref<SupplierObject | undefined> = ref()
    const errors: Ref<SupplierErrors | undefined> = ref()
    const filter: Ref<SupplierFilter> = ref({})

    const fetchIndex = async () => {
      const urlParams = new URLSearchParams(
        Object.entries(filter.value).filter((el) => el[1] !== undefined),
      )
      return api
        .get(`accounting/suppliers?${urlParams}`)
        .then((response) => {
          index.value = response.data
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
        .get(`accounting/suppliers/${id}`)
        .then((response) => {
          current.value = response.data
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

    const create = (prefill: SupplierObject = <SupplierObject>{}) => {
      current.value = {
        ...prefill,
      }
    }

    const store = async () => {
      const cash = current.value
      if (!cash) throw new Error('No cash')
      errors.value = undefined
      return api
        .post('accounting/suppliers', cash)
        .then((response) => {
          cash.id = response.data.id
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
        .put(`accounting/suppliers/${id}`, current.value)
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
        .delete(`accounting/suppliers/${id}`)
        .then(() => {
          if (index.value !== null) fetchIndex()
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
  },
)
