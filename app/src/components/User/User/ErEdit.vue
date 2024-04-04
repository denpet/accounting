<template>
  <q-form v-if="user !== undefined" @submit.prevent="onSubmit">
    <q-card class="row">
      <q-card-section class="col-12">
        <q-chip :label="userId" icon="mdi-identifier" color="orange-6" />
      </q-card-section>
      <q-card-section class="col-4">
        <q-input
          v-model="user.user_name"
          label="Name"
          :error="errors?.user_name !== undefined"
          :error-message="errors?.user_name?.toString()"
        />
      </q-card-section>
      <q-card-section class="col-4">
        <q-input
          v-model="user.email"
          label="E-mail"
          :error="errors?.email !== undefined"
          :error-message="errors?.email?.toString()"
        />
      </q-card-section>
      <q-card-section class="col-4">
        <q-input
          v-model="user.password"
          label="Password"
          type="password"
          :error="errors?.password !== undefined"
          :error-message="errors?.password?.toString()"
        />
      </q-card-section>
      <q-card-actions class="col-12">
        <q-btn
          label="Save"
          type="submit"
          color="positive"
          icon="mdi-content-save"
        />
        <q-space />
        <q-btn
          label="Delete"
          color="negative"
          icon="mdi-delete"
          @click="onDestroy"
        />
      </q-card-actions>
    </q-card>
  </q-form>
</template>

<script setup lang="ts">
import { useUserUserStore } from 'stores/user/user'
import { useQuasar } from 'quasar'
import { computed, onMounted, onUnmounted } from 'vue'

const $q = useQuasar()

const emit = defineEmits(['changed'])

const props = defineProps({
  userId: { type: Number, default: null },
})

const userStore = useUserUserStore()

onMounted(() => userStore.show(props.userId))

onUnmounted(() => {
  userStore.current.delete(props.userId)
  userStore.currentErrors.delete(props.userId)
})

const user = computed(() => userStore.current.get(props.userId))

const errors = computed(() => userStore.currentErrors.get(props.userId))

const onSubmit = () => {
  userStore.update(props.userId).then(() => emit('changed'))
}

const onDestroy = () => {
  $q.dialog({
    title: 'Confirm',
    message: 'Are you sure you want to delete the row?',
    cancel: true,
    persistent: true,
  }).onOk(() => {
    userStore.destroy(props.userId).then(() => {
      emit('changed', null)
    })
  })
}
</script>
