<template>
  <AppLayout title="Edit Akun">
    <div class="p-5 max-w-2xl space-y-5">

      <div class="flex items-center gap-3">
        <Link :href="route('users.index')" class="text-slate-500 text-sm hover:underline">← Kembali</Link>
        <h1 class="text-base font-bold text-slate-800">Edit Akun · {{ user.nama }}</h1>
      </div>

      <!-- Flash -->
      <div v-if="$page.props.flash?.success"
           class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">
        {{ $page.props.flash.success }}
      </div>

      <!-- Form Edit Data -->
      <div class="bg-white rounded-xl shadow-sm p-5 space-y-4">
        <h2 class="font-semibold text-slate-700 text-sm border-b border-slate-100 pb-2">Data Akun</h2>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Lengkap *</label>
            <input v-model="form.nama" type="text" required
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400" />
            <p v-if="form.errors.nama" class="text-red-500 text-xs mt-1">{{ form.errors.nama }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Email *</label>
            <input v-model="form.email" type="email" required
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400" />
            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Role *</label>
            <select v-model="form.role" required
                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400">
              <option v-for="r in roles" :key="r" :value="r">{{ roleLabel(r) }}</option>
            </select>
          </div>

          <!-- Kader: 1 posyandu -->
          <div v-if="form.role === 'kader'">
            <label class="block text-xs font-semibold text-slate-600 mb-1">Posyandu</label>
            <select v-model="form.posyandu_id"
                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400">
              <option :value="null">— Pilih posyandu —</option>
              <option v-for="p in posyandu" :key="p.id" :value="p.id">{{ p.nama }}</option>
            </select>
          </div>

          <!-- Nakes & Petugas: banyak posyandu -->
          <div v-if="['nakes','petugas'].includes(form.role)">
            <label class="block text-xs font-semibold text-slate-600 mb-1">
              Posyandu {{ form.role === 'petugas' ? 'Wilayah Kerja' : 'Binaan' }} *
            </label>
            <input v-model="cariPosyandu" type="text" placeholder="Cari nama posyandu atau kelurahan..."
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-purple-400" />
            <div class="space-y-1 border border-slate-200 rounded-lg p-2 max-h-52 overflow-y-auto">
              <p v-if="posyanduTerfilter.length === 0" class="text-xs text-slate-400 p-2">Tidak ditemukan.</p>
              <label v-for="p in posyanduTerfilter" :key="p.id"
                     :class="['flex items-center gap-2 p-2 rounded cursor-pointer transition',
                              form.posyandu_ids.includes(p.id) ? 'bg-emerald-50 border border-emerald-300' : 'hover:bg-slate-50']">
                <input type="checkbox" :value="p.id" v-model="form.posyandu_ids"
                       class="w-4 h-4 accent-emerald-600 shrink-0" />
                <div>
                  <span class="text-sm text-slate-700">{{ p.nama }}</span>
                  <span v-if="p.kelurahan" class="text-xs text-slate-400 ml-1">· {{ p.kelurahan }}, Kec. {{ p.kecamatan }}</span>
                </div>
              </label>
            </div>
          </div>

          <div class="flex gap-3 pt-2">
            <Link :href="route('users.index')" class="flex-1 text-center px-4 py-2 text-sm border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 transition">
              Batal
            </Link>
            <button type="submit" :disabled="form.processing"
                    class="flex-1 px-4 py-2 text-sm font-semibold bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-60 transition">
              {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Panel Reset Password -->
      <div class="bg-white rounded-xl shadow-sm p-5 space-y-4 border-l-4 border-orange-400">
        <h2 class="font-semibold text-slate-700 text-sm border-b border-slate-100 pb-2">
          🔑 Reset Password
        </h2>
        <p class="text-xs text-slate-500">
          Gunakan fitur ini untuk mengatur ulang password pengguna. Pengguna tidak perlu konfirmasi email.
        </p>

        <form @submit.prevent="submitReset" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Password Baru *</label>
            <input v-model="resetForm.password" type="password" required minlength="6"
                   placeholder="Minimal 6 karakter"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400" />
            <p v-if="resetForm.errors.password" class="text-red-500 text-xs mt-1">{{ resetForm.errors.password }}</p>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Konfirmasi Password *</label>
            <input v-model="resetForm.password_confirmation" type="password" required
                   placeholder="Ulangi password baru"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400" />
          </div>
          <button type="submit" :disabled="resetForm.processing"
                  class="w-full px-4 py-2 text-sm font-semibold bg-orange-500 text-white rounded-lg hover:bg-orange-600 disabled:opacity-60 transition">
            {{ resetForm.processing ? 'Mereset...' : 'Reset Password' }}
          </button>
        </form>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/UI/AppLayout.vue'

const props = defineProps({ user: Object, posyandu: Array, roles: Array, roleAktor: String })

const cariPosyandu = ref('')
const posyanduTerfilter = computed(() => {
  const q = cariPosyandu.value.toLowerCase()
  if (!q) return props.posyandu
  return props.posyandu.filter(p =>
    p.nama.toLowerCase().includes(q) ||
    (p.kelurahan ?? '').toLowerCase().includes(q) ||
    (p.kecamatan ?? '').toLowerCase().includes(q)
  )
})

const form = useForm({
  nama:         props.user.nama,
  email:        props.user.email,
  role:         props.user.role,
  posyandu_id:  props.user.posyandu_id,
  posyandu_ids: props.user.posyandu_ids ?? [],
})

const resetForm = useForm({
  password:              '',
  password_confirmation: '',
})

function submit() {
  form.put(route('users.update', props.user.id))
}

function submitReset() {
  resetForm.patch(route('users.reset-password', props.user.id), {
    onSuccess: () => resetForm.reset(),
  })
}

function roleLabel(r) {
  return {
    admin: 'Admin', petugas: 'Petugas Puskesmas', nakes: 'Bidan / Nakes',
    kader: 'Kader Posyandu', dinas: 'Dinas Kesehatan', orang_tua: 'Orang Tua',
  }[r] ?? r
}
</script>
