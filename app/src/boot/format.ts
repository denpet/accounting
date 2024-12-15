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
  uuidv4: () => {
    return '10000000-1000-4000-8000-100000000000'.replace(/[018]/g, (c) =>
      (
        +c ^
        (crypto.getRandomValues(new Uint8Array(1))[0] & (15 >> (+c / 4)))
      ).toString(16),
    )
  },
}

export default boot(({ app }) => {
  app.config.globalProperties.$format = format
})

export { format }
