import type { LoginResponse, User } from '~/types'

export const useAuthStore = defineStore('auth', () => {
  const cookieOpts = { maxAge: 60 * 60 * 24 * 7 }
  const token = useCookie<string | null>('auth_token', cookieOpts)
  const userCookie = useCookie<User | null>('auth_user', cookieOpts)
  const user = ref<User | null>(userCookie.value)

  const isAuthenticated = computed(() => !!token.value)

  async function login(email: string, password: string): Promise<void> {
    const { $api } = useNuxtApp()
    const data = await ($api as typeof $fetch)<LoginResponse>('/login', {
      method: 'POST',
      body: { email, password },
    })
    token.value = data.token
    user.value = data.user
    userCookie.value = data.user
  }

  async function logout(): Promise<void> {
    const { $api } = useNuxtApp()
    try {
      await ($api as typeof $fetch)('/logout', { method: 'POST' })
    }
    finally {
      token.value = null
      user.value = null
      userCookie.value = null
      await navigateTo('/login')
    }
  }

  return { token, user, isAuthenticated, login, logout }
})
