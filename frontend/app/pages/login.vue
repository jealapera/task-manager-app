<script setup lang="ts">
import { Recycle } from 'lucide-vue-next'

definePageMeta({ middleware: 'auth' })

const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const errorMsg = ref('')
const loading = ref(false)

async function handleLogin() {
  if (!email.value || !password.value) return
  loading.value = true
  errorMsg.value = ''
  try {
    await authStore.login(email.value, password.value)
    await navigateTo('/')
  }
  catch {
    errorMsg.value = 'Invalid email or password.'
  }
  finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-white flex items-center justify-center px-4">
    <div class="w-full max-w-sm">
      <!-- Logo -->
      <div class="flex justify-center mb-8">
        <Recycle :size="40" stroke-width="2.5" class="text-gray-900" />
      </div>

      <!-- Card -->
      <div class="border border-gray-200 rounded-2xl px-8 py-8">
        <h1 class="text-2xl font-bold text-center text-gray-900 mb-1">
          Sign In
        </h1>
        <p class="text-sm text-gray-400 text-center mb-7">
          Login to continue using this app
        </p>

        <!-- Error -->
        <p v-if="errorMsg" class="text-red-500 text-sm text-center mb-4">
          {{ errorMsg }}
        </p>

        <form class="space-y-4" @submit.prevent="handleLogin">
          <!-- Email -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
              v-model="email"
              type="email"
              autocomplete="email"
              required
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-300 transition"
            >
          </div>

          <!-- Password -->
          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="text-sm font-medium text-gray-700">Password</label>
              <span class="text-sm text-gray-400 cursor-default">Forgot your password?</span>
            </div>
            <input
              v-model="password"
              type="password"
              autocomplete="current-password"
              required
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-300 transition"
            >
          </div>

          <!-- Submit -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-gray-900 hover:bg-gray-700 disabled:opacity-50 text-white font-medium rounded-full py-2.5 text-sm transition mt-2"
          >
            {{ loading ? 'Signing in…' : 'Login' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
