import { defineStore } from 'pinia'
import { Ref, ref } from 'vue'
import { Notify } from 'quasar'
import { api } from 'boot/axios'

type ReportFilter = {
  from_date?: string
  to_date?: string
}

export const useUnicentaReportStore = defineStore('unicenta/report', () => {
  const costIncomeReport = ref([])
  const filter: Ref<ReportFilter> = ref({})

  const fetchCostIncomeReport = async () => {
    const urlParams = new URLSearchParams(
      Object.entries(filter.value).filter((el) =>
        ['from_date', 'to_date'].includes(el[0]),
      ),
    )
    return api
      .get(`unicenta/reports/cost-income?${urlParams}`)
      .then((response) => {
        costIncomeReport.value = response.data
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
    fetchCostIncomeReport,
    costIncomeReport,
    filter,
  }
})
