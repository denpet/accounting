import { boot } from 'quasar/wrappers'

const format = {
  number: (value: number, decimals = 0, showZero = false) => {
    if (!showZero && !value) return ''
    if (value === null) return ''
    return new Intl.NumberFormat('sv-SE', {
      maximumFractionDigits: decimals,
    }).format(value)
  },
  tin: (value: string | null) => {
    if (value === null) return ''
    let result = ''
    for (const char of value) {
      if (result.length == 3 || result.length == 7 || result.length == 11) {
        result += '-'
      }
      result += char
    }
    return result
  },
}

export default boot(({ app }) => {
  app.config.globalProperties.$format = format
})

export { format }
