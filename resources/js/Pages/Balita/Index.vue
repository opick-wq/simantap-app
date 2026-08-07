<template>
  <AppLayout title="Data Balita">
    <div class="p-4 space-y-3">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-lg font-bold text-gray-800">Data Balita</h1>
          <p class="text-xs text-gray-500">{{ balita.total }} balita ditemukan</p>
        </div>
        <div class="flex gap-2">
          <Link :href="route('balita.import.page')" class="btn-secondary px-3 py-1.5 text-sm">⬆ Import</Link>
          <Link :href="route('balita.create')" class="btn-primary px-3 py-1.5 text-sm">+ Daftar</Link>
        </div>
      </div>

      <!-- Label filter aktif -->
      <div v-if="judulFilter" class="flex items-center gap-2">
        <span class="text-sm font-semibold text-gray-700">{{ judulFilter }}</span>
        <button @click="resetFilter"
                class="text-xs text-blue-600 underline">Tampilkan semua</button>
      </div>

      <!-- Search -->
      <input v-model="search" type="search" placeholder="Cari nama balita…"
             class="form-input" @input="applySearch" />

      <!-- Filter Posyandu (hanya jika akses > 1 posyandu) -->
      <div v-if="posyandu.length > 1">
        <p class="text-xs text-gray-400 mb-1">Filter Posyandu</p>
        <div class="flex gap-2 overflow-x-auto pb-1">
          <button @click="setPosyandu('')"
                  :class="['px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap border transition',
                           !activePosyandu ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-300']">
            Semua
          </button>
          <button v-for="p in posyandu" :key="p.id"
                  @click="setPosyandu(p.id)"
                  :class="['px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap border transition',
                           activePosyandu == p.id ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-300']">
            {{ p.nama }}
          </button>
        </div>
      </div>

      <!-- Filter EWS -->
      <div>
        <p class="text-xs text-gray-400 mb-1">Filter EWS <span v-if="activeEws.length" class="text-blue-500">({{ activeEws.length }} dipilih)</span></p>
        <div class="flex gap-2 overflow-x-auto pb-1">
          <button v-for="f in ewsOptions" :key="f.val"
                  @click="toggleEws(f.val)"
                  :class="['px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap border transition',
                           activeEws.includes(f.val) ? f.active : 'bg-white text-gray-600 border-gray-300']">
            {{ f.label }}
          </button>
        </div>
      </div>

      <!-- Filter Status Gizi -->
      <div>
        <p class="text-xs text-gray-400 mb-1">Filter Status Gizi <span v-if="activeGizi.length" class="text-blue-500">({{ activeGizi.length }} dipilih)</span></p>
        <div class="flex gap-2 overflow-x-auto pb-1">
          <button v-for="f in giziOptions" :key="f.val"
                  @click="toggleGizi(f.val)"
                  :class="['px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap border transition',
                           activeGizi.includes(f.val) ? f.active : 'bg-white text-gray-600 border-gray-300']">
            {{ f.label }}
          </button>
        </div>
      </div>

      <!-- Filter Stunting -->
      <div>
        <p class="text-xs text-gray-400 mb-1">Filter Stunting <span v-if="activeStunting.length" class="text-blue-500">({{ activeStunting.length }} dipilih)</span></p>
        <div class="flex gap-2 overflow-x-auto pb-1">
          <button v-for="f in stuntingOptions" :key="f.val"
                  @click="toggleStunting(f.val)"
                  :class="['px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap border transition',
                           activeStunting.includes(f.val) ? f.active : 'bg-white text-gray-600 border-gray-300']">
            {{ f.label }}
          </button>
        </div>
      </div>
    </div>

    <!-- Tabel -->
    <div class="pb-20">
      <div v-if="balita.data.length === 0" class="text-center text-gray-400 py-12 px-4">
        <p class="text-4xl mb-2">🔍</p>
        <p>Tidak ada balita ditemukan</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm border-collapse min-w-[900px]">
          <thead>
            <tr class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wide">
              <th class="px-3 py-2.5 text-center font-semibold w-10">No</th>
              <th class="px-3 py-2.5 text-left font-semibold">
                <button @click="sortBy('nama')" class="flex items-center gap-1 hover:text-blue-600">
                  Nama <SortIcon col="nama" />
                </button>
              </th>
              <th class="px-3 py-2.5 text-left font-semibold">NIK</th>
              <th class="px-3 py-2.5 text-center font-semibold">
                <button @click="sortBy('tanggal_lahir')" class="flex items-center gap-1 hover:text-blue-600 mx-auto">
                  Usia <SortIcon col="tanggal_lahir" />
                </button>
              </th>
              <th class="px-3 py-2.5 text-center font-semibold">
                <button @click="sortBy('bb')" class="flex items-center gap-1 hover:text-blue-600 mx-auto">
                  BB (kg) <SortIcon col="bb" />
                </button>
              </th>
              <th class="px-3 py-2.5 text-center font-semibold">
                <button @click="sortBy('tb')" class="flex items-center gap-1 hover:text-blue-600 mx-auto">
                  TB (cm) <SortIcon col="tb" />
                </button>
              </th>
              <th class="px-3 py-2.5 text-center font-semibold">Status Gizi</th>
              <th class="px-3 py-2.5 text-center font-semibold">Status Stunting</th>
              <th class="px-3 py-2.5 text-center font-semibold">Status Wasting</th>
              <th class="px-3 py-2.5 text-center font-semibold">IMT/U</th>
              <th class="px-3 py-2.5 text-left font-semibold">Posyandu</th>
              <th class="px-3 py-2.5 text-center font-semibold">
                <button @click="sortBy('tanggal_ukur')" class="flex items-center gap-1 hover:text-blue-600 mx-auto">
                  Tgl Ukur <SortIcon col="tanggal_ukur" />
                </button>
              </th>
              <th class="px-3 py-2.5 text-center font-semibold">EWS</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(b, index) in balita.data" :key="b.id"
                @click="$inertia.visit(route('balita.show', b.id))"
                class="border-b border-gray-100 hover:bg-blue-50 cursor-pointer transition-colors">
              
              <!-- No -->
              <td class="px-3 py-2.5 text-center text-xs text-gray-400">
                {{ (balita.current_page - 1) * balita.per_page + index + 1 }}
              </td>
              
              <!-- Nama -->
              <td class="px-3 py-2.5">
                <div class="flex items-center gap-2">
                  <span class="shrink-0">{{ b.jenis_kelamin === 'L' ? '👦' : '👧' }}</span>
                  <span class="font-semibold text-gray-800 truncate max-w-[140px]">{{ b.nama }}</span>
                </div>
                <p class="text-xs text-gray-400 ml-6">{{ b.nama_ibu }}</p>
              </td>
              
              <!-- NIK -->
              <td class="px-3 py-2.5 text-xs text-gray-500 font-mono">
                {{ b.nik_balita ?? '-' }}
              </td>
              
              <!-- Usia -->
              <!-- Usia -->
              <td class="px-3 py-2.5 text-center text-xs text-gray-600">
                {{ b.umur_bulan != null ? Math.floor(b.umur_bulan) + ' bln' : b.umur_lengkap }}
              </td>
              
              <!-- BB -->
              <td class="px-3 py-2.5 text-center font-semibold text-blue-700">
                {{ b.bb_terakhir ?? '-' }}
              </td>
              
              <!-- TB -->
              <td class="px-3 py-2.5 text-center font-semibold text-green-700">
                {{ b.tb_terakhir ?? '-' }}
              </td>
              
              <!-- Status Gizi -->
              <td class="px-3 py-2.5 text-center">
                <span v-if="b.status_gizi"
                      :class="['text-xs px-2 py-0.5 rounded-full font-medium', giziClass(b.status_gizi)]">
                  {{ giziLabel(b.status_gizi) }}
                </span>
                <span v-else class="text-gray-300 text-xs">—</span>
              </td>
              
              <!-- Status Stunting (Diperbaiki: Ditambah v-if agar tidak muncul badge '—') -->
              <td class="px-3 py-2.5 text-center">
                <span v-if="b.status_stunting" 
                      :class="['text-xs px-2 py-0.5 rounded-full font-medium', stuntingClass(b.status_stunting)]">
                  {{ stuntingLabel(b.status_stunting) }}
                </span>
                <span v-else class="text-gray-300 text-xs">—</span>
              </td>
              
              <!-- Status Wasting -->
              <td class="px-3 py-2.5 text-center">
                <span v-if="b.status_wasting"
                      :class="['text-xs px-2 py-0.5 rounded-full font-medium', wastingClass(b.status_wasting)]">
                  {{ wastingLabel(b.status_wasting) }}
                </span>
                <span v-else class="text-gray-300 text-xs">—</span>
              </td>
              
              <!-- IMT/U -->
              <td class="px-3 py-2.5 text-center">
                <span v-if="b.status_imt_u"
                      :class="['text-xs px-2 py-0.5 rounded-full font-medium', wastingClass(b.status_imt_u)]">
                  {{ imtLabel(b.status_imt_u) }}
                </span>
                <span v-else class="text-gray-300 text-xs">—</span>
              </td>
              
              <!-- Posyandu -->
              <td class="px-3 py-2.5 text-xs text-gray-600 max-w-[120px] truncate">
                {{ b.posyandu_nama }}
              </td>
              
              <!-- Tgl Ukur -->
              <td class="px-3 py-2.5 text-center text-xs text-gray-500">
                {{ b.tanggal_ukur ?? '—' }}
              </td>
              
              <!-- EWS (Diperbaiki: Ditambah v-if agar rapi) -->
              <td class="px-3 py-2.5 text-center">
                <span v-if="b.flag_ews" 
                      :class="['text-xs px-2 py-0.5 rounded-full font-semibold', ewsBadge(b.flag_ews)]">
                  {{ b.flag_ews }}
                </span>
                <span v-else class="text-gray-300 text-xs">—</span>
              </td>
              
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="balita.last_page > 1" class="flex justify-center gap-2 py-4 px-4">
        <Link v-if="balita.prev_page_url" :href="balita.prev_page_url" class="px-3 py-1 text-sm border rounded">‹ Prev</Link>
        <span class="px-3 py-1 text-sm text-gray-500">{{ balita.current_page }} / {{ balita.last_page }}</span>
        <Link v-if="balita.next_page_url" :href="balita.next_page_url" class="px-3 py-1 text-sm border rounded">Next ›</Link>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, h } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/UI/AppLayout.vue'

const props = defineProps({ balita: Object, filters: Object, posyandu: Array })

const toArray = v => !v ? [] : (Array.isArray(v) ? v : [v])

const search          = ref(props.filters?.search ?? '')
const currentSort     = ref(props.filters?.sort ?? 'nama')
const currentDir      = ref(props.filters?.dir  ?? 'asc')
const activePosyandu  = ref(props.filters?.posyandu_id ?? '')
const activeEws       = ref(toArray(props.filters?.flag_ews))
const activeGizi      = ref(toArray(props.filters?.status_gizi))
const activeStunting  = ref(toArray(props.filters?.status_stunting))
const belumDiukur     = ref(props.filters?.belum_diukur === '1')

const ewsOptions = [
  { val: 'MERAH',  label: '🔴 Merah',   active: 'bg-red-600 text-white border-red-600' },
  { val: 'KUNING', label: '🟡 Kuning',  active: 'bg-yellow-500 text-white border-yellow-500' },
  { val: 'HIJAU',  label: '🟢 Hijau',   active: 'bg-green-600 text-white border-green-600' },
]

const giziOptions = [
  { val: 'GIZI_BURUK',   label: 'BB Sangat Kurang', active: 'bg-red-600 text-white border-red-600' },
  { val: 'GIZI_KURANG',  label: 'BB Kurang',        active: 'bg-orange-500 text-white border-orange-500' },
  { val: 'GIZI_BAIK',    label: 'BB Normal',        active: 'bg-green-600 text-white border-green-600' },
  { val: 'RISIKO_LEBIH', label: 'Risiko BB Lebih',  active: 'bg-yellow-500 text-white border-yellow-500' },
]

const stuntingOptions = [
  { val: 'SANGAT_PENDEK', label: 'Sangat Pendek', active: 'bg-red-600 text-white border-red-600' },
  { val: 'PENDEK',        label: 'Pendek',        active: 'bg-orange-500 text-white border-orange-500' },
  { val: 'NORMAL',        label: 'Normal',        active: 'bg-green-600 text-white border-green-600' },
  { val: 'TINGGI',        label: 'Tinggi',        active: 'bg-blue-600 text-white border-blue-600' },
]

const judulFilter = computed(() => {
  if (belumDiukur.value)         return 'Belum Diukur Bulan Ini'
  if (activeGizi.value.length)   return activeGizi.value.map(v => giziOptions.find(f => f.val === v)?.label).join(', ')
  if (activeStunting.value.length) return activeStunting.value.map(v => stuntingOptions.find(f => f.val === v)?.label).join(', ')
  if (activeEws.value.length)    return 'EWS: ' + activeEws.value.join(', ')
  return ''
})

function go() {
  router.get(route('balita.index'), {
    search:              search.value || undefined,
    posyandu_id:         activePosyandu.value || undefined,
    'flag_ews[]':        activeEws.value.length ? activeEws.value : undefined,
    'status_gizi[]':     activeGizi.value.length ? activeGizi.value : undefined,
    'status_stunting[]': activeStunting.value.length ? activeStunting.value : undefined,
    belum_diukur:        belumDiukur.value ? '1' : undefined,
    sort:                currentSort.value !== 'nama' ? currentSort.value : undefined,
    dir:                 currentDir.value  !== 'asc'  ? currentDir.value  : undefined,
  }, { preserveState: true })
}

function sortBy(col) {
  if (currentSort.value === col) {
    currentDir.value = currentDir.value === 'asc' ? 'desc' : 'asc'
  } else {
    currentSort.value = col
    currentDir.value  = 'asc'
  }
  go()
}

let timer = null
function applySearch() {
  clearTimeout(timer)
  timer = setTimeout(go, 400)
}

function toggleEws(val) {
  const i = activeEws.value.indexOf(val)
  i >= 0 ? activeEws.value.splice(i, 1) : activeEws.value.push(val)
  go()
}
function toggleGizi(val) {
  const i = activeGizi.value.indexOf(val)
  i >= 0 ? activeGizi.value.splice(i, 1) : activeGizi.value.push(val)
  go()
}
function toggleStunting(val) {
  const i = activeStunting.value.indexOf(val)
  i >= 0 ? activeStunting.value.splice(i, 1) : activeStunting.value.push(val)
  go()
}
function setPosyandu(id) {
  activePosyandu.value = id
  go()
}
function resetFilter() {
  activePosyandu.value = ''
  activeEws.value = []; activeGizi.value = []; activeStunting.value = []
  belumDiukur.value = false; search.value = ''
  go()
}

function ewsBadge(flag) {
  return { MERAH: 'bg-red-100 text-red-700', KUNING: 'bg-yellow-100 text-yellow-700',
           HIJAU:  'bg-green-100 text-green-700' }[flag] ?? 'bg-gray-100 text-gray-500'
}
function giziClass(s) {
  return {
    GIZI_BURUK:   'bg-red-100 text-red-700',
    GIZI_KURANG:  'bg-orange-100 text-orange-700',
    GIZI_BAIK:    'bg-green-100 text-green-700',
    RISIKO_LEBIH: 'bg-yellow-100 text-yellow-700',
  }[s] ?? 'bg-gray-100 text-gray-600'
}
function giziLabel(s) {
  return {
    GIZI_BURUK:   'BB Sangat Kurang',
    GIZI_KURANG:  'BB Kurang',
    GIZI_BAIK:    'BB Normal',
    RISIKO_LEBIH: 'Risiko BB Lebih',
  }[s] ?? (s ?? '—')
}
function stuntingClass(s) {
  return {
    SANGAT_PENDEK: 'bg-red-100 text-red-700',
    PENDEK:        'bg-orange-100 text-orange-700',
    NORMAL:        'bg-green-100 text-green-700',
    TINGGI:        'bg-blue-100 text-blue-700',
  }[s] ?? 'bg-gray-100 text-gray-400'
}
function stuntingLabel(s) {
  return {
    SANGAT_PENDEK: 'Sangat Pendek',
    PENDEK:        'Pendek',
    NORMAL:        'Normal',
    TINGGI:        'Tinggi',
  }[s] ?? (s ?? '—')
}
function wastingClass(s) {
  return {
    SANGAT_KURUS:   'bg-red-100 text-red-700',
    KURUS:          'bg-orange-100 text-orange-700',
    NORMAL:         'bg-green-100 text-green-700',
    BERISIKO_GEMUK: 'bg-yellow-100 text-yellow-700',
    GEMUK:          'bg-orange-100 text-orange-700',
    OBESITAS:       'bg-red-100 text-red-700',
  }[s] ?? 'bg-gray-100 text-gray-400'
}
function wastingLabel(s) {
  return {
    SANGAT_KURUS:   'Sangat Kurus',
    KURUS:          'Kurus',
    NORMAL:         'Normal',
    BERISIKO_GEMUK: 'Berisiko Gemuk',
    GEMUK:          'Gemuk',
    OBESITAS:       'Obesitas',
  }[s] ?? (s ?? '—')
}
function imtLabel(s) {
  return {
    SANGAT_KURUS:   'Gizi Buruk',
    KURUS:          'Gizi Kurang',
    NORMAL:         'Gizi Baik',
    BERISIKO_GEMUK: 'Berisiko Lebih',
    GEMUK:          'Gizi Lebih',
    OBESITAS:       'Obesitas',
  }[s] ?? '—'
}

// Komponen ikon sort
const SortIcon = {
  props: ['col'],
  setup(props) {
    return () => {
      const active = currentSort.value === props.col
      const asc    = currentDir.value === 'asc'
      return h('span', { class: 'inline-flex flex-col leading-none ml-0.5' }, [
        h('svg', {
          class: `w-2.5 h-2.5 ${active && asc ? 'text-blue-600' : 'text-gray-300'}`,
          viewBox: '0 0 10 6', fill: 'currentColor',
        }, [h('path', { d: 'M5 0L0 6h10z' })]),
        h('svg', {
          class: `w-2.5 h-2.5 ${active && !asc ? 'text-blue-600' : 'text-gray-300'}`,
          viewBox: '0 0 10 6', fill: 'currentColor',
        }, [h('path', { d: 'M5 6L0 0h10z' })]),
      ])
    }
  }
}
</script>