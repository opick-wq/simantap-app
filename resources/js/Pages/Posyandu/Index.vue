<template>
  <AppLayout title="Data Posyandu">
    <div class="p-5 space-y-5 max-w-screen-xl mx-auto">

      <!-- Banner -->
      <div class="rounded-2xl p-5 text-white shadow-md bg-gradient-to-r from-purple-700 to-purple-900">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <h1 class="text-xl font-bold">Data Posyandu</h1>
            <p class="text-purple-200 text-sm mt-0.5">{{ posyandu.length }} posyandu terdaftar dalam sistem</p>
          </div>
          <Link :href="route('posyandu.create')"
                class="shrink-0 bg-white text-purple-700 font-semibold text-sm px-4 py-2 rounded-lg hover:bg-purple-50 transition">
            + Tambah Posyandu
          </Link>
        </div>
      </div>

      <!-- Flash -->
      <div v-if="$page.props.flash?.success"
           class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">
        {{ $page.props.flash.success }}
      </div>

      <!-- Filter & Sort -->
      <div class="bg-white rounded-xl shadow-sm p-4 space-y-3">
        <div class="flex flex-col sm:flex-row gap-3">
          <!-- Search -->
          <input v-model="search" @input="doFilter" type="text"
                 placeholder="Cari nama posyandu atau wilayah..."
                 class="flex-1 border border-slate-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400" />

          <!-- Filter Kecamatan -->
          <select v-model="filterKecamatan" @change="onKecamatanChange"
                  class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 min-w-[180px]">
            <option value="">Semua Kecamatan</option>
            <option v-for="k in kecamatans" :key="k" :value="k">Kec. {{ k }}</option>
          </select>

          <!-- Filter Kelurahan -->
          <select v-model="filterKelurahan" @change="doFilter"
                  :disabled="!filterKecamatan"
                  class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 min-w-[180px] disabled:opacity-50">
            <option value="">Semua Kelurahan</option>
            <option v-for="k in kelurahans" :key="k.id" :value="k.id">{{ k.nama }}</option>
          </select>
        </div>

        <div class="text-xs text-slate-400 text-right">{{ posyandu.length }} posyandu ditampilkan</div>
      </div>

      <!-- Tabel -->
      <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
              <th @click="setSort('nama')" class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide cursor-pointer hover:text-purple-600 select-none">
                Nama Posyandu <SortIcon col="nama" />
              </th>
              <th @click="setSort('kelurahan')" class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide cursor-pointer hover:text-purple-600 select-none">
                Kelurahan/Desa <SortIcon col="kelurahan" />
              </th>
              <th @click="setSort('kecamatan')" class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide cursor-pointer hover:text-purple-600 select-none">
                Kecamatan <SortIcon col="kecamatan" />
              </th>
              <th @click="setSort('jadwal')" class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell cursor-pointer hover:text-purple-600 select-none">
                Jadwal Rutin <SortIcon col="jadwal" />
              </th>
              <th @click="setSort('total_balita')" class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide cursor-pointer hover:text-purple-600 select-none">
                Balita <SortIcon col="total_balita" />
              </th>
              <th @click="setSort('aktif')" class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide cursor-pointer hover:text-purple-600 select-none">
                Status <SortIcon col="aktif" />
              </th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <!-- Grouped by kecamatan (tanpa filter) -->
            <template v-if="!filterKecamatan && !search">
              <template v-for="(group, kec) in grouped" :key="kec">
<tr v-for="p in group" :key="p.id" class="hover:bg-slate-50">
                  <td class="px-4 py-3 font-medium text-slate-800">{{ p.nama }}</td>
                  <td class="px-4 py-3 text-slate-600">{{ stripPrefix(p.wilayah) }}</td>
                  <td class="px-4 py-3 text-slate-500">{{ stripPrefix(p.kecamatan) }}</td>
                  <td class="px-4 py-3 text-slate-400 hidden lg:table-cell">
                    <span v-if="p.jadwal_hari">{{ formatJadwal(p) }}</span>
                    <span v-else class="text-slate-300">—</span>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <span :class="p.total_balita > 0 ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-400'"
                          class="text-xs font-semibold px-2 py-0.5 rounded-full">
                      {{ p.total_balita }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <span :class="p.aktif ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'"
                          class="text-xs font-semibold px-2 py-0.5 rounded-full">
                      {{ p.aktif ? 'Aktif' : 'Nonaktif' }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-right">
                    <Link :href="route('posyandu.edit', p.id)"
                          class="text-xs text-purple-600 font-semibold hover:underline">Edit</Link>
                  </td>
                </tr>
              </template>
            </template>

            <!-- Flat list (saat filter/search aktif) -->
            <template v-else>
              <tr v-if="posyandu.length === 0">
                <td colspan="7" class="px-4 py-10 text-center text-slate-400">Tidak ada posyandu ditemukan.</td>
              </tr>
              <tr v-for="p in posyandu" :key="p.id" class="hover:bg-slate-50">
                <td class="px-4 py-3 font-medium text-slate-800">{{ p.nama }}</td>
                <td class="px-4 py-3 text-slate-600">{{ p.wilayah }}</td>
                <td class="px-4 py-3 text-slate-500">{{ p.kecamatan }}</td>
                <td class="px-4 py-3 text-slate-400 hidden lg:table-cell">
                  <span v-if="p.jadwal_hari">{{ formatJadwal(p) }}</span>
                  <span v-else class="text-slate-300">—</span>
                </td>
                <td class="px-4 py-3 text-center">
                  <span :class="p.total_balita > 0 ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-400'"
                        class="text-xs font-semibold px-2 py-0.5 rounded-full">
                    {{ p.total_balita }}
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                  <span :class="p.aktif ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'"
                        class="text-xs font-semibold px-2 py-0.5 rounded-full">
                    {{ p.aktif ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-right">
                  <Link :href="route('posyandu.edit', p.id)"
                        class="text-xs text-purple-600 font-semibold hover:underline">Edit</Link>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, h } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/UI/AppLayout.vue'

// Inline sort icon component
const SortIcon = {
  props: ['col'],
  setup(p) {
    return () => {
      const active = sortBy.value === p.col
      const icon = active ? (sortDir.value === 'asc' ? '↑' : '↓') : '↕'
      return h('span', { class: active ? 'text-purple-600' : 'text-slate-300' }, ' ' + icon)
    }
  }
}

const props = defineProps({
  posyandu:   Array,
  kecamatans: Array,
  kelurahans: Array,
  filters:    Object,
})

function stripPrefix(str) {
  return (str ?? '').replace(/^(Kelurahan|Kecamatan|Desa)\s+/i, '')
}

function formatJadwal(p) {
  if (!p.jadwal_hari) return null
  const jam = p.jadwal_jam?.substring(0, 5) ?? '--:--'
  if (p.jadwal_minggu) return `Minggu ke-${p.jadwal_minggu}; ${p.jadwal_hari}: ${jam}`
  return `${p.jadwal_hari}: ${jam}`
}

const search          = ref(props.filters.search ?? '')
const filterKecamatan = ref(props.filters.kecamatan ?? '')
const filterKelurahan = ref(props.filters.kelurahan ?? '')
const sortBy          = ref(props.filters.sortBy ?? 'nama')
const sortDir         = ref(props.filters.sortDir ?? 'asc')

const sortOptions = [
  { value: 'nama',         label: 'Nama' },
  { value: 'total_balita', label: 'Jumlah Balita' },
]

// Grouped by kecamatan (hanya saat tanpa filter)
const grouped = computed(() => {
  const result = {}
  props.posyandu.forEach(p => {
    if (!result[p.kecamatan]) result[p.kecamatan] = []
    result[p.kecamatan].push(p)
  })
  return result
})

let filterTimer
function doFilter() {
  clearTimeout(filterTimer)
  filterTimer = setTimeout(() => {
    router.get('/posyandu', {
      search:    search.value,
      kecamatan: filterKecamatan.value,
      kelurahan: filterKelurahan.value,
      sort:      sortBy.value,
      dir:       sortDir.value,
    }, { preserveState: true, replace: true })
  }, 350)
}

function onKecamatanChange() {
  filterKelurahan.value = ''
  doFilter()
}

function setSort(col) {
  if (sortBy.value === col) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value  = col
    sortDir.value = 'asc'
  }
  doFilter()
}

</script>
