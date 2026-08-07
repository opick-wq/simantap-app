<template>
  <AppLayout title="Ganti Password">
    <div class="p-5 max-w-md">

      <div class="flex items-center gap-3 mb-5">
        <h1 class="text-base font-bold text-slate-800">Ganti Password</h1>
      </div>

      <!-- Flash -->
      <div v-if="$page.props.flash?.success"
           class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3 mb-4">
        {{ $page.props.flash.success }}
      </div>

      <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
        <p class="text-xs text-slate-500 mb-4">
          Masukkan password lama Anda untuk verifikasi, lalu tentukan password baru.
        </p>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Password Lama *</label>
            <input v-model="form.password_lama" type="password" required
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
            <p v-if="form.errors.password_lama" class="text-red-500 text-xs mt-1">{{ form.errors.password_lama }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Password Baru *</label>
            <input v-model="form.password" type="password" required minlength="6"
                   placeholder="Minimal 6 karakter"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
            <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Konfirmasi Password Baru *</label>
            <input v-model="form.password_confirmation" type="password" required
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>

          <button type="submit" :disabled="form.processing"
                  class="w-full px-4 py-2 text-sm font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-60 transition">
            {{ form.processing ? 'Menyimpan...' : 'Ubah Password' }}
          </button>
        </form>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/UI/AppLayout.vue'

const form = useForm({
  password_lama:         '',
  password:              '',
  password_confirmation: '',
})

function submit() {
  form.patch('/akun/ganti-password', {
    onSuccess: () => form.reset(),
  })
}
</script>
