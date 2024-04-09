import { defineStore } from 'pinia'
import { Ref, ref } from 'vue'
import { Notify } from 'quasar'
import { api } from 'boot/axios'

export type UserObject = {
  id: number | null
  name: string
  email: string
  password: string
}

export type UserErrors = {
  id?: string
  name?: string
  email?: string
  password?: string
}

type UserFilter = {
  name?: string
}

export const useUserUserStore = defineStore('user/user', () => {
  const index = ref([])
  const current: Ref<UserObject | undefined> = ref()
  const errors: Ref<UserErrors | undefined> = ref()
  const filter: Ref<UserFilter> = ref({})

  const fetchIndex = async () => {
    const urlParams = new URLSearchParams(
      Object.entries(filter.value).filter((el) => el[1] !== undefined),
    )
    return api
      .get(`users/users?${urlParams}`)
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

  const show = async (id: number) => {
    return api
      .get(`users/users/${id}`)
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

  const create = (prefill: UserObject = <UserObject>{}) => {
    current.value = {
      ...{ name: '', email: '' },
      ...prefill,
    }
  }

  const store = async () => {
    const user = current.value
    if (!user) throw new Error('No user')
    errors.value = undefined
    return api
      .post('users/users', user)
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
      .put(`users/users/${id}`, current.value)
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
      .delete(`users/users/${id}`)
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
    show,
    create,
    store,
    update,
    destroy,
    index,
    filter,
    current,
    errors,
  }
})
