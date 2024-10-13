import { defineStore } from 'pinia'
import { Ref, ref } from 'vue'
import { Notify } from 'quasar'
import { api } from 'boot/axios'

export type TimeRecordObject = {
  id: number | null
  employee_id: number
  biometric_timestamp: string | null
  biometric_status: number | null
  biometric_status_name: string | null
  adjusted_timestamp: string
  hide: number
}

export type TimeRecordErrors = {
  id?: string
  employee_id?: string
  biometric_timestamp?: string
  biometric_status?: string
  adjusted_timestamp?: string
  hide?: string
}

type TimeRecordFilter = {
  period?: string
}

export const usePayrollTimeRecordStore = defineStore(
  'payroll/time-record',
  () => {
    const index = ref()
    const periods = ref([])
    const current: Ref<TimeRecordObject[]> = ref([])
    const errors: Ref<TimeRecordErrors | undefined> = ref()
    const filter: Ref<TimeRecordFilter> = ref({})
    const employee: Ref<{ id: number; name: string } | undefined> = ref()

    const fetchIndex = async () => {
      return api
        .get(`payroll/time-records?from=${filter.value.period}`)
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

    const fetchPeriods = async () => {
      const urlParams = new URLSearchParams(
        Object.entries(filter.value).filter((el) => el[1] !== undefined),
      )
      return api
        .get(`payroll/time-records/periods?${urlParams}`)
        .then((response) => {
          periods.value = response.data
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

    const show = async (
      employeeId: number,
      from: string,
      employeeName: string,
    ) => {
      return api
        .get(`payroll/time-records/${employeeId}/${from}`)
        .then((response) => {
          current.value = response.data
          employee.value = {
            id: employeeId,
            name: employeeName,
          }
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

    const create = (prefill: TimeRecordObject = <TimeRecordObject>{}) => {
      current.value.push({
        ...{
          id: null,
          biometric_timestamp: null,
          biometric_status: null,
          adjusted_timestamp: null,
          hide: false,
        },
        ...prefill,
      })
    }

    const store = async () => {
      if (!current.value) throw new Error('No time-record')
      errors.value = undefined
      return api
        .post('payroll/time-records', current.value)
        .then((response) => {
          if (current.value) current.value = response.data
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
        .put(`payroll/time-records/${id}`, current.value)
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
        .delete(`payroll/time-records/${id}`)
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
      fetchPeriods,
      show,
      create,
      store,
      update,
      destroy,
      index,
      filter,
      periods,
      current,
      employee,
      errors,
    }
  },
)
