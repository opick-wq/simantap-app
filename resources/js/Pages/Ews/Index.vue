<template>
  <AppLayout title="EWS — Peringatan Dini">
    <div class="p-4 space-y-4 pb-20">
      <h1 class="text-lg font-bold text-gray-800">⚠️ Peringatan Dini (EWS)</h1>

      <!-- Summary cards -->
      <div class="grid grid-cols-2 gap-3">
        <div class="card text-center border-l-4 border-red-500">
          <p class="text-3xl font-bold text-red-600">{{ ringkasan.total_merah }}</p>
          <p class="text-xs text-gray-500 mt-0.5">Risiko Tinggi</p>
        </div>
        <div class="card text-center border-l-4 border-yellow-400">
          <p class="text-3xl font-bold text-yellow-600">{{ ringkasan.total_kuning }}</p>
          <p class="text-xs text-gray-500 mt-0.5">Perlu Perhatian</p>
        </div>
      </div>

      <!-- Filter -->
      <div class="space-y-2">
        <!-- Filter posyandu (hanya tampil jika ada lebih dari 1 posyandu) -->
        <div v-if="posyandu.length > 1">
          <select v-model="filters.posyandu_id" @change="go"
                  class="form-input text-sm py-1.5">
            <option value="">Semua Posyandu</option>
            <option v-for="p in posyandu" :key="p.id" :value="p.id">{{ p.nama }}</option>
          </select>
        </div>
        <!-- Filter level -->
        <div class="flex gap-2 flex-wrap">
          <button v-for="f in levelFilters" :key="f.val"
                  @click="setFilter('level', f.val)"
                  :class="['px-3 py-1 rounded-full text-xs font-medium border transition',
                           filters.level === f.val ? f.cls : 'bg-white text-gray-600 border-gray-300']">
            {{ f.label }}
          </button>
        </div>
      </div>

      <!-- List -->
      <div v-if="peringatan.data.length === 0" class="text-center text-gray-400 py-12">
        <p class="text-4xl mb-2">✅</p>
        <p>Tidak ada peringatan aktif</p>
      </div>

      <div v-for="p in peringatan.data" :key="p.id"
           :class="['card border-l-4 space-y-2',
                    p.level_risiko === 'MERAH' ? 'border-red-500' : 'border-yellow-400']">
        <div class="flex items-start justify-between">
          <div class="flex items-start gap-2">
            <!-- Ikon kategori jenis peringatan -->
            <span :class="['text-xl leading-none mt-0.5', jenisMeta(p.jenis_peringatan).color]">
              {{ jenisMeta(p.jenis_peringatan).icon }}
            </span>
            <div>
              <p class="font-semibold text-gray-800">{{ p.balita_nama }}</p>
              <p class="text-xs text-gray-500">{{ p.umur_lengkap }} · {{ p.posyandu_nama }}</p>
            </div>
          </div>
          <span :class="p.level_risiko === 'MERAH' ? 'badge-merah' : 'badge-kuning'">
            {{ p.level_risiko }}
          </span>
        </div>

        <!-- Badge kategori -->
        <div class="flex items-center gap-2">
          <span :class="['text-xs font-semibold px-2 py-0.5 rounded-full',
                         jenisMeta(p.jenis_peringatan).badge]">
            {{ jenisMeta(p.jenis_peringatan).kategori }}
          </span>
          <span class="text-xs text-gray-500">{{ p.jenis_peringatan }}</span>
        </div>

        <p class="text-sm text-gray-700 bg-gray-50 rounded p-2">{{ p.pesan }}</p>

        <!-- Peringatan darurat untuk Nakes: MERAH belum diteruskan Kader -->
        <div v-if="p.belum_diteruskan && roleUser === 'nakes'"
             class="flex items-center gap-1.5 bg-red-50 border border-red-200 rounded px-2 py-1.5 text-xs text-red-700 font-medium">
          ⚠️ Belum dilaporkan Kader — tampil karena level MERAH
        </div>

        <!-- Badge bulan menunggak -->
        <div v-if="p.is_menunggak"
             class="flex items-center gap-1.5 bg-orange-50 border border-orange-200 rounded px-2 py-1 text-xs text-orange-700 font-medium">
          🕐 Peringatan dari <strong class="ml-1">{{ p.bulan_label }}</strong> — belum ditindaklanjuti
        </div>

        <div class="flex items-center justify-between text-xs text-gray-400">
          <span>{{ p.created_at_human }}</span>
          <span :class="statusBadge(p.status_tindak_lanjut)"
                class="text-xs font-semibold px-2 py-0.5 rounded-full">
            {{ statusLabel(p.status_tindak_lanjut) }}
          </span>
        </div>
        <div class="flex gap-2">
          <Link :href="route('balita.show', p.balita_id)"
                class="flex-1 text-center text-xs py-1.5 border rounded text-blue-600 border-blue-300">
            Lihat Profil
          </Link>
          <Link :href="route('peringatan.show', p.id)"
                class="flex-1 text-center text-xs py-1.5 bg-blue-600 text-white rounded">
            Detail
          </Link>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="peringatan.last_page > 1" class="flex justify-center gap-2">
        <Link v-if="peringatan.prev_page_url" :href="peringatan.prev_page_url" class="px-3 py-1 text-sm border rounded">‹</Link>
        <span class="px-3 py-1 text-sm text-gray-500">{{ peringatan.current_page }}/{{ peringatan.last_page }}</span>
        <Link v-if="peringatan.next_page_url" :href="peringatan.next_page_url" class="px-3 py-1 text-sm border rounded">›</Link>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/UI/AppLayout.vue'

const props = defineProps({
  peringatan: Object,
  ringkasan:  Object,
  posyandu:   Array,
  filters:    Object,
  roleUser:   String,
})

const filters = reactive({
  posyandu_id: props.filters?.posyandu_id ?? '',
  level:       props.filters?.level ?? '',
})

const levelFilters = [
  { val: '',       label: 'Semua',      cls: 'bg-blue-600 text-white border-blue-600' },
  { val: 'MERAH',  label: '🔴 Merah',  cls: 'bg-red-600 text-white border-red-600' },
  { val: 'KUNING', label: '🟡 Kuning', cls: 'bg-yellow-500 text-white border-yellow-500' },
]

function go() {
  const params = {}
  if (filters.posyandu_id) params.posyandu_id = filters.posyandu_id
  if (filters.level)       params.level       = filters.level
  router.get(route('peringatan.index'), params, { preserveState: true })
}

function setFilter(key, val) {
  filters[key] = val
  go()
}

function jenisMeta(jenis) {
  if (['GIZI_BURUK', 'GIZI_KURANG', 'RISIKO_GIZI'].includes(jenis)) {
    return {
      icon: '⚖️',
      color: 'text-orange-500',
      kategori: 'Gizi',
      badge: 'bg-orange-100 text-orange-700',
    }
  }
  if (['SANGAT_PENDEK', 'PENDEK_STUNTED', 'RISIKO_PENDEK'].includes(jenis)) {
    return {
      icon: '📏',
      color: 'text-purple-500',
      kategori: 'Stunting',
      badge: 'bg-purple-100 text-purple-700',
    }
  }
  if (['ZSCORE_DROP', 'ZSCORE_DROP_MILD', 'ZSCORE_DROP_PROG', 'WEIGHT_LOSS', 'WEIGHT_STAGNATION'].includes(jenis)) {
    return {
      icon: '📉',
      color: 'text-blue-500',
      kategori: 'Tren Turun',
      badge: 'bg-blue-100 text-blue-700',
    }
  }
  if (['ABSEN_2BULAN', 'ABSEN_LAMA'].includes(jenis)) {
    return {
      icon: '🚫',
      color: 'text-gray-400',
      kategori: 'Absen',
      badge: 'bg-gray-100 text-gray-600',
    }
  }
  return { icon: '⚠️', color: 'text-yellow-500', kategori: 'Lainnya', badge: 'bg-yellow-100 text-yellow-700' }
}

function statusLabel(s) {
  return {
    BELUM:        'Belum Dilaporkan Kader',
    DILAPORKAN:   'Dilaporkan ke Nakes',
    DALAM_PROSES: 'Dalam Proses',
    SELESAI:      'Selesai',
  }[s] ?? s
}

function statusBadge(s) {
  return {
    BELUM:        'bg-red-100 text-red-700',
    DILAPORKAN:   'bg-blue-100 text-blue-700',
    DALAM_PROSES: 'bg-yellow-100 text-yellow-700',
    SELESAI:      'bg-green-100 text-green-700',
  }[s] ?? 'bg-gray-100 text-gray-600'
}
</script>
