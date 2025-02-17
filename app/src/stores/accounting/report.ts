import { defineStore } from 'pinia'
import { Ref, ref } from 'vue'
import { Notify } from 'quasar'
import { api } from 'boot/axios'

type BalanaceFilter = {
  date?: string
}

type ResultFilter = {
  from?: string
  to?: string
}

type LedgerFilter = {
  from?: string
  to?: string
}

type TransactionsFilter = {
  from?: string
  to?: string
  account?: string
  hideWithOR?: string
}

type AccountTransactionsFilter = {
  from?: string
  to?: string
  account?: string
  details?: string
}

export const useAccountingReportStore = defineStore('accounting/report', () => {
  const today = new Date()
  const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1)
  const firstDayOfYear = new Date(today.getFullYear(), 0, 1)

  const balance = ref()
  const balanceFilter: Ref<BalanaceFilter> = ref({
    date: today.toLocaleDateString('sv'),
  })
  const result = ref()
  const resultFilter: Ref<ResultFilter> = ref({
    from: firstDayOfMonth.toLocaleDateString('sv'),
    to: today.toLocaleDateString('sv'),
  })

  const ledger = ref()
  const ledgerFilter: Ref<LedgerFilter> = ref({
    from: firstDayOfMonth.toLocaleDateString('sv'),
    to: today.toLocaleDateString('sv'),
  })

  const transactions = ref()
  const transactionsFilter: Ref<TransactionsFilter> = ref({
    from: firstDayOfYear.toLocaleDateString('sv'),
    to: today.toLocaleDateString('sv'),
    hideWithOR: '0',
  })

  const accountTransactions = ref()
  const accountTransactionsFilter: Ref<AccountTransactionsFilter> = ref({
    from: firstDayOfMonth.toLocaleDateString('sv'),
    to: today.toLocaleDateString('sv'),
    details: 'yes',
  })

  const closedCash = ref()
  const closedCashFilter: Ref<AccountTransactionsFilter> = ref({
    from: firstDayOfMonth.toLocaleDateString('sv'),
    to: today.toLocaleDateString('sv'),
  })

  const fetchBalance = async () => {
    const urlParams = new URLSearchParams(
      Object.entries(balanceFilter.value).filter((el) => el[1] !== undefined),
    )
    return api
      .get(`accounting/report/balance?${urlParams}`)
      .then((response) => {
        balance.value = response.data
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

  const fetchResult = async () => {
    const urlParams = new URLSearchParams(
      Object.entries(resultFilter.value).filter((el) => el[1] !== undefined),
    )
    return api
      .get(`accounting/report/result?${urlParams}`)
      .then((response) => {
        result.value = response.data
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

  const fetchLedger = async () => {
    const urlParams = new URLSearchParams(
      Object.entries(ledgerFilter.value).filter((el) => el[1] !== undefined),
    )
    return api
      .get(`accounting/report/ledger?${urlParams}`)
      .then((response) => {
        ledger.value = response.data
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

  const fetchTransactions = async () => {
    const urlParams = new URLSearchParams(
      Object.entries(transactionsFilter.value).filter((el) => el[1] !== null),
    )
    return api
      .get(`accounting/report/transactions?${urlParams}`)
      .then((response) => {
        transactions.value = response.data
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

  const fetchAccountTransactions = async () => {
    const urlParams = new URLSearchParams(
      Object.entries(accountTransactionsFilter.value).filter(
        (el) => el[1] !== null,
      ),
    )
    return api
      .get(`accounting/report/account-transactions?${urlParams}`)
      .then((response) => {
        accountTransactions.value = response.data
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

  const fetchClosedCash = async () => {
    const urlParams = new URLSearchParams(
      Object.entries(closedCashFilter.value).filter((el) => el[1] !== null),
    )
    return api
      .get(`accounting/report/closed-cash?${urlParams}`)
      .then((response) => {
        closedCash.value = response.data
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

  return {
    balanceFilter,
    balance,
    fetchBalance,
    resultFilter,
    result,
    fetchResult,
    ledgerFilter,
    ledger,
    fetchLedger,
    transactionsFilter,
    transactions,
    fetchTransactions,
    accountTransactionsFilter,
    accountTransactions,
    fetchAccountTransactions,
    closedCashFilter,
    closedCash,
    fetchClosedCash,
  }
})
