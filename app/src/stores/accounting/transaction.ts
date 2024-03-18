import { defineStore } from 'pinia'
import { Ref, ref } from 'vue'
import { Notify } from 'quasar'
import { api } from 'boot/axios'

export type TransactionObject = {
  id: number | null
  date: Date
  from_account_id: number
  to_account_id: number
  note: string
  amount: number
  vat: number
  tin: string
  official_receipt: string
}

export type TransactionErrors = {
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
    const index = ref(null)
    const options = ref(null)
    const current: Ref<Map<number, TransactionObject>> = ref(new Map())
    const currentErrors: Ref<Map<number, TransactionErrors>> = ref(new Map())
    const created: Ref<Map<string, TransactionObject>> = ref(new Map())
    const createdErrors: Ref<Map<string, TransactionErrors>> = ref(new Map())
    const filter: Ref<TransactionFilter> = ref({})

    const fetchIndex = async (force = false) => {
      console.log('>>fetchIndex')
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

    const fetchOptions = async (force = false) => {
      if (force || options.value === null) {
        const urlParams = new URLSearchParams(
          Object.entries(filter.value).filter((el) => el[1] !== undefined),
        )
        return api
          .get(`accounting/transactions/options?${urlParams}`)
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
        .get(`accounting/transactions/${id}`)
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

    const create = (prefill: TransactionObject = <TransactionObject>{}) => {
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
      const transaction = created.value.get(id)
      if (!transaction) throw new Error('No transaction')
      return api
        .post('accounting/transactions', transaction)
        .then((response) => {
          transaction.id = response.data.id
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
        .put(`accounting/transactions/${id}`, current.value.get(id))
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
        .delete(`accounting/transactions/${id}`)
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
