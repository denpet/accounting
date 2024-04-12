import { defineStore } from 'pinia'
import { Ref, ref } from 'vue'
import { api, web } from 'boot/axios'

type Auth = {
  email: string
  password: string
  user_name: string
  role_id: number
}

type AuthError = {
  email?: string
  password?: string
  user_name?: string
}

export const useAuthStore = defineStore('auth', () => {
  const current: Ref<Auth> = ref({
    email: '',
    password: '',
    user_name: '',
    role_id: 0,
  })

  const errors: Ref<AuthError> = ref({})

  const user = async () => {
    api.get('user').then((response) => {
      current.value = response.data
    })
  }

  const login = async () => {
    errors.value = {}
    if (current.value.email === '') {
      errors.value.email = 'E-mail must be entered'
      throw new Error(errors.value.email)
    }
    if (current.value.password === '') {
      errors.value.password = 'Password must be entered'
      throw new Error(errors.value.password)
    }

    await web.get('/sanctum/csrf-cookie')
    return api
      .post('login', current.value)
      .then(async (response) => {
        current.value = response.data
        await user()
      })
      .catch((response) => {
        errors.value.password = response.response.data
        throw new Error(errors.value.password)
      })
  }

  const logout = async () => {
    return api.post('logout').then(() => {
      current.value = {
        email: '',
        password: '',
        user_name: '',
        role_id: 0,
      }
    })
  }

  return {
    current,
    errors,
    login,
    logout,
    user,
  }
})
