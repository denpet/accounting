import { defineStore } from 'pinia'
import { Ref, ref } from 'vue'
import { Notify } from 'quasar'
import { api } from 'boot/axios'

export type CategoryObject = {
  id: string | null
  name: string
}

export type CategoryErrors = {
  id?: string
  name?: string
}

type CategoryFilter = {
  name?: string
}

export const useUnicentaCategoryStore = defineStore('unicenta/category', () => {
  const index = ref([])
  const options = ref([])
  const current: Ref<CategoryObject | undefined> = ref()
  const errors: Ref<CategoryErrors | undefined> = ref()
  const filter: Ref<CategoryFilter> = ref({})

  const fetchIndex = async () => {
    const urlParams = new URLSearchParams(
      Object.entries(filter.value).filter((el) => el[1] !== undefined),
    )
    return api
      .get(`unicenta/categories?${urlParams}`)
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
      .get(`unicenta/categories/options?${urlParams}`)
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

  const show = async (id: string) => {
    return api
      .get(`unicenta/categories/${id}`)
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

  const create = (prefill: CategoryObject = <CategoryObject>{}) => {
    current.value = {
      ...{ name: '', email: '' },
      ...prefill,
    }
  }

  const store = async () => {
    if (!current.value) throw new Error('No category')
    errors.value = undefined
    return api
      .post('unicenta/categories', current.value)
      .then((response) => {
        if (current.value) current.value.id = response.data.id
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

  const update = async (id: string) => {
    errors.value = undefined
    return api
      .put(`unicenta/categories/${id}`, current.value)
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
      .delete(`unicenta/categories/${id}`)
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
    fetchOptions,
    show,
    create,
    store,
    update,
    destroy,
    index,
    options,
    current,
    errors,
    filter,
  }
})
