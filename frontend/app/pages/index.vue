<script setup lang="ts">
import { LogOut, Menu, Recycle, X } from 'lucide-vue-next'

definePageMeta({ middleware: 'auth' })

const authStore = useAuthStore()
const taskStore = useTaskStore()

// ── User menu dropdown ───────────────────────────────────────────────────────
const showUserMenu = ref(false)
const userMenuRef = ref<HTMLElement | null>(null)

function handleClickOutside(e: MouseEvent) {
  if (userMenuRef.value && !userMenuRef.value.contains(e.target as Node)) {
    showUserMenu.value = false
  }
}
onMounted(() => document.addEventListener('click', handleClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside))

// ── Mobile sidebar ────────────────────────────────────────────────────────────
const showSidebar = ref(false)

function selectDate(value: string) {
  taskStore.setDate(value)
  showSidebar.value = false // close drawer after picking a date on mobile
}

// ── Sidebar date list ────────────────────────────────────────────────────────
interface DateItem { value: string, label: string }
interface DateGroup { label: string | null, dates: DateItem[] }

const dateGroups = computed<DateGroup[]>(() => {
  const today = new Date()
  today.setHours(0, 0, 0, 0)

  const fmt = (d: Date) =>
    `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`

  const fmtLong = (d: Date) =>
    d.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' })

  const all: (DateItem & { raw: Date })[] = []
  for (let i = 0; i < 28; i++) {
    const d = new Date(today)
    d.setDate(today.getDate() - i)
    all.push({
      value: fmt(d),
      label: i === 0 ? 'Today' : i === 1 ? 'Yesterday' : fmtLong(d),
      raw: d,
    })
  }

  const groups: DateGroup[] = [{ label: null, dates: [all[0], all[1]] }]

  const ordinals = ['1st', '2nd', '3rd', '4th', '5th']
  function weekLabel(d: Date): string {
    const msPerWeek = 7 * 24 * 60 * 60 * 1000
    const diff = Math.floor((today.getTime() - d.getTime()) / msPerWeek)
    if (diff < 1) return 'This week'
    if (diff < 2) return 'Last week'
    const weekNum = Math.ceil(d.getDate() / 7)
    return `${ordinals[weekNum - 1] ?? ''} Week of ${d.toLocaleDateString('en-US', { month: 'long' })}`
  }

  let currentLabel = ''
  let currentDates: DateItem[] = []
  for (let i = 2; i < all.length; i++) {
    const label = weekLabel(all[i].raw)
    if (label !== currentLabel) {
      if (currentDates.length) groups.push({ label: currentLabel, dates: currentDates })
      currentLabel = label
      currentDates = []
    }
    currentDates.push({ value: all[i].value, label: all[i].label })
  }
  if (currentDates.length) groups.push({ label: currentLabel, dates: currentDates })

  return groups
})

// ── Current date label (for mobile header) ───────────────────────────────────
const currentDateLabel = computed(() => {
  const fmt = (d: Date) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
  const today = new Date()
  const yesterday = new Date(today)
  yesterday.setDate(today.getDate() - 1)
  if (taskStore.currentDate === fmt(today)) return 'Today'
  if (taskStore.currentDate === fmt(yesterday)) return 'Yesterday'
  const d = new Date(taskStore.currentDate + 'T00:00:00')
  return d.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' })
})

// ── User initial ─────────────────────────────────────────────────────────────
const userInitial = computed(() =>
  (authStore.user?.name ?? authStore.user?.email ?? 'U')[0].toUpperCase(),
)

onMounted(() => taskStore.fetchTasks())
</script>

<template>
  <div class="flex flex-col h-screen bg-white overflow-hidden">
    <!-- ── Header ── -->
    <header class="flex items-center gap-2 px-4 py-3 border-b border-gray-100 flex-shrink-0">
      <!-- Mobile: sidebar toggle -->
      <button
        class="sm:hidden flex-shrink-0 p-1 text-gray-600 hover:text-gray-900 transition"
        @click="showSidebar = true"
      >
        <Menu :size="20" />
      </button>

      <!-- Logo (hidden on mobile to save space) -->
      <Recycle :size="26" stroke-width="2.5" class="hidden sm:block text-gray-900 flex-shrink-0" />

      <!-- Mobile: current date label -->
      <span class="sm:hidden text-sm font-semibold text-gray-800 truncate flex-shrink-0 max-w-[120px]">
        {{ currentDateLabel }}
      </span>

      <!-- Search -->
      <div class="flex-1 flex justify-center">
        <UiSearchBar />
      </div>

      <!-- Avatar / User menu -->
      <div ref="userMenuRef" class="relative flex-shrink-0">
        <button
          class="w-8 h-8 rounded-full bg-gray-900 flex items-center justify-center text-white text-xs font-semibold hover:bg-gray-700 transition"
          @click.stop="showUserMenu = !showUserMenu"
        >
          {{ userInitial }}
        </button>

        <!-- Dropdown -->
        <div
          v-if="showUserMenu"
          class="absolute right-0 top-10 w-52 bg-white border border-gray-200 rounded-xl shadow-lg py-2 z-50"
        >
          <div class="px-4 py-2 border-b border-gray-100">
            <p class="text-sm font-semibold text-gray-900 truncate">
              {{ authStore.user?.name ?? '' }}
            </p>
            <p class="text-xs text-gray-400 truncate">
              {{ authStore.user?.email ?? '' }}
            </p>
          </div>
          <button
            class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition mt-1"
            @click="authStore.logout()"
          >
            <LogOut :size="14" class="text-gray-500" />
            Sign out
          </button>
        </div>
      </div>
    </header>

    <!-- ── Body ── -->
    <div class="flex flex-1 overflow-hidden relative">

      <!-- ── Desktop sidebar (always visible on sm+) ── -->
      <aside class="hidden sm:flex sm:flex-col w-52 flex-shrink-0 overflow-y-auto px-3 py-4 border-r border-gray-100">
        <template v-for="(group, gi) in dateGroups" :key="gi">
          <p v-if="group.label" class="text-xs text-gray-400 px-3 mt-4 mb-1">
            {{ group.label }}
          </p>
          <button
            v-for="date in group.dates"
            :key="date.value"
            class="w-full text-left px-3 py-1.5 rounded-full text-sm mb-0.5 transition"
            :class="taskStore.currentDate === date.value
              ? 'bg-gray-900 text-white font-medium'
              : 'text-gray-700 hover:bg-gray-100'"
            @click="taskStore.setDate(date.value)"
          >
            {{ date.label }}
          </button>
        </template>
      </aside>

      <!-- ── Mobile: backdrop ── -->
      <Transition name="fade">
        <div
          v-if="showSidebar"
          class="sm:hidden fixed inset-0 bg-black/40 z-30"
          @click="showSidebar = false"
        />
      </Transition>

      <!-- ── Mobile: slide-over sidebar ── -->
      <Transition name="slide">
        <aside
          v-if="showSidebar"
          class="sm:hidden fixed inset-y-0 left-0 z-40 w-64 bg-white shadow-xl flex flex-col overflow-y-auto px-3 py-4"
        >
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide px-3">Browse by date</span>
            <button class="p-1 text-gray-400 hover:text-gray-700" @click="showSidebar = false">
              <X :size="16" />
            </button>
          </div>

          <template v-for="(group, gi) in dateGroups" :key="gi">
            <p v-if="group.label" class="text-xs text-gray-400 px-3 mt-4 mb-1">
              {{ group.label }}
            </p>
            <button
              v-for="date in group.dates"
              :key="date.value"
              class="w-full text-left px-3 py-1.5 rounded-full text-sm mb-0.5 transition"
              :class="taskStore.currentDate === date.value
                ? 'bg-gray-900 text-white font-medium'
                : 'text-gray-700 hover:bg-gray-100'"
              @click="selectDate(date.value)"
            >
              {{ date.label }}
            </button>
          </template>
        </aside>
      </Transition>

      <!-- ── Content ── -->
      <main class="flex-1 overflow-y-auto flex flex-col min-w-0">
        <!-- Loading -->
        <div v-if="taskStore.loading" class="flex-1 flex items-center justify-center text-gray-400 text-sm">
          Loading…
        </div>

        <!-- Error -->
        <div v-else-if="taskStore.error" class="flex-1 flex flex-col items-center justify-center gap-3 text-sm px-4">
          <p class="text-red-500 text-center">{{ taskStore.error }}</p>
          <button class="text-gray-500 underline text-xs" @click="taskStore.fetchTasks()">
            Retry
          </button>
        </div>

        <!-- Empty state -->
        <template v-else-if="taskStore.tasks.length === 0">
          <div class="flex-1 flex flex-col items-center justify-center px-4 sm:px-8">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-5 text-center">
              What do you have in mind?
            </h2>
            <TaskInput :centered="true" class="w-full max-w-lg" />
          </div>
        </template>

        <!-- Task list -->
        <template v-else>
          <div class="flex-1 px-4 sm:px-8 pt-5 pb-2">
            <!-- Date nav + Sort control -->
            <div class="flex items-center justify-between mb-4">
              <UiDateNavigator />

              <!-- Sort -->
              <div class="flex items-center gap-2">
                <span class="text-xs text-gray-400">Sort by</span>
                <select
                  v-model="taskStore.sortBy"
                  class="text-xs border border-gray-200 rounded-lg px-2 py-1 text-gray-600 focus:outline-none focus:ring-1 focus:ring-gray-300"
                  @change="taskStore.onSortByChange()"
                >
                  <option value="sort_order">Custom order</option>
                  <option value="priority">Priority</option>
                  <option value="created_at">Date added</option>
                </select>
              </div>
            </div>

            <TaskList />
          </div>

          <!-- Add task input pinned to bottom -->
          <div class="px-4 sm:px-8 pb-5 flex-shrink-0">
            <TaskInput :centered="false" />
          </div>
        </template>
      </main>
    </div>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
  transition: transform 0.25s ease;
}
.slide-enter-from,
.slide-leave-to {
  transform: translateX(-100%);
}
</style>
