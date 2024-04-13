import { defineStore } from 'pinia'
import { Ref, ref } from 'vue'
import { Notify } from 'quasar'
import { api } from 'boot/axios'

export type EmployeeObject = {
  id: number | null
  name: string
  rate: number
  active: number
}

export type EmployeeErrors = {
  id?: string
  name?: string
  rate?: string
  active?: string
}

type EmployeeFilter = {
  name?: string
}

export const usePayrollEmployeeStore = defineStore('payroll/employee', () => {
  const index = ref()
  const options = ref([])
  const current: Ref<EmployeeObject | undefined> = ref()
  const errors: Ref<EmployeeErrors | undefined> = ref()
  const filter: Ref<EmployeeFilter> = ref({})

  const fetchIndex = async () => {
    const urlParams = new URLSearchParams(
      Object.entries(filter.value).filter((el) => el[1] !== undefined),
    )
    return api
      .get(`payroll/employees?${urlParams}`)
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

  const fetchOptions = async () => {
    const urlParams = new URLSearchParams(
      Object.entries(filter.value).filter((el) => el[1] !== undefined),
    )
    return api
      .get(`payroll/employees/options?${urlParams}`)
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

  const show = async (id: number) => {
    return api
      .get(`payroll/employees/${id}`)
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

  const create = (prefill: EmployeeObject = <EmployeeObject>{}) => {
    current.value = {
      ...{
        name: '',
        type: '',
      },
      ...prefill,
    }
  }

  const store = async () => {
    if (!current.value) throw new Error('No employee')
    errors.value = undefined
    return api
      .post('payroll/employees', current.value)
      .then((response) => {
        if (current.value) current.value.id = response.data.id
        fetchIndex()
        fetchOptions()
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
      .put(`payroll/employees/${id}`, current.value)
      .then(() => {
        fetchIndex()
        fetchOptions()
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
      .delete(`payroll/employees/${id}`)
      .then(() => {
        fetchIndex()
        fetchOptions()
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
    errors,
  }
})
