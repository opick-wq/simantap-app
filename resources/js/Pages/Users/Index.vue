<template>
  <AppLayout :title="pageTitle">
    <div class="p-4 space-y-4">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-lg font-bold text-gray-800">{{ pageTitle }}</h1>
          <p class="text-xs text-gray-500">{{ filteredUsers.length }} akun</p>
        </div>
        <Link :href="route('users.create')" class="btn-primary">+ Tambah</Link>
      </div>

      <!-- Filter role — hanya tampil kalau lebih dari 1 jenis role tersedia -->
      <div v-if="roleFilters.length > 1" class="flex gap-2 overflow-x-auto pb-1">
        <button v-for="r in roleFilters" :key="r.val"
                @click="activeRole = r.val"
                :class="['px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap border transition',
                         activeRole === r.val
                           ? 'bg-blue-600 text-white border-blue-600'
                           : 'bg-white text-gray-600 border-gray-300']">
          {{ r.label }}
        </button>
      </div>

      <!-- List -->
      <div class="space-y-2">
        <div v-if="filteredUsers.length === 0"
             class="text-center text-gray-400 py-10 text-sm">
          Belum ada akun
        </div>

        <div v-for="u in filteredUsers" :key="u.id"
             class="card flex items-center justify-between gap-3">
          <div class="flex items-center gap-3 flex-1 min-w-0">
            <div :class="['w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold shrink-0',
                           roleColor(u.role)]">
              {{ u.nama.charAt(0).toUpperCase() }}
            </div>
            <div class="min-w-0">
              <p class="font-semibold text-gray-800 truncate">{{ u.nama }}</p>
              <p class="text-xs text-gray-500 truncate">{{ u.email }}</p>
              <div class="flex gap-2 mt-0.5">
                <span :class="['text-xs px-1.5 py-0.5 rounded font-medium', roleBadge(u.role)]">
                  {{ roleLabel(u.role) }}
                </span>
                <span v-if="u.posyandu !== '-'" class="text-xs text-gray-400 truncate">
                  {{ u.posyandu }}
                </span>
              </div>
            </div>
          </div>
          <div class="flex gap-2 shrink-0">
            <Link :href="route('users.edit', u.id)" class="btn-secondary text-xs">Edit</Link>
            <button @click="hapus(u)" class="text-red-500 text-xs border border-red-200 px-2 py-1 rounded-lg hover:bg-red-50">
              Hapus
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal konfirmasi hapus -->
    <div v-if="konfirmHapus"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
      <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl">
        <p class="font-semibold text-gray-800 mb-2">Hapus akun ini?</p>
        <p class="text-sm text-gray-500 mb-4">
          Akun <strong>{{ konfirmHapus.nama }}</strong> akan dihapus permanen.
        </p>
        <div class="flex gap-3">
          <button @click="konfirmHapus = null" class="btn-secondary flex-1">Batal</button>
          <Link :href="route('users.destroy', konfirmHapus.id)" method="delete" as="button"
                class="btn-danger flex-1 text-center"
                @click="konfirmHapus = null">
            Hapus
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Components/UI/AppLayout.vue'

const props = defineProps({
  users:          Array,
  posyandu:       Array,
  roleBisaDibuat: Array,
  roleAktor:      String,
})

const activeRole = ref('semua')
const konfirmHapus = ref(null)

const pageTitle = computed(() => {
  if (props.roleAktor === 'nakes')   return 'Kader Posyandu Saya'
  if (props.roleAktor === 'petugas') return 'Daftar Nakes'
  return 'Manajemen Akun'
})

// Filter hanya role yang ada di daftar bisa dibuat
const roleFilters = computed(() => {
  const semua = [{ val: 'semua', label: 'Semua' }]
  const extras = props.roleBisaDibuat.map(r => ({ val: r, label: roleLabel(r) }))
  return extras.length > 1 ? [...semua, ...extras] : semua
})

const filteredUsers = computed(() =>
  activeRole.value === 'semua'
    ? props.users
    : props.users.filter(u => u.role === activeRole.value)
)

function hapus(user) { konfirmHapus.value = user }

function roleLabel(r) {
  return {
    admin: 'Admin', petugas: 'Petugas', nakes: 'Bidan/Nakes',
    kader: 'Kader', dinas: 'Dinas', orang_tua: 'Orang Tua',
  }[r] ?? r
}

function roleColor(role) {
  return {
    admin:     'bg-purple-100 text-purple-700',
    petugas:   'bg-blue-100 text-blue-700',
    nakes:     'bg-teal-100 text-teal-700',
    kader:     'bg-green-100 text-green-700',
    dinas:     'bg-orange-100 text-orange-700',
    orang_tua: 'bg-pink-100 text-pink-700',
  }[role] ?? 'bg-gray-100 text-gray-700'
}

function roleBadge(role) { return roleColor(role) }
</script>
