<script setup>
import { getToday } from '@/common';
import { onMounted, reactive } from 'vue';

onMounted(() => {
  form.date = getToday()
})

const form = reactive({
  date: null,
  customer_id: null,
})

const props = defineProps({
  'customers': Array,

})
</script>

<template>
  会員名
  <br>
  <select name="customer" v-model="form.customer_id">
    <option v-for="customer in customers" :value="customer.id" :key="customer.id">
      {{ customer.id }} : {{ customer.name }}
    </option>
  </select>
  <br>
  日付<br>
  <input type="date" name="date" v-model="form.date">
</template>