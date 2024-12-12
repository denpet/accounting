import { defineStore } from 'pinia'
import { ref } from 'vue'
import { Notify } from 'quasar'
import { api } from 'boot/axios'

export type ProductObject = {
  id: string | null
  name: string
  pricebuy: number
}

export const useUnicentaProductStore = defineStore('unicenta/product', () => {
  const pricebuyIndex = ref([])

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

  const updatePricebuy = async (id: string, pricebuy: number) => {
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
    fetchPricebuyIndex,
    pricebuyIndex,
    updatePricebuy,
  }
})
