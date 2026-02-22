<script setup lang="ts">
const taskStore = useTaskStore()

const draggedIndex = ref<number | null>(null)

function onDragStart(index: number, event: DragEvent) {
  draggedIndex.value = index
  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = 'move'
  }
}

function onDragOver(event: DragEvent) {
  event.preventDefault()
}

async function onDrop(targetIndex: number) {
  if (draggedIndex.value === null || draggedIndex.value === targetIndex) {
    draggedIndex.value = null
    return
  }

  const reordered = [...taskStore.tasks]
  const [moved] = reordered.splice(draggedIndex.value, 1)
  reordered.splice(targetIndex, 0, moved)

  // Optimistically update local order
  taskStore.tasks = reordered

  // Persist to API
  await taskStore.reorderTasks(reordered.map((t, i) => ({ id: t.id, sort_order: i })))

  draggedIndex.value = null
}
</script>

<template>
  <div>
    <TaskItem
      v-for="(task, index) in taskStore.tasks"
      :key="task.id"
      :task="task"
      :dragging="draggedIndex === index"
      @dragstart="onDragStart(index, $event)"
      @dragover="onDragOver($event)"
      @drop="onDrop(index)"
    />
  </div>
</template>
