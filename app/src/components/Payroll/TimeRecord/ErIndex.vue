<template>
  <q-table
    class="ellipsis"
    :columns="columns"
    :rows="timeRecordStore.index ?? []"
    row-key="id"
    :rows-per-page-options="[0]"
    dense
    @row-click="onShow"
  >
    <template #top-left>
      <div class="q-table__title">Time Records</div>
    </template>
    <template #top-right>
      <q-select
        v-model="timeRecordStore.filter.period"
        label="Period"
        :options="timeRecordStore.periods"
        map-options
        emit-value
        style="width: 25vh; max-width: 25vh"
        clearable
        @update:model-value="timeRecordStore.fetchIndex()"
      />
    </template>
  </q-table>
</template>

<script setup lang="ts">
import { usePayrollTimeRecordStore } from 'stores/payroll/time-record'
import { QTableColumn } from 'quasar'

const timeRecordStore = usePayrollTimeRecordStore()
timeRecordStore.fetchPeriods()

const onShow = (
  event: Event,
  row: { period: string; employee_id: number; name: string },
) => {
  timeRecordStore.show(row.employee_id, row.period, row.name)
}

const columns: QTableColumn[] = [
  {
    name: 'employee_id',
    label: 'Id#',
    field: 'employee_id',
    align: 'left',
    style: 'width: 5rem',
    sortable: true,
  },
  {
    name: 'name',
    label: 'Name',
    field: 'name',
    align: 'left',
    sortable: true,
  },
]
</script>
