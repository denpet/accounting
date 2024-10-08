<template>
  <div class="row">
    <p class="col-12">
      {{ timeRecordStore.employee?.name }}
    </p>
    <q-markup-table class="col-7">
      <tr>
        <th>Bio Timestamp</th>
        <th>Bio Status</th>
        <th>Timestamp</th>
        <th>Hidden</th>
      </tr>
      <tr v-for="(current, index) in timeRecordStore.current" :key="index">
        <td>{{ current.biometric_timestamp }}</td>
        <td>{{ current.biometric_status_name }}</td>
        <td>
          <q-input label="Timestamp" v-model="current.adjusted_timestamp">
            <template v-slot:append>
              <q-icon name="access_time" class="cursor-pointer">
                <q-popup-proxy
                  cover
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="q-gutter-md row items-start">
                    <q-date
                      v-model="current.adjusted_timestamp"
                      mask="YYYY-MM-DD HH:mm"
                      color="purple"
                    />
                    <q-time
                      v-model="current.adjusted_timestamp"
                      mask="YYYY-MM-DD HH:mm"
                      color="purple"
                      format24h
                    />
                  </div>
                </q-popup-proxy>
              </q-icon>
            </template>
          </q-input>
        </td>
        <td>
          <q-toggle
            label="Show"
            v-model.number="current.hide"
            :false-value="1"
            :true-value="0"
          />
        </td>
      </tr>
      <q-tr>
        <q-td />
        <q-td />
        <q-td>
          <q-btn @click="onAddTime" round color="primary" icon="mdi-plus" />
        </q-td>
      </q-tr>
      <q-tr>
        <q-td>
          <q-btn label="Submit" @click="onSubmit" color="positive" />
        </q-td>
      </q-tr>
    </q-markup-table>
    <q-markup-table class="col-5">
      <q-tr>
        <q-th>Check in</q-th>
        <q-th>check out</q-th>
        <q-th align="right">Time worked</q-th>
      </q-tr>
      <q-tr v-for="(time, index) in payroll.rows" :key="index">
        <q-td>{{ time.in }}</q-td>
        <q-td>{{ time.out }}</q-td>
        <q-td align="right">{{ time.time }}</q-td>
      </q-tr>
      <q-tr>
        <q-td />
        <q-td align="right">Total</q-td>
        <q-td align="right">{{ payroll.total }}</q-td>
      </q-tr>
    </q-markup-table>
  </div>
</template>

<script setup lang="ts">
import { usePayrollTimeRecordStore } from 'stores/payroll/time-record'
import { computed } from 'vue'

const timeRecordStore = usePayrollTimeRecordStore()

const onAddTime = () => {
  if (timeRecordStore.employee) {
    timeRecordStore.create({
      id: null,
      employee_id: timeRecordStore.employee.id,
      biometric_timestamp: null,
      biometric_status: null,
      biometric_status_name: null,
      adjusted_timestamp: '',
      hide: 0,
    })
  }
}

const onSubmit = () => {
  timeRecordStore.store().then(() => {
    timeRecordStore.current = []
  })
}

const payroll = computed(() => {
  const temp = [...timeRecordStore.current]
  temp.sort((a, b) =>
    a.adjusted_timestamp > b.adjusted_timestamp
      ? 1
      : b.adjusted_timestamp > a.adjusted_timestamp
        ? -1
        : 0,
  )
  const payroll: {
    total: number
    rows: { in: string; out: string; time: number }[]
  } = { total: 0, rows: [] }
  let checkIn = true
  temp.forEach((time) => {
    if (!time.hide) {
      if (checkIn) {
        payroll.rows.push({
          in: time.adjusted_timestamp,
          out: '',
          time: 10,
        })
      } else {
        payroll.rows[payroll.rows.length - 1].out = time.adjusted_timestamp
        const elapsed =
          new Date(payroll.rows[payroll.rows.length - 1].out).valueOf() -
          new Date(payroll.rows[payroll.rows.length - 1].in).valueOf()
        payroll.rows[payroll.rows.length - 1].time =
          Math.round(elapsed / 360000) / 10
        payroll.total += elapsed
      }
      checkIn = !checkIn
    }
  })
  payroll.total = Math.round(payroll.total / 360000) / 10
  return payroll
})
</script>
