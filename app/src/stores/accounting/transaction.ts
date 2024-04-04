import { defineStore } from 'pinia'
import { Ref, ref } from 'vue'
import { Notify } from 'quasar'
import { api } from 'boot/axios'

export type TransactionObject = {
  id: number | null
  date: string
  from_account_id: number
  to_account_id: number
  note: string
  amount: number | null
  vat: number | null
  tin: string | null
  official_receipt: string | null
}

export type TransactionErrors = {
  message?: string
  id?: string
  date?: string
  from_account_id?: string
  to_account_id?: string
  note?: string
  amount?: string
  vat?: string
  tin?: string
  official_receipt?: string
}

type TransactionFilter = {
  date?: string
  from_account_id?: string
  to_account_id?: string
  note?: string
}

export const useAccountingTransactionStore = defineStore(
  'accounting/transaction',
  () => {
    const index = ref()
    const current: Ref<TransactionObject> = ref(<TransactionObject>{})
    const errors: Ref<TransactionErrors | undefined> = ref()
    const filter: Ref<TransactionFilter> = ref({})

    const fetchIndex = async (force = false) => {
      if (force || index.value === null) {
        const urlParams = new URLSearchParams(
          Object.entries(filter.value).filter((el) => el[1] !== undefined),
        )
        return api
          .get(`accounting/transactions?${urlParams}`)
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

    const show = async (id: number) => {
      return api
        .get(`accounting/transactions/${id}`)
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

    const create = (prefill: TransactionObject = <TransactionObject>{}) => {
      current.value = {
        ...{ name: '' },
        ...prefill,
      }
    }

    const store = async () => {
      const transaction = current.value
      if (!transaction) throw new Error('No transaction')
      return api
        .post('accounting/transactions', transaction)
        .then((response) => {
          transaction.id = response.data.id
          if (index.value !== null) fetchIndex()
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
      return api
        .put(`accounting/transactions/${id}`, current.value)
        .then(() => {
          if (index.value !== null) fetchIndex()
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
        .delete(`accounting/transactions/${id}`)
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
