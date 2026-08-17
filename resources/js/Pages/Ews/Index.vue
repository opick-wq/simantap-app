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
        <!-- Filter Kategori (FITUR BARU) -->
        <div style="margin-bottom: 8px;">
          <select v-model="filters.kategori" @change="go"
                  style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; background-color: white;">
            <option value="semua">Semua Kategori (Gizi & Kehadiran)</option>
            <option value="gizi">Kategori: Masalah Gizi & Stunting</option>
            <option value="kehadiran">Kategori: Masalah Kehadiran</option>
          </select>
        </div>

        <!-- Filter posyandu -->
        <div v-if="posyandu.length > 1">
          <select v-model="filters.posyandu_id" @change="go"
                  class="form-input text-sm py-1.5" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            <option value="">Semua Posyandu</option>
            <option v-for="p in posyandu" :key="p.id" :value="p.id">{{ p.nama }}</option>
          </select>
        </div>

        <!-- Filter level -->
        <div class="flex gap-2 flex-wrap" style="margin-top: 8px;">
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
           class="card space-y-2"
           :style="{ borderLeft: isKehadiran(p) ? '5px solid #9ca3af' : (p.level_risiko === 'MERAH' ? '5px solid #ef4444' : '5px solid #facc15') }">
        
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

        <!-- FITUR BARU: Badge Rekomendasi Aksi & Kategori Asli -->
        <div class="flex items-center gap-2 flex-wrap">
          <span v-if="isKehadiran(p)" style="background-color: #f3f4f6; color: #4b5563; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: bold;">
            📍 Kunjungan Rumah
          </span>
          <span v-else style="background-color: #fee2e2; color: #b91c1c; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: bold;">
            🩺 Rujukan Gizi
          </span>
          
          <span :class="['text-xs font-semibold px-2 py-0.5 rounded-full', jenisMeta(p.jenis_peringatan).badge]">
            {{ jenisMeta(p.jenis_peringatan).kategori }}
          </span>
        </div>

        <p class="text-sm text-gray-700 bg-gray-50 rounded p-2" style="border: 1px solid #e5e7eb;">{{ p.pesan }}</p>

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

        <div class="flex items-center justify-between text-xs text-gray-400 pb-2 border-b border-gray-100">
          <span>{{ p.created_at_human }}</span>
          <span :class="statusBadge(p.status_tindak_lanjut)"
                class="text-xs font-semibold px-2 py-0.5 rounded-full">
            {{ statusLabel(p.status_tindak_lanjut) }}
          </span>
        </div>

        <!-- FITUR BARU: Tombol Aksi Langsung -->
        <div class="flex gap-2 pt-1">
          <!-- Tombol Lapor (Khusus Kader) -->
          <button v-if="roleUser === 'kader' && p.status_tindak_lanjut === 'BELUM'" 
                  @click="bukaModalLapor(p.id)"
                  style="flex: 1; padding: 8px; background-color: #2563eb; color: white; border: none; border-radius: 4px; font-size: 12px; font-weight: bold; cursor: pointer;">
            Lapor ke Nakes
          </button>
          
          <!-- Tombol Tandai Ditangani (Khusus Nakes/Admin) -->
          <button v-if="(roleUser === 'nakes' || roleUser === 'admin') && p.status_tindak_lanjut !== 'SELESAI'" 
                  @click="bukaModalTindakLanjut(p.id)"
                  style="flex: 1; padding: 8px; background-color: #16a34a; color: white; border: none; border-radius: 4px; font-size: 12px; font-weight: bold; cursor: pointer;">
            Tandai Ditangani
          </button>

          <Link :href="route('balita.show', p.balita_id)"
                class="flex-1 text-center text-xs py-2 border rounded text-blue-600 border-blue-300 font-medium">
            Lihat Profil
          </Link>
          <Link :href="route('peringatan.show', p.id)"
                class="flex-1 text-center text-xs py-2 bg-gray-100 text-gray-700 rounded font-medium">
            Detail
          </Link>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="peringatan.last_page > 1" class="flex justify-center gap-2 mt-4">
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
  kategori:    props.filters?.kategori ?? 'semua', // Filter kategori baru
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
  if (filters.kategori && filters.kategori !== 'semua') params.kategori = filters.kategori
  
  router.get(route('peringatan.index'), params, { preserveState: true })
}

function setFilter(key, val) {
  filters[key] = val
  go()
}

// Mengecek apakah jenis peringatan adalah masalah kehadiran
function isKehadiran(p) {
  return p.jenis_peringatan_raw === 'ABSEN_LAMA' || p.jenis_peringatan_raw === 'ABSEN_2BULAN';
}

// Fungsi Trigger untuk memanggil Modal Lapor Nakes
function bukaModalLapor(id) {
  // Hubungkan ke state/modal pelaporan yang kamu miliki
  console.log('Membuka modal lapor nakes untuk ID:', id);
  // Contoh: showModalLapor.value = true;
  // selectedId.value = id;
}

// Fungsi Trigger untuk memanggil Modal Tindak Lanjut[cite: 1]
function bukaModalTindakLanjut(id) {
  // Hubungkan ke state/modal penanganan yang kamu miliki
  console.log('Membuka modal tandai ditangani untuk ID:', id);
  // Contoh: showModalTindakLanjut.value = true;
  // selectedId.value = id;
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