import { defineStore } from 'pinia'
import { Ref, ref } from 'vue'
import { Notify } from 'quasar'
import { api } from 'boot/axios'

export type RoleObject = {
  id: number | null
  name: string
}

export type RoleErrors = {
  id?: string
  name?: string
}

type RoleFilter = {
  name?: string
}

export const useUserRoleStore = defineStore('user/role', () => {
  const index = ref([])
  const options = ref([])
  const current: Ref<RoleObject | undefined> = ref()
  const errors: Ref<RoleErrors | undefined> = ref()
  const filter: Ref<RoleFilter> = ref({})

  const fetchIndex = async () => {
    const urlParams = new URLSearchParams(
      Object.entries(filter.value).filter((el) => el[1] !== undefined),
    )
    return api
      .get(`users/roles?${urlParams}`)
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
      .get(`users/roles/options?${urlParams}`)
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
      .get(`users/roles/${id}`)
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

  const create = (prefill: RoleObject = <RoleObject>{}) => {
    current.value = {
      ...{ name: '', email: '' },
      ...prefill,
    }
  }

  const store = async () => {
    const user = current.value
    if (!user) throw new Error('No role')
    errors.value = undefined
    return api
      .post('users/roles', user)
      .then((response) => {
        user.id = response.data.id
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
      .put(`users/roles/${id}`, current.value)
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
      .delete(`users/roles/${id}`)
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
    filter,
    current,
    errors,
  }
})
