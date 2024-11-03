<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const newTitle = ref('');
const newContent = ref('');
</script>

<template>
  InertiaTest<br>
  <a href="/">aタグ</a><br>
  <Link href="/">Link</Link><br><br>
  <Link :href="route('inertia.index')">名前付きルート</Link><br>
  <Link :href="route('inertia.show', { id: 1 })">button</Link><br>


  <div class="mb-8"></div>
  <input type="text" name="newTitle" v-model="newTitle"><br>
  <input type="text" name="newContent" v-model="newContent"><br>
  <Link as="button" method="post" :href="route('inertia.store')" :data="{
    title: newTitle,
    content: newContent,
  }">保存</Link>
</template>