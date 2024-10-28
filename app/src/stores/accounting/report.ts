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

export const useAccountingReportStore = defineStore('accounting/report', () => {
  const balance = ref()
  const balanceFilter: Ref<BalanaceFilter> = ref({
    date: new Date().toLocaleDateString('sv'),
  })
  const result = ref()
  const resultFilter: Ref<ResultFilter> = ref({
    from: '2024-10-01',
    to: new Date().toLocaleDateString('sv'),
  })

  const ledger = ref()
  const ledgerFilter: Ref<LedgerFilter> = ref({
    from: '2024-10-01',
    to: new Date().toLocaleDateString('sv'),
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
  }
})
