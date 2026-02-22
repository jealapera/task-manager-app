<script setup lang="ts">
import { Search } from 'lucide-vue-next'

const taskStore = useTaskStore()

const localSearch = ref('')
let debounceTimer: ReturnType<typeof setTimeout>

watch(localSearch, (val) => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    taskStore.search = val
    taskStore.fetchTasks()
  }, 300)
})
</script>

<template>
  <div class="relative w-full max-w-xs sm:max-w-[240px]">
    <Search :size="14" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" />
    <input
      v-model="localSearch"
      type="text"
      placeholder="Search"
      class="w-full border border-gray-200 rounded-full pl-8 pr-4 py-1.5 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-200 transition"
    >
  </div>
</template>
