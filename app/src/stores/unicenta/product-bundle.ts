import { defineStore } from 'pinia'
import { Ref, ref } from 'vue'
import { Notify } from 'quasar'
import { api } from 'boot/axios'
import { format } from 'boot/format'

export type ProductBundleObject = {
  id: string | null
  product: string
  product_bundle: string
  quantity: number
}

export type ProductBundleErrors = {
  id?: string
  product?: string
  product_bundle?: string
  quantity?: string
}

type ProductBundleFilter = {
  product?: string
}

export const useUnicentaProductBundleStore = (id = '') =>
  defineStore(`unicenta/product-bundle${id}`, () => {
    const index = ref([])
    const current: Ref<ProductBundleObject | undefined> = ref()
    const errors: Ref<ProductBundleErrors | undefined> = ref()
    const filter: Ref<ProductBundleFilter> = ref({})

    const fetchIndex = async () => {
      const urlParams = new URLSearchParams(
        Object.entries(filter.value).filter((el) => el[1] !== undefined),
      )
      return api
        .get(`unicenta/products-bundle?${urlParams}`)
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

    const show = async (id: string) => {
      return api
        .get(`unicenta/products-bundle/${id}`)
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

    const create = (prefill: ProductBundleObject = <ProductBundleObject>{}) => {
      current.value = {
        ...{ product_bundle: '', quantity: 0 },
        ...prefill,
      }
    }

    const store = async () => {
      if (!current.value) throw new Error('No product bundle')
      errors.value = undefined
      current.value.id = format.uuidv4()
      return api
        .post('unicenta/products-bundle', current.value)
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
        .put(`unicenta/products-bundle/${id}`, current.value)
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

    const destroy = async (id: string) => {
      return api
        .delete(`unicenta/products-bundle/${id}`)
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
      index,
      current,
      errors,
      filter,
      fetchIndex,
      show,
      create,
      store,
      update,
      destroy,
    }
  })()
