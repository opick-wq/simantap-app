<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
      <!-- Logo & judul -->
      <div class="text-center mb-8">
        <div class="w-20 h-20 bg-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
          <span class="text-3xl">👶</span>
        </div>
        <h1 class="text-2xl font-bold text-white">SI-MANTAP</h1>
        <p class="text-blue-200 text-sm mt-1">Monitoring Pertumbuhan Balita</p>
        <p class="text-blue-300 text-xs mt-0.5">Posyandu Apel</p>
      </div>

      <!-- Form login -->
      <div class="bg-white rounded-2xl shadow-xl p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Masuk ke Sistem</h2>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="form-label">Email</label>
            <input v-model="form.email" type="email" class="form-input"
                   placeholder="email@contoh.com" autocomplete="email" required />
            <p v-if="form.errors.email" class="form-error">{{ form.errors.email }}</p>
          </div>

          <div>
            <label class="form-label">Password</label>
            <input v-model="form.password" type="password" class="form-input"
                   placeholder="••••••••" required />
            <p v-if="form.errors.password" class="form-error">{{ form.errors.password }}</p>
          </div>

          <div class="flex items-center gap-2">
            <input v-model="form.remember" type="checkbox" id="remember"
                   class="w-4 h-4 text-blue-600 rounded" />
            <label for="remember" class="text-sm text-gray-600">Ingat saya</label>
          </div>

          <button type="submit" :disabled="form.processing"
                  class="btn-primary w-full py-3 text-base">
            {{ form.processing ? 'Memuat...' : 'Masuk' }}
          </button>
        </form>

  
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  email: '', password: '', remember: false,
})

function isi(email) {
  form.email    = email
  form.password = 'password'
}

function submit() {
  form.post('/login')
}
</script>
