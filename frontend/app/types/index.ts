export interface User {
  id: number
  name: string
  email: string
}

export interface Task {
  id: number
  statement: string
  status: 'pending' | 'completed'
  is_completed: boolean
  priority: 0 | 1 | 2 | 3
  task_date: string
  sort_order: number
  created_at: string
  updated_at: string
}

export interface TasksResponse {
  data: Task[]
  links: {
    first: string | null
    last: string | null
    prev: string | null
    next: string | null
  }
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

export interface LoginResponse {
  token: string
  user: User
}

export type Priority = 0 | 1 | 2 | 3

export const PRIORITY_LABELS: Record<Priority, string> = {
  0: 'None',
  1: 'Low',
  2: 'Medium',
  3: 'High',
}

export const PRIORITY_COLORS: Record<Priority, string> = {
  0: '',
  1: 'bg-blue-100 text-blue-600',
  2: 'bg-yellow-100 text-yellow-700',
  3: 'bg-red-100 text-red-600',
}
