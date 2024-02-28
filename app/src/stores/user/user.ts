import { defineStore } from 'pinia'
import { Ref, ref } from 'vue'
import { Notify } from 'quasar'
import { api } from 'boot/axios'

export type UserObject = {
  user_id: number | null
  user_name: string
  email: string
  password: string
}

export type UserErrors = {
  user_id?: string
  user_name?: string
  email?: string
  password?: string
}

type UserFilter = {
  user_name?: string
}

export const useUserUserStore = defineStore('user/user', () => {
  const index = ref(null)
  const options = ref(null)
  const current: Ref<Map<number, UserObject>> = ref(new Map())
  const currentErrors: Ref<Map<number, UserErrors>> = ref(new Map())
  const created: Ref<Map<string, UserObject>> = ref(new Map())
  const createdErrors: Ref<Map<string, UserErrors>> = ref(new Map())
  const filter: Ref<UserFilter> = ref({})

  const fetchIndex = async (force = false) => {
    if (force || index.value === null) {
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
  }

  const fetchOptions = async (force = false) => {
    if (force || options.value === null) {
      const urlParams = new URLSearchParams(
        Object.entries(filter.value).filter((el) => el[1] !== undefined),
      )
      return api
        .get(`users/users/options?${urlParams}`)
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
  }

  const show = async (id: number) => {
    return api
      .get(`users/users/${id}`)
      .then((response) => {
        current.value.set(id, response.data)
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
    const id = self.crypto.randomUUID()
    created.value.set(id, {
      ...{
        user_name: '',
        email: '',
      },
      ...prefill,
    })
    return id
  }

  const store = async (id: string) => {
    const user = created.value.get(id)
    if (!user) throw new Error('No user')
    return api
      .post('users/users', user)
      .then((response) => {
        user.user_id = response.data.user_id
        if (index.value !== null) fetchIndex(true)
        if (options.value !== null) fetchOptions(true)
        Notify.create({
          message: 'Stored',
          type: 'positive',
          position: 'top-right',
          progress: true,
        })
      })
      .catch((response) => {
        createdErrors.value.set(id, response.response.data.errors)
        throw new Error(response.response.data.message)
      })
  }

  const update = async (id: number) => {
    return api
      .put(`users/users/${id}`, current.value.get(id))
      .then(() => {
        if (index.value !== null) fetchIndex(true)
        if (options.value !== null) fetchOptions(true)
        Notify.create({
          message: 'Updated',
          type: 'positive',
          position: 'top-right',
          progress: true,
        })
      })
      .catch((response) => {
        currentErrors.value.set(id, response.response.data.errors)
        throw new Error(response.response.data.message)
      })
  }

  const destroy = async (id: number) => {
    return api
      .delete(`users/users/${id}`)
      .then(() => {
        if (index.value !== null) fetchIndex(true)
        if (options.value !== null) fetchOptions(true)
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
    currentErrors,
    created,
    createdErrors,
  }
})
