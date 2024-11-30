<style>
.q-table td {
  font-size: 12px;
}

.q-table th {
  font-weight: bold;
  font-size: 12px;
}
</style>
<template>
  <div class="col-3">
    <q-markup-table>
      <q-tr>
        <q-th align="left">Account</q-th>
        <q-th align="right">Amount</q-th>
        <q-th align="right">Totals</q-th>
      </q-tr>
      <q-tr>
        <q-td colspan="3" style="font-size: 20px" align="center">Income</q-td>
      </q-tr>
      <q-tr v-for="row in income" :key="row.name">
        <q-td>{{ row.name }}</q-td>
        <q-td align="right">{{ format.number(row.amount) }}</q-td>
      </q-tr>
      <q-tr>
        <q-td class="text-bold">Total Income</q-td>
        <q-td />
        <q-td
          class="text-bold"
          align="right"
          style="border-top: 5px double white"
        >
          {{ format.number(totalIncome) }}
        </q-td>
      </q-tr>
      <q-tr>
        <q-td
          colspan="3"
          style="font-size: 20px; border-top: solid 1px"
          align="center"
          >Cost</q-td
        >
      </q-tr>
      <q-tr v-for="row in cost" :key="row.name">
        <q-td>{{ row.name }}</q-td>
        <q-td align="right">{{ format.number(row.amount) }}</q-td>
      </q-tr>
      <q-tr>
        <q-td class="text-bold">Total Cost</q-td>
        <q-td />
        <q-td
          class="text-bold"
          align="right"
          style="border-top: 5px double white"
        >
          {{ format.number(totalCost) }}
        </q-td>
      </q-tr>
      <q-tr>
        <q-td class="text-bold">Result</q-td>
        <q-td />
        <q-td
          class="text-bold"
          align="right"
          style="border-top: 5px double white"
        >
          {{ format.number(totalIncome - totalCost) }}
        </q-td>
      </q-tr>
      <q-tr>
        <q-td
          colspan="3"
          style="font-size: 20px; border-top: solid 1px"
          align="center"
        >
          Unicenta sales
        </q-td>
      </q-tr>
      <q-tr>
        <q-td colspan="3">
          Unicenta sales All sales might not yet have reflected cash balance<br />
          due to customers charge to room. It also doesn't include out of<br />
          UniCenta sales, like Agoda and Expedia.
        </q-td>
      </q-tr>
      <q-tr v-for="row in reportStore.result.unicenta" :key="row.name">
        <q-td>{{ row.name }}</q-td>
        <q-td align="right">{{ format.number(row.amount) }}</q-td>
      </q-tr>
    </q-markup-table>
  </div>
</template>

<script setup lang="ts">
import { format } from 'boot/format'
import { useAccountingReportStore } from 'stores/accounting/report'
import { computed } from 'vue'

const reportStore = useAccountingReportStore()

const income = computed(() => {
  return reportStore.result.income.filter(
    (val: { amount: number }) => val.amount !== 0,
  )
})

const totalIncome = computed(() => {
  return reportStore.result.income.reduce(
    (accumulator: number, currentValue: { amount: number }) => {
      return accumulator + currentValue.amount
    },
    0,
  )
})

const cost = computed(() => {
  return reportStore.result.cost.filter(
    (val: { amount: number }) => val.amount !== 0,
  )
})

const totalCost = computed(() => {
  return reportStore.result.cost.reduce(
    (accumulator: number, currentValue: { amount: number }) => {
      return accumulator + currentValue.amount
    },
    0,
  )
})
</script>
