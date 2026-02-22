import type { Task, TasksResponse } from '~/types'

function toLocalDateStr(d: Date): string {
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
}

function parseLocalDate(dateStr: string): Date {
  const [y = 0, m = 0, d = 0] = dateStr.split('-').map(Number)
  return new Date(y, m - 1, d)
}

export const useTaskStore = defineStore('tasks', () => {
  const tasks = ref<Task[]>([])
  const currentDate = ref<string>(toLocalDateStr(new Date()))
  const search = ref('')
  const sortBy = ref<string>('sort_order')
  const sortDir = ref<string>('asc')
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchTasks(): Promise<void> {
    const { $api } = useNuxtApp()
    loading.value = true
    error.value = null
    try {
      const params = new URLSearchParams()
      if (search.value) {
        params.set('search', search.value)
      }
      else {
        params.set('task_date', currentDate.value)
      }
      params.set('sort_by', sortBy.value)
      params.set('sort_dir', sortDir.value)

      const data = await ($api as typeof $fetch)<TasksResponse>(`/tasks?${params}`)
      tasks.value = data.data
    }
    catch (e: unknown) {
      error.value = (e as { data?: { message?: string } })?.data?.message ?? 'Failed to load tasks.'
    }
    finally {
      loading.value = false
    }
  }

  async function createTask(statement: string, priority = 0): Promise<void> {
    const { $api } = useNuxtApp()
    const data = await ($api as typeof $fetch)<{ data: Task }>('/tasks', {
      method: 'POST',
      body: { statement, task_date: currentDate.value, priority },
    })
    tasks.value.push(data.data)
  }

  async function updateTask(id: number, payload: Partial<Pick<Task, 'statement' | 'status' | 'priority'>>): Promise<void> {
    const { $api } = useNuxtApp()
    const data = await ($api as typeof $fetch)<{ data: Task }>(`/tasks/${id}`, {
      method: 'PUT',
      body: payload,
    })
    const index = tasks.value.findIndex(t => t.id === id)
    if (index !== -1) tasks.value[index] = data.data
  }

  async function toggleTask(task: Task): Promise<void> {
    await updateTask(task.id, { status: task.is_completed ? 'pending' : 'completed' })
  }

  async function deleteTask(id: number): Promise<void> {
    const { $api } = useNuxtApp()
    await ($api as typeof $fetch)(`/tasks/${id}`, { method: 'DELETE' })
    tasks.value = tasks.value.filter(t => t.id !== id)
  }

  async function reorderTasks(ordered: { id: number, sort_order: number }[]): Promise<void> {
    const { $api } = useNuxtApp()
    await ($api as typeof $fetch)('/tasks/reorder', {
      method: 'POST',
      body: { tasks: ordered },
    })
  }

  function onSortByChange(): void {
    // Priority should default to descending (High first); others ascending
    sortDir.value = sortBy.value === 'priority' ? 'desc' : 'asc'
    fetchTasks()
  }

  function setDate(date: string): void {
    currentDate.value = date
    fetchTasks()
  }

  function prevDay(): void {
    const d = parseLocalDate(currentDate.value)
    d.setDate(d.getDate() - 1)
    setDate(toLocalDateStr(d))
  }

  function nextDay(): void {
    const d = parseLocalDate(currentDate.value)
    d.setDate(d.getDate() + 1)
    setDate(toLocalDateStr(d))
  }

  return {
    tasks,
    currentDate,
    search,
    sortBy,
    sortDir,
    loading,
    error,
    fetchTasks,
    createTask,
    updateTask,
    toggleTask,
    deleteTask,
    reorderTasks,
    onSortByChange,
    setDate,
    prevDay,
    nextDay,
  }
})
