export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()
  const authToken = useCookie<string | null>('auth_token')

  const api = $fetch.create({
    baseURL: config.public.apiBase,
    headers: {
      Accept: 'application/json',
    },
    onRequest({ options }) {
      if (authToken.value) {
        options.headers = {
          ...(options.headers as Record<string, string>),
          Authorization: `Bearer ${authToken.value}`,
        }
      }
    },
    onResponseError({ response }) {
      if (response.status === 401) {
        authToken.value = null
        navigateTo('/login')
      }
    },
  })

  return {
    provide: { api },
  }
})
