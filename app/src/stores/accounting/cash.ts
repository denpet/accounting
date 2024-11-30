import { defineStore } from 'pinia'
import { Ref, ref } from 'vue'
import { Notify } from 'quasar'
import { api } from 'boot/axios'

export type CashObject = {
  id: number | null
  datetime: string
  date: string
  amount: number | null
  safe: number | null
  emergency: number | null
}

export type CashErrors = {
  id?: string
  date?: string
  amount?: string
  safe?: string
}

type CashFilter = {
  from_date?: string
  to_date?: string
}

export const useAccountingCashStore = defineStore('accounting/cash', () => {
  const index = ref()
  const current: Ref<CashObject | undefined> = ref()
  const errors: Ref<CashErrors | undefined> = ref()
  const filter: Ref<CashFilter> = ref({})

  const fetchIndex = async () => {
    const urlParams = new URLSearchParams(
      Object.entries(filter.value).filter((el) => el[1] !== undefined),
    )
    return api
      .get(`accounting/cash?${urlParams}`)
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
      .get(`accounting/cash/${id}`)
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

  const create = (prefill: CashObject = <CashObject>{}) => {
    current.value = {
      ...prefill,
    }
  }

  const store = async () => {
    const cash = current.value
    if (!cash) throw new Error('No cash')
    errors.value = undefined
    return api
      .post('accounting/cash', cash)
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
      .put(`accounting/cash/${id}`, current.value)
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
      .delete(`accounting/cash/${id}`)
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
})
