<template>
  <q-table
    class="ellipsis"
    :columns="columns"
    :rows="employeeStore.index ?? []"
    row-key="id"
    :rows-per-page-options="[0]"
    dense
    @row-click="onShow"
  >
    <template #top-left>
      <div class="q-table__title">Employees</div>
      <q-btn
        @click.stop="employeeStore.create()"
        icon="mdi-plus"
        label="User"
        color="secondary"
        size="sm"
        rounded
        dense
        align="left"
        style="width: 6rem"
        class="q-ml-md"
      />
    </template>
  </q-table>
</template>

<script setup lang="ts">
import { usePayrollEmployeeStore } from 'stores/payroll/employee'
import { QTableColumn } from 'quasar'

const employeeStore = usePayrollEmployeeStore()
employeeStore.fetchIndex()

const onShow = (event: Event, row: { id: number }) => {
  employeeStore.show(row.id)
}

const columns: QTableColumn[] = [
  {
    name: 'id',
    label: 'Id#',
    field: 'id',
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
  {
    name: 'rate',
    label: 'Rate',
    field: 'rate',
    align: 'left',
    sortable: true,
  },
  {
    name: 'active',
    label: 'Type',
    field: (value) => {
      return value.active == 0
        ? 'Inactive'
        : value.active == 1
          ? 'Regular'
          : 'On-calls'
    },
    align: 'right',
    sortable: true,
  },
]
</script>
