import { boot } from 'quasar/wrappers'

const format = {
  number: (value: number, decimals = 0, showZero = false) => {
    if (!showZero && !value) return ''
    if (value === null) return ''
    return new Intl.NumberFormat('sv-SE', {
      maximumFractionDigits: decimals,
    }).format(value)
  },
}

export default boot(({ app }) => {
  app.config.globalProperties.$format = format
})

export { format }
