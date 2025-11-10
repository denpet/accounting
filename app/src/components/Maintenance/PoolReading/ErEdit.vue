<template>
  <q-form
    v-if="poolReadingStore.current"
    @submit="onSubmit"
    @reset="poolReadingStore.current = undefined"
    class="q-gutter-md"
  >
    <q-chip
      v-if="poolReadingStore.current.id"
      :label="poolReadingStore.current.id"
      icon="mdi-identifier"
      color="orange-6"
    />
    <q-chip v-else label="New " icon="mdi-identifier" color="orange-6" />
    <div class="row">
      <q-input
        label="Date"
        class="col-4 q-pr-md"
        v-model="poolReadingStore.current.date"
        :error="poolReadingStore.errors?.date !== undefined"
        :error-message="poolReadingStore.errors?.date?.toString()"
      />
    </div>
    <div class="row">
      <q-input
        label="pH"
        class="col-4 q-pr-md"
        v-model="poolReadingStore.current.ph"
        :error="poolReadingStore.errors?.ph !== undefined"
        :error-message="poolReadingStore.errors?.ph?.toString()"
      />
    </div>
    <div class="row">
      <q-input
        label="Free Chlorine"
        class="col-4 q-pr-md"
        v-model="poolReadingStore.current.free_chlorine"
        :error="poolReadingStore.errors?.free_chlorine !== undefined"
        :error-message="poolReadingStore.errors?.free_chlorine?.toString()"
      />
      <q-input
        label="Total Chlorine"
        class="col-4 q-pr-md"
        v-model="poolReadingStore.current.total_chlorine"
        :error="poolReadingStore.errors?.total_chlorine !== undefined"
        :error-message="poolReadingStore.errors?.total_chlorine?.toString()"
      />
      <q-input
        label="Cyanuric Acid"
        class="col-4"
        v-model="poolReadingStore.current.cyanuric_acid"
        :error="poolReadingStore.errors?.cyanuric_acid !== undefined"
        :error-message="poolReadingStore.errors?.cyanuric_acid?.toString()"
      />
    </div>
    <div class="row">
      <q-input
        label="Alkalinity"
        class="col-4 q-pr-md"
        v-model="poolReadingStore.current.alkalinity"
        :error="poolReadingStore.errors?.alkalinity !== undefined"
        :error-message="poolReadingStore.errors?.alkalinity?.toString()"
      />
    </div>
    <div class="row">
      <q-input
        label="Hardness"
        class="col-4 q-pr-md"
        v-model="poolReadingStore.current.hardness"
        :error="poolReadingStore.errors?.hardness !== undefined"
        :error-message="poolReadingStore.errors?.hardness?.toString()"
      />
    </div>
    <q-toolbar>
      <q-btn label="Submit" type="submit" color="positive" />
      <q-space />
      <q-btn label="Cancel" type="reset" color="warning" />
      <q-space />
      <q-btn
        v-if="poolReadingStore.current?.id"
        label="Delete"
        @click="onDestroy"
        color="negative"
      />
    </q-toolbar>
    <fieldset v-if="recommendations">
      <legend>Recommendation</legend>
      <ul>
        <li v-for="recommendation in recommendations" :key="recommendation">
          {{ recommendation }}
        </li>
      </ul>
    </fieldset>
  </q-form>
</template>

<script setup lang="ts">
import { useQuasar } from 'quasar'
import { useMaintenancePoolReadingStore } from 'stores/maintenance/pool-reading'
import { computed } from 'vue'

const $q = useQuasar()

const poolReadingStore = useMaintenancePoolReadingStore()

const onSubmit = () => {
  if (poolReadingStore.current?.id) {
    poolReadingStore.update(poolReadingStore.current.id).then(() => {
      poolReadingStore.current = undefined
    })
  } else {
    poolReadingStore.store().then(() => {
      poolReadingStore.current = undefined
    })
  }
}

const onDestroy = () => {
  $q.dialog({
    title: 'Confirm',
    message: 'Are you sure you want to delete the row?',
    cancel: true,
    persistent: true,
  }).onOk(() => {
    poolReadingStore.destroy(poolReadingStore.current?.id ?? 0).then(() => {
      poolReadingStore.current = undefined
    })
  })
}

const recommendations = computed(() => {
  const recs: string[] = []

  if (!poolReadingStore.current) {
    return null
  }

  if (poolReadingStore.current.total_chlorine) {
    if (poolReadingStore.current.total_chlorine > 0) {
      const calHypo =
        Math.round(poolReadingStore.current.total_chlorine * 0.23 * 100) / 10
      recs.push(
        `Calcium Hypochlorite: Add ${calHypo} kg to chock the pool (only monday).`,
      )
    }
  } else {
    recs.push('Enter total chlorine reading to get chocking recommendation.')
  }

  if (poolReadingStore.current.cyanuric_acid) {
    if (poolReadingStore.current.cyanuric_acid < 50) {
      recs.push('Trichlor Cyanuric Acid: Daily add 130 gr.')
    } else {
      recs.push('Calcium Hypochlorite: Daily add 170 gr.')
    }
  } else {
    recs.push('Enter cyanuric acid reading to get chlorine recommendation.')
  }

  if (poolReadingStore.current.alkalinity && poolReadingStore.current.ph) {
    if (
      poolReadingStore.current.alkalinity < 120 &&
      poolReadingStore.current.ph < 7.6
    ) {
      const maxSodaAsh = Math.min(
        Math.round((7.6 - poolReadingStore.current.ph) * 0.68 * 50) / 10,
        Math.round((120 - poolReadingStore.current.alkalinity) * 0.68 * 2) / 10,
      )

      const toFixAlkalinity =
        poolReadingStore.current.alkalinity < 80
          ? Math.round((100 - poolReadingStore.current.alkalinity) * 0.68 * 2) /
            10
          : 0

      const toFixPh =
        poolReadingStore.current.ph < 7.2
          ? Math.round((7.4 - poolReadingStore.current.ph) * 0.68 * 50) / 10
          : 0

      if (Math.max(toFixAlkalinity, toFixPh) > maxSodaAsh) {
        recs.push(`Soda Ash: Add ${maxSodaAsh} kg. Test water again tomorrow.`)
      } else {
        const sodaAsh = Math.max(toFixAlkalinity, toFixPh)
        if (sodaAsh > 0) recs.push(`Soda Ash: Add ${sodaAsh} kg`)
      }
    } else if (
      poolReadingStore.current.alkalinity > 80 &&
      poolReadingStore.current.ph > 7.2
    ) {
      const maxMuriaticAcid = Math.min(
        Math.round((poolReadingStore.current.ph - 7.2) * 0.68 * 50) / 10,
        Math.round((poolReadingStore.current.alkalinity - 80) * 0.68 * 2) / 10,
      )

      const toFixAlkalinity =
        poolReadingStore.current.alkalinity > 120
          ? Math.round((poolReadingStore.current.alkalinity - 100) * 2.4) / 10
          : 0

      const toFixPh =
        poolReadingStore.current.ph > 7.6
          ? Math.round((poolReadingStore.current.ph - 7.4) * 1.2 * 25) / 10
          : 0

      if (Math.max(toFixAlkalinity, toFixPh) > maxMuriaticAcid) {
        recs.push(
          `Muriatic Acid: Add ${maxMuriaticAcid} l. Test water again tomorrow.`,
        )
      } else {
        const muriaticAcid = Math.max(toFixAlkalinity, toFixPh)
        if (muriaticAcid > 0) recs.push(`Muriatic Acid: Add ${muriaticAcid} l`)
      }
    }
  } else {
    recs.push(
      'Enter alkalinity and pH readings to get soda ash or muriatic acid recommendation.',
    )
  }

  if (poolReadingStore.current.hardness) {
    if (poolReadingStore.current.hardness > 200) {
      recs.push('Water: Use rainwater if available, otherwise municipal water.')
    } else {
      recs.push('Water: Use municipal water only')
    }
  } else {
    recs.push('Water: Enter hardness reading to get recommendation.')
  }

  return recs.length > 0 ? recs : ['All readings are within optimal ranges.']
})
</script>
