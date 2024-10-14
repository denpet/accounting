import { defineStore } from 'pinia'
import { Ref, ref } from 'vue'
import { Notify } from 'quasar'
import { api } from 'boot/axios'

type BalanaceFilter = {
  date?: string
}

type LedgerFilter = {
  date?: string
}

export const useAccountingReportStore = defineStore('accounting/report', () => {
  const balance = ref()
  const balanceFilter: Ref<BalanaceFilter> = ref({
    date: new Date().toLocaleDateString('sv'),
  })
  const ledger = ref()
  const ledgerFilter: Ref<LedgerFilter> = ref({
    date: new Date().toLocaleDateString('sv'),
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

  const fetchLedger = async () => {
    const urlParams = new URLSearchParams(
      Object.entries(ledgerFilter.value).filter((el) => el[1] !== undefined),
    )
    return api
      .get(`accounting/report/ledger${urlParams}`)
      .then((response) => {
        ledger.value = response.data.data
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
    ledgerFilter,
    ledger,
    fetchLedger,
  }
})
