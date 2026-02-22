<script setup lang="ts">
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

const taskStore = useTaskStore()

function toDateStr(d: Date): string {
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
}

const label = computed(() => {
  const today = new Date()
  const yesterday = new Date(today)
  yesterday.setDate(today.getDate() - 1)

  if (taskStore.currentDate === toDateStr(today)) return 'Today'
  if (taskStore.currentDate === toDateStr(yesterday)) return 'Yesterday'

  const d = new Date(taskStore.currentDate + 'T00:00:00')
  return d.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' })
})

const isToday = computed(() => taskStore.currentDate >= toDateStr(new Date()))
</script>

<template>
  <div class="flex items-center gap-2">
    <button
      class="p-1.5 rounded-full hover:bg-gray-100 text-gray-500 hover:text-gray-800 transition"
      title="Previous day"
      @click="taskStore.prevDay()"
    >
      <ChevronLeft :size="16" />
    </button>
    <span class="text-xs font-medium text-gray-600 min-w-[100px] text-center">{{ label }}</span>
    <button
      class="p-1.5 rounded-full transition"
      :class="isToday ? 'text-gray-300 cursor-not-allowed' : 'hover:bg-gray-100 text-gray-500 hover:text-gray-800'"
      :disabled="isToday"
      title="Next day"
      @click="taskStore.nextDay()"
    >
      <ChevronRight :size="16" />
    </button>
  </div>
</template>
