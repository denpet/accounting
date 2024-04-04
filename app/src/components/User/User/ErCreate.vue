<template>
  <q-tr v-if="user !== undefined">
    <q-td>
      <q-btn
        color="accent"
        size="sm"
        round
        dense
        icon="mdi-arrow-down"
        @click="onCancel"
      />
    </q-td>
    <q-td>
      <q-chip label="New " icon="mdi-identifier" color="orange-6" />
    </q-td>
    <q-td>
      <q-input
        v-model="user.user_name"
        label="Name"
        :error="errors?.user_name !== undefined"
        :error-message="errors?.user_name?.toString()"
      />
    </q-td>
    <q-td>
      <q-input
        v-model="user.email"
        label="Email"
        :error="errors?.email !== undefined"
        :error-message="errors?.email?.toString()"
      />
    </q-td>
    <q-td>
      <q-input
        v-model="user.password"
        label="Password"
        type="password"
        :error="errors?.password !== undefined"
        :error-message="errors?.password?.toString()"
      />
    </q-td>
    <q-td>
      <q-btn
        color="positive"
        icon="mdi-content-save"
        size="sm"
        round
        flat
        @click="onSubmit"
      />
    </q-td>
  </q-tr>
</template>

<script setup lang="ts">
import { useUserUserStore } from 'stores/user/user'
import { computed } from 'vue'

const props = defineProps({
  id: { type: String, default: '' },
})

const userStore = useUserUserStore()

const user = computed(() => userStore.created.get(props.id))

const errors = computed(() => userStore.createdErrors.get(props.id))

const onSubmit = () => {
  userStore.store(props.id).then(() => {
    userStore.created.delete(props.id)
    userStore.createdErrors.delete(props.id)
  })
}

const onCancel = () => {
  userStore.created.delete(props.id)
  userStore.createdErrors.delete(props.id)
}
</script>
