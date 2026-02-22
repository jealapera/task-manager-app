<script setup lang="ts">
import { ArrowUp } from 'lucide-vue-next'
import type { Priority } from '~/types'
import { PRIORITY_COLORS, PRIORITY_LABELS } from '~/types'

defineProps<{
  centered?: boolean
}>()

const taskStore = useTaskStore()
const statement = ref('')
const priority = ref<Priority>(0)
const loading = ref(false)
const submitError = ref('')
const showPrioTooltip = ref(false)
const prioTooltipText = computed(() =>
  priority.value === 0 ? 'Set priority — click to cycle' : `${PRIORITY_LABELS[priority.value]} — click to change`,
)

function cyclePriority() {
  priority.value = ((priority.value + 1) % 4) as Priority
}

async function submit() {
  const trimmed = statement.value.trim()
  if (!trimmed || loading.value) return
  loading.value = true
  submitError.value = ''
  try {
    await taskStore.createTask(trimmed, priority.value)
    statement.value = ''
    priority.value = 0
  }
  catch (e: unknown) {
    submitError.value = (e as { data?: { message?: string } })?.data?.message ?? 'Failed to create task.'
  }
  finally {
    loading.value = false
  }
}
</script>

<template>
  <!-- Centered (empty state) — large textarea -->
  <div v-if="centered" class="relative w-full">
    <p v-if="submitError" class="text-xs text-red-500 mb-2">{{ submitError }}</p>
    <textarea
      v-model="statement"
      placeholder="Write the task you plan to do today here..."
      rows="4"
      class="w-full border border-gray-200 rounded-2xl px-5 py-4 pr-14 pb-14 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-200 resize-none transition"
      @keydown.enter.prevent="submit"
    />
    <!-- Priority picker — bottom left, tooltip above -->
    <div
      class="absolute bottom-4 left-4"
      @mouseenter="showPrioTooltip = true"
      @mouseleave="showPrioTooltip = false"
    >
      <button
        class="text-xs font-medium rounded-full transition hover:scale-105"
        :class="priority === 0
          ? 'w-6 h-6 border border-dashed border-gray-300 text-gray-400 hover:border-gray-400 hover:text-gray-500'
          : `px-2 py-0.5 ${PRIORITY_COLORS[priority]}`"
        @click.prevent="cyclePriority"
      >
        <span v-if="priority === 0" class="text-[10px]">+</span>
        <span v-else>{{ PRIORITY_LABELS[priority] }}</span>
      </button>
      <!-- Custom tooltip — appears above, no delay -->
      <span
        class="pointer-events-none absolute bottom-full left-0 mb-2 whitespace-nowrap rounded-lg bg-gray-800 px-2 py-1 text-xs text-white transition-opacity"
        :class="showPrioTooltip ? 'opacity-100' : 'opacity-0'"
      >
        {{ prioTooltipText }}
      </span>
    </div>
    <!-- Submit — bottom right -->
    <button
      class="absolute bottom-4 right-4 w-9 h-9 bg-gray-900 hover:bg-gray-700 disabled:opacity-40 text-white rounded-full flex items-center justify-center transition"
      :disabled="!statement.trim() || loading"
      @click="submit"
    >
      <ArrowUp :size="16" />
    </button>
  </div>

  <!-- Bottom bar (task list state) — single line -->
  <div v-else class="w-full">
    <p v-if="submitError" class="text-xs text-red-500 mb-1">{{ submitError }}</p>
    <div class="flex items-center gap-2 border border-gray-200 rounded-2xl px-3 py-2.5 focus-within:ring-2 focus-within:ring-gray-200 transition">
      <!-- Priority picker — flex sibling, tooltip above -->
      <div
        class="relative flex-shrink-0"
        @mouseenter="showPrioTooltip = true"
        @mouseleave="showPrioTooltip = false"
      >
        <button
          class="text-xs font-medium rounded-full transition hover:scale-105 flex items-center justify-center"
          :class="priority === 0
            ? 'w-5 h-5 border border-dashed border-gray-300 text-gray-400 hover:border-gray-400 hover:text-gray-500'
            : `px-2 py-0.5 ${PRIORITY_COLORS[priority]}`"
          @click.prevent="cyclePriority"
        >
          <span v-if="priority === 0" class="text-[10px]">+</span>
          <span v-else>{{ PRIORITY_LABELS[priority] }}</span>
        </button>
        <!-- Custom tooltip — appears above, no delay -->
        <span
          class="pointer-events-none absolute bottom-full left-0 mb-2 whitespace-nowrap rounded-lg bg-gray-800 px-2 py-1 text-xs text-white transition-opacity"
          :class="showPrioTooltip ? 'opacity-100' : 'opacity-0'"
        >
          {{ prioTooltipText }}
        </span>
      </div>

      <!-- Input — takes remaining space, no padding conflicts -->
      <input
        v-model="statement"
        type="text"
        placeholder="What else do you need to do?"
        class="flex-1 text-sm text-gray-700 placeholder-gray-400 focus:outline-none bg-transparent"
        @keydown.enter="submit"
      >

      <!-- Submit -->
      <button
        class="flex-shrink-0 w-8 h-8 bg-gray-900 hover:bg-gray-700 disabled:opacity-40 text-white rounded-full flex items-center justify-center transition"
        :disabled="!statement.trim() || loading"
        @click="submit"
      >
        <ArrowUp :size="14" />
      </button>
    </div>
  </div>
</template>
