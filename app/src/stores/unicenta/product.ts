import { defineStore } from 'pinia'
import { Ref, ref } from 'vue'
import { Notify } from 'quasar'
import { api } from 'boot/axios'
import { ProductBundleObject } from './product-bundle'

export type ProductObject = {
  id: string | null
  name: string
  pricebuy: number
  bundle: Array<ProductBundleObject>
}

export type ProductErrors = {
  id?: string
  name?: string
  pricebuy?: string
}

type ProductFilter = {
  name?: string
  category?: string
}

export const useUnicentaProductStore = (id = '') =>
  defineStore(`unicenta/product${id}`, () => {
    const index = ref([])
    const options = ref([])
    const current: Ref<ProductObject | undefined> = ref()
    const errors: Ref<ProductErrors | undefined> = ref()
    const filter: Ref<ProductFilter> = ref({})

    const pricebuyIndex = ref([])

    const fetchIndex = async () => {
      const urlParams = new URLSearchParams(
        Object.entries(filter.value).filter((el) => el[1] !== undefined),
      )
      return api
        .get(`unicenta/products?${urlParams}`)
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
        .get(`unicenta/products/options?${urlParams}`)
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
        .get(`unicenta/products/${id}`)
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

    const create = (prefill: ProductObject = <ProductObject>{}) => {
      current.value = {
        ...{ name: '', email: '' },
        ...prefill,
      }
    }

    const store = async () => {
      if (!current.value) throw new Error('No product')
      errors.value = undefined
      return api
        .post('unicenta/products', current.value)
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
        .put(`unicenta/products/${id}`, current.value)
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
        .delete(`unicenta/products/${id}`)
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

    const fetchPricebuyIndex = async () => {
      return api
        .get('unicenta/products/pricebuy')
        .then((response) => {
          pricebuyIndex.value = response.data
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

    const pricebuyUpdate = async (id: string, pricebuy: number) => {
      return api
        .put(`unicenta/products/pricebuy/${id}`, { pricebuy: pricebuy })
        .then(() => {
          Notify.create({
            message: 'Updated',
            type: 'positive',
            position: 'top-right',
            progress: true,
          })
        })
        .catch((response) => {
          throw new Error(response.response.data.message)
        })
    }

    return {
      index,
      options,
      current,
      errors,
      filter,
      fetchIndex,
      fetchOptions,
      show,
      create,
      store,
      update,
      destroy,
      fetchPricebuyIndex,
      pricebuyIndex,
      pricebuyUpdate,
    }
  })()
