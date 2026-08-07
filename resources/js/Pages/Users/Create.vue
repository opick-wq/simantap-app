<template>
  <AppLayout :title="pageTitle">
    <div class="p-4 max-w-lg">
      <div class="flex items-center gap-3 mb-4">
        <Link :href="route('users.index')" class="text-gray-500 text-sm">← Kembali</Link>
        <h1 class="text-base font-bold text-gray-800">{{ pageTitle }}</h1>
      </div>

      <!-- Info konteks: Nakes buat kader -->
      <div v-if="roleAktor === 'nakes'" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
        Anda mendaftarkan kader untuk posyandu binaan Anda.
      </div>
      <div v-if="roleAktor === 'petugas'" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
        Anda mendaftarkan Bidan / Nakes baru.
      </div>

      <form @submit.prevent="submit" class="card space-y-4">
        <div>
          <label class="form-label">Nama Lengkap *</label>
          <input v-model="form.nama" type="text" class="form-input"
                 placeholder="Siti Rahayu" required />
          <p v-if="form.errors.nama" class="form-error">{{ form.errors.nama }}</p>
        </div>

        <div>
          <label class="form-label">Email *</label>
          <input v-model="form.email" type="email" class="form-input"
                 placeholder="siti@contoh.com" required />
          <p v-if="form.errors.email" class="form-error">{{ form.errors.email }}</p>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 text-sm text-blue-700">
          🔑 Password default: <strong>123456</strong> — pengguna dapat menggantinya setelah login melalui menu <em>Ganti Password</em>.
        </div>

        <!-- Role: jika hanya 1 pilihan (nakes/petugas), langsung set dan sembunyikan -->
        <div v-if="roles.length > 1">
          <label class="form-label">Role *</label>
          <select v-model="form.role" class="form-input" required>
            <option value="">— Pilih role —</option>
            <option v-for="r in roles" :key="r" :value="r">{{ roleLabel(r) }}</option>
          </select>
          <p v-if="form.errors.role" class="form-error">{{ form.errors.role }}</p>
        </div>
        <!-- Jika hanya 1 role yang bisa dibuat, tampilkan as readonly badge -->
        <div v-else>
          <label class="form-label">Role</label>
          <div class="form-input bg-gray-50 text-gray-600 cursor-not-allowed">
            {{ roleLabel(roles[0]) }}
          </div>
        </div>

        <!-- Kader: pilih 1 posyandu -->
        <!-- Kader: 1 posyandu -->
        <div v-if="form.role === 'kader'">
          <label class="form-label">Posyandu <span class="text-red-500">*</span></label>
          <select v-model="form.posyandu_id" class="form-input" required>
            <option :value="null">— Pilih posyandu —</option>
            <option v-for="p in posyandu" :key="p.id" :value="p.id">{{ p.nama }}</option>
          </select>
          <p v-if="form.errors.posyandu_id" class="form-error">{{ form.errors.posyandu_id }}</p>
        </div>

        <!-- Nakes & Petugas: pilih banyak posyandu -->
        <div v-if="['nakes','petugas'].includes(form.role)">
          <label class="form-label">
            Posyandu {{ form.role === 'petugas' ? 'Wilayah Kerja' : 'Binaan' }}
            <span class="text-red-500">*</span>
          </label>
          <input v-model="cariPosyandu" type="text" placeholder="Cari nama posyandu atau kelurahan..."
                 class="form-input mb-2 text-sm" />
          <div class="space-y-1 border rounded-lg p-2 max-h-52 overflow-y-auto">
            <p v-if="posyanduTerfilter.length === 0" class="text-xs text-gray-400 p-2">Tidak ditemukan.</p>
            <label v-for="p in posyanduTerfilter" :key="p.id"
                   :class="['flex items-center gap-2 p-2 rounded cursor-pointer transition',
                            form.posyandu_ids.includes(p.id) ? 'bg-emerald-50 border border-emerald-300' : 'hover:bg-gray-50']">
              <input type="checkbox" :value="p.id" v-model="form.posyandu_ids"
                     class="w-4 h-4 accent-emerald-600 shrink-0" />
              <div>
                <span class="text-sm text-gray-700">{{ p.nama }}</span>
                <span v-if="p.kelurahan" class="text-xs text-gray-400 ml-1">· {{ p.kelurahan }}, Kec. {{ p.kecamatan }}</span>
              </div>
            </label>
          </div>
          <p v-if="form.errors.posyandu_ids" class="form-error mt-1">{{ form.errors.posyandu_ids }}</p>
        </div>

        <div class="flex gap-3 pt-2">
          <Link :href="route('users.index')" class="btn-secondary flex-1 text-center">Batal</Link>
          <button type="submit" :disabled="form.processing" class="btn-primary flex-1">
            {{ form.processing ? 'Menyimpan...' : 'Buat Akun' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/UI/AppLayout.vue'

const props = defineProps({
  posyandu:   Array,
  roles:      Array,
  roleAktor:  String,
})

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
  nama:         '',
  email:        '',

  role:         props.roles.length === 1 ? props.roles[0] : '',
  posyandu_id:  null,
  posyandu_ids: [],
})

const pageTitle = computed(() => {
  if (props.roleAktor === 'nakes')   return 'Daftarkan Kader'
  if (props.roleAktor === 'petugas') return 'Daftarkan Nakes'
  return 'Tambah Akun'
})

function submit() {
  form.post(route('users.store'))
}

function roleLabel(r) {
  return {
    admin: 'Admin', petugas: 'Petugas Kesehatan', nakes: 'Bidan / Nakes',
    kader: 'Kader Posyandu', dinas: 'Dinas Kesehatan', orang_tua: 'Orang Tua',
  }[r] ?? r
}
</script>
