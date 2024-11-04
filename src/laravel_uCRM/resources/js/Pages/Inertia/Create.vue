<script setup>
  import { reactive } from 'vue';
  import { Inertia } from '@inertiajs/inertia';

  const form = reactive({
    title: null,
    content: null
  })

  const submitFunction = () => {
    Inertia.post('/inertia', form);
  }
</script>

<template>
  <form v-on:submit.prevent="submitFunction">
    <input name="title" v-model="form.title"><br>
    <input name="content" v-model="form.content">
    <button>送信</button>
  </form>
</template>