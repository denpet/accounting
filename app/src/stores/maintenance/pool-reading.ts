import { defineStore } from 'pinia'
import { Ref, ref } from 'vue'
import { Notify } from 'quasar'
import { api } from 'boot/axios'

export type PoolReadingObject = {
  id: number | null
  date: string
  free_chlorine: number | null
  ph: number | null
  alkalinity: number | null
  total_chlorine: number | null
  hardness: number | null
  cyanuric_acid: number | null
}

export type PoolReadingErrors = {
  id?: string
  date?: string
  free_chlorine?: string
  ph?: string
  alkalinity?: string
  total_chlorine?: string
  hardness?: string
  cyanuric_acid?: string
}

type PoolReadingFilter = {
  date_from?: string
  date_to?: string
}

export const useMaintenancePoolReadingStore = defineStore(
  'maintenance/pool-reading',
  () => {
    const index = ref()
    const options = ref([])
    const current: Ref<PoolReadingObject | undefined> = ref()
    const errors: Ref<PoolReadingErrors | undefined> = ref()
    const filter: Ref<PoolReadingFilter> = ref({})

    const fetchIndex = async () => {
      const urlParams = new URLSearchParams(
        Object.entries(filter.value).filter((el) => el[1] !== undefined),
      )
      return api
        .get(`maintenance/pool-readings?${urlParams}`)
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
        .get(`maintenance/pool-readings/options?${urlParams}`)
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
        .get(`maintenance/pool-readings/${id}`)
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

    const create = (prefill: PoolReadingObject = <PoolReadingObject>{}) => {
      current.value = {
        ...prefill,
      }
    }

    const store = async () => {
      if (!current.value) throw new Error('No pool reading')
      errors.value = undefined
      return api
        .post('maintenance/pool-readings', current.value)
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
        .put(`maintenance/pool-readings/${id}`, current.value)
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
        .delete(`maintenance/pool-readings/${id}`)
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
  },
)
