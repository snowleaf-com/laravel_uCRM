<script setup>
import { Inertia } from '@inertiajs/inertia';

defineProps({
  record: Object
})

const deleteConfirm = (id) => {
  Inertia.delete(`/inertia/${id}`, {
    onBefore: () => confirm('本当に削除しますか？')
  })
}
</script>

<template>
  <div>
    <h1>レコード詳細</h1>
    <p>ID: {{ record.id }}</p>
    <p>件名: {{ record.title }}</p>
    <p>本文: {{ record.content }}</p>
    <button v-on:click="deleteConfirm(record.id)">削除</button>
  </div>
</template>