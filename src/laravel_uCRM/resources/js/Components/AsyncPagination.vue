<script setup>
defineProps({
  links: Array
});
</script>

<template>
  <div v-if="links.length > 3">
    <div class="flex flex-wrap -mb-1">
      <template v-for="(link, index) in links" :key="index">
        <button
          type="button"
          v-if="link.url"
          @click="$emit('change-page', link.url)"
          class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500"
          :class="{'bg-blue-700 text-white': link.active}"
          v-html="link.label"
        />
        <div
          v-else
          class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded"
          v-html="link.label"
        />
      </template>
    </div>
  </div>
</template>