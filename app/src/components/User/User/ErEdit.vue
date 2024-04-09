<template>
  <q-form
    v-if="userStore.current"
    @submit="onSubmit"
    @reset="userStore.current = undefined"
    class="q-gutter-md"
  >
    <q-chip
      v-if="userStore.current.id"
      :label="userStore.current.id"
      icon="mdi-identifier"
      color="orange-6"
    />
    <q-chip v-else label="New " icon="mdi-identifier" color="orange-6" />
    <q-input
      label="Name"
      v-model="userStore.current.name"
      :error="userStore.errors?.name !== undefined"
      :error-message="userStore.errors?.name?.toString()"
    />
    <q-input
      label="E-mail"
      v-model="userStore.current.email"
      :error="userStore.errors?.email !== undefined"
      :error-message="userStore.errors?.email?.toString()"
    />
    <q-input
      label="Password"
      v-model="userStore.current.password"
      password
      :error="userStore.errors?.password !== undefined"
      :error-message="userStore.errors?.password?.toString()"
    />
    <q-toolbar>
      <q-btn label="Submit" type="submit" color="positive" />
      <q-space />
      <q-btn label="Cancel" type="reset" color="warning" />
      <q-space />
      <q-btn
        v-if="userStore.current?.id"
        label="Delete"
        @click="onDestroy"
        color="negative"
      />
    </q-toolbar>
  </q-form>
</template>

<script setup lang="ts">
import { useQuasar } from 'quasar'
import { useUserUserStore } from 'stores/user/user'

const $q = useQuasar()

const userStore = useUserUserStore()

const onSubmit = () => {
  if (userStore.current?.id) {
    userStore.update(userStore.current.id).then(() => {
      userStore.current = undefined
    })
  } else {
    userStore.store().then(() => {
      userStore.current = undefined
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
    userStore.destroy(userStore.current?.id ?? 0).then(() => {
      userStore.current = undefined
    })
  })
}
</script>
