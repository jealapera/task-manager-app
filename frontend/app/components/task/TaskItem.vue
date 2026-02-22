<script setup lang="ts">
import { AlertTriangle, Check, Pencil, Trash2, X } from 'lucide-vue-next'
import type { Priority, Task } from '~/types'
import { PRIORITY_COLORS, PRIORITY_LABELS } from '~/types'

const props = defineProps<{
  task: Task
  dragging?: boolean
}>()

const emit = defineEmits<{
  dragstart: [event: DragEvent]
  dragover: [event: DragEvent]
  drop: [event: DragEvent]
}>()

const taskStore = useTaskStore()

// ── Inline edit ───────────────────────────────────────────────────────────────
const isEditing = ref(false)
const editValue = ref('')

function startEdit() {
  if (props.task.is_completed) return
  editValue.value = props.task.statement
  isEditing.value = true
  nextTick(() => (document.getElementById(`edit-${props.task.id}`) as HTMLInputElement)?.focus())
}

async function saveEdit() {
  const trimmed = editValue.value.trim()
  if (trimmed && trimmed !== props.task.statement) {
    await taskStore.updateTask(props.task.id, { statement: trimmed })
  }
  isEditing.value = false
}

function cancelEdit() {
  editValue.value = props.task.statement
  isEditing.value = false
}

// ── Priority cycling ──────────────────────────────────────────────────────────
const showPrioTooltip = ref(false)
const prioTooltipText = computed(() =>
  props.task.priority === 0
    ? 'Set priority — click to cycle'
    : `${PRIORITY_LABELS[props.task.priority as Priority]} — click to change`,
)

function cyclePriority() {
  const next = ((props.task.priority + 1) % 4) as Priority
  taskStore.updateTask(props.task.id, { priority: next })
}

// ── Delete with modal confirmation ────────────────────────────────────────────
const showDeleteModal = ref(false)
const deleting = ref(false)

async function handleDelete() {
  deleting.value = true
  await taskStore.deleteTask(props.task.id)
  deleting.value = false
  showDeleteModal.value = false
}
</script>

<template>
  <div
    class="group flex items-center gap-3 border border-gray-200 rounded-2xl px-4 py-3 mb-2 transition hover:border-gray-300"
    :class="[{ 'opacity-50': dragging }, isEditing ? 'cursor-default' : 'cursor-grab']"
    :draggable="!isEditing"
    @dragstart="!isEditing && emit('dragstart', $event)"
    @dragover.prevent="emit('dragover', $event)"
    @drop.prevent="emit('drop', $event)"
  >
    <!-- Checkbox toggle -->
    <button
      class="flex-shrink-0 w-5 h-5 rounded-full border-2 flex items-center justify-center transition"
      :class="task.is_completed
        ? 'bg-gray-900 border-gray-900'
        : 'border-gray-300 hover:border-gray-500'"
      @click="taskStore.toggleTask(task)"
    >
      <Check v-if="task.is_completed" :size="11" class="text-white" stroke-width="3" />
    </button>

    <!-- Priority badge -->
    <div
      class="relative flex-shrink-0"
      @mouseenter="showPrioTooltip = true"
      @mouseleave="showPrioTooltip = false"
    >
      <button
        class="text-xs font-medium rounded-full transition hover:scale-105 flex items-center justify-center"
        :class="task.priority === 0
          ? 'w-5 h-5 border border-dashed border-gray-300 text-gray-400 hover:border-gray-400 hover:text-gray-500'
          : `px-2 py-0.5 ${PRIORITY_COLORS[task.priority as Priority]}`"
        @click="cyclePriority"
      >
        <span v-if="task.priority === 0" class="text-[10px]">+</span>
        <span v-else>{{ PRIORITY_LABELS[task.priority as Priority] }}</span>
      </button>
      <span
        class="pointer-events-none absolute bottom-full left-0 mb-2 whitespace-nowrap rounded-lg bg-gray-800 px-2 py-1 text-xs text-white transition-opacity z-10"
        :class="showPrioTooltip ? 'opacity-100' : 'opacity-0'"
      >
        {{ prioTooltipText }}
      </span>
    </div>

    <!-- Statement -->
    <div class="flex-1 min-w-0 flex items-center gap-1.5">
      <!-- Edit mode -->
      <template v-if="isEditing">
        <input
          :id="`edit-${task.id}`"
          v-model="editValue"
          type="text"
          class="flex-1 text-sm text-gray-900 bg-transparent focus:outline-none border-b border-gray-400"
          @blur="cancelEdit"
          @keyup.enter="saveEdit"
          @keyup.escape="cancelEdit"
        >
        <!-- Confirm save — mousedown.prevent stops input blur before click fires -->
        <button
          class="flex-shrink-0 text-green-500 hover:text-green-600 transition"
          title="Save (Enter)"
          @mousedown.prevent
          @click="saveEdit"
        >
          <Check :size="14" stroke-width="3" />
        </button>
        <!-- Cancel -->
        <button
          class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition"
          title="Cancel (Esc)"
          @mousedown.prevent
          @click="cancelEdit"
        >
          <X :size="14" />
        </button>
      </template>

      <!-- View mode -->
      <template v-else>
        <span
          class="text-sm flex-1 truncate"
          :class="[
            task.is_completed ? 'line-through text-gray-400' : 'text-gray-800',
            !task.is_completed ? 'cursor-pointer' : '',
          ]"
          @click="startEdit"
        >
          {{ task.statement }}
        </span>

        <!-- Pencil — visible on hover, hidden for completed tasks -->
        <button
          v-if="!task.is_completed"
          class="flex-shrink-0 text-gray-300 hover:text-gray-500 transition opacity-0 group-hover:opacity-100"
          title="Edit task"
          @click.stop="startEdit"
        >
          <Pencil :size="13" />
        </button>
      </template>
    </div>

    <!-- Trash — hidden while editing -->
    <button
      v-if="!isEditing"
      class="flex-shrink-0 text-gray-300 hover:text-red-500 transition opacity-0 group-hover:opacity-100"
      title="Delete task"
      @click="showDeleteModal = true"
    >
      <Trash2 :size="15" />
    </button>
  </div>

  <!-- ── Delete confirmation modal ── -->
  <Teleport to="body">
    <Transition name="modal-fade">
      <div
        v-if="showDeleteModal"
        class="fixed inset-0 z-50 flex items-center justify-center px-4"
        @keydown.escape="showDeleteModal = false"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/40" @click="showDeleteModal = false" />

        <!-- Modal card -->
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
          <!-- Close button -->
          <button
            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition"
            @click="showDeleteModal = false"
          >
            <X :size="16" />
          </button>

          <!-- Title row: warning icon + heading -->
          <div class="flex items-center gap-2 mb-2">
            <AlertTriangle :size="18" class="flex-shrink-0 text-yellow-500" />
            <h3 class="text-base font-semibold text-gray-900">
              Delete task?
            </h3>
          </div>

          <!-- Task statement -->
          <p class="text-sm text-gray-500 mb-6 line-clamp-2 pl-6">
            "{{ task.statement }}"
          </p>

          <div class="flex gap-3">
            <button
              class="flex-1 px-4 py-2 rounded-xl border border-gray-200 text-sm text-gray-700 hover:bg-gray-50 transition"
              @click="showDeleteModal = false"
            >
              Cancel
            </button>
            <button
              class="flex-1 px-4 py-2 rounded-xl bg-red-500 hover:bg-red-600 disabled:opacity-50 text-sm text-white font-medium transition"
              :disabled="deleting"
              @click="handleDelete"
            >
              {{ deleting ? 'Deleting…' : 'Delete' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
</style>
