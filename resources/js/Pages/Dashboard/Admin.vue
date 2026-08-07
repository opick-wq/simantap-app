<template>
  <AppLayout title="Dashboard Admin">
    <div class="p-5 space-y-5 max-w-screen-xl mx-auto">

      <!-- ══ BANNER ══ -->
      <div class="rounded-2xl p-5 text-white shadow-md bg-gradient-to-r from-purple-700 to-purple-900">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <h1 class="text-xl font-bold">Dashboard Administrator</h1>
            <p class="text-purple-200 text-sm mt-0.5">Manajemen sistem SI-MANTAP · {{ tanggal }}</p>
          </div>
          <div class="text-right shrink-0">
            <p class="text-lg font-semibold">{{ sistem.total_posyandu }} Posyandu</p>
            <p class="text-purple-300 text-xs">Aktif dalam sistem</p>
          </div>
        </div>
      </div>

      <!-- ══ RINGKASAN SISTEM ══ -->
      <div>
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-widest">Ringkasan Sistem</h2>
          <Link href="/users" class="text-xs text-blue-600 font-medium hover:underline">Kelola Akun →</Link>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
          <Link href="/balita" class="bg-white rounded-xl p-4 shadow-sm border-t-4 border-blue-500 hover:shadow-md transition">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Total Balita</p>
            <p class="text-3xl font-bold text-slate-800">{{ sistem.total_balita }}</p>
            <p class="text-xs text-slate-400 mt-2">Aktif · <span class="text-slate-500">{{ sistem.balita_nonaktif }} nonaktif</span></p>
          </Link>
          <Link href="/posyandu" class="bg-white rounded-xl p-4 shadow-sm border-t-4 border-teal-500 hover:shadow-md transition">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Posyandu</p>
            <p class="text-3xl font-bold text-slate-800">{{ sistem.total_posyandu }}</p>
            <p class="text-xs text-slate-400 mt-2">Terdaftar aktif</p>
          </Link>
          <Link href="/users" class="bg-white rounded-xl p-4 shadow-sm border-t-4 border-blue-400 hover:shadow-md transition">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">🏘️ Kader</p>
            <p class="text-3xl font-bold text-blue-600">{{ sistem.total_kader }}</p>
            <p class="text-xs text-slate-400 mt-2">Pengguna aktif</p>
          </Link>
          <Link href="/users" class="bg-white rounded-xl p-4 shadow-sm border-t-4 border-emerald-400 hover:shadow-md transition">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">👩‍⚕️ Nakes/Bidan</p>
            <p class="text-3xl font-bold text-emerald-600">{{ sistem.total_nakes }}</p>
            <p class="text-xs text-slate-400 mt-2">Pengguna aktif</p>
          </Link>
          <Link href="/users" class="bg-white rounded-xl p-4 shadow-sm border-t-4 border-indigo-400 hover:shadow-md transition">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">🏛️ Petugas</p>
            <p class="text-3xl font-bold text-indigo-600">{{ sistem.total_petugas }}</p>
            <p class="text-xs text-slate-400 mt-2">Pengguna aktif</p>
          </Link>
          <Link href="/users" class="bg-white rounded-xl p-4 shadow-sm border-t-4 border-teal-400 hover:shadow-md transition">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">📊 Dinas</p>
            <p class="text-3xl font-bold text-teal-600">{{ sistem.total_dinas }}</p>
            <p class="text-xs text-slate-400 mt-2">Pengguna aktif</p>
          </Link>
        </div>
      </div>

      <!-- ══ STATISTIK KESEHATAN ══ -->
      <div>
        <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-widest mb-3">Statistik Kesehatan</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

          <!-- Status Gizi -->
          <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
              <h3 class="font-semibold text-slate-700 text-sm">Status Gizi BB/U</h3>
              <p class="text-xs text-slate-400">Pengukuran terakhir per balita</p>
            </div>
            <div class="p-4 grid grid-cols-5 gap-2 text-center">
              <div class="rounded-xl bg-red-50 p-3">
                <p class="text-xl font-bold text-red-600">{{ statistik.gizi_buruk }}</p>
                <p class="text-xs text-slate-500 mt-1 leading-tight">Gizi Buruk</p>
                <p class="text-xs text-red-400 font-mono">&lt;-3</p>
              </div>
              <div class="rounded-xl bg-orange-50 p-3">
                <p class="text-xl font-bold text-orange-500">{{ statistik.gizi_kurang }}</p>
                <p class="text-xs text-slate-500 mt-1 leading-tight">Gizi Kurang</p>
                <p class="text-xs text-orange-400 font-mono">-3~-2</p>
              </div>
              <div class="rounded-xl bg-green-50 p-3">
                <p class="text-xl font-bold text-green-600">{{ statistik.gizi_baik }}</p>
                <p class="text-xs text-slate-500 mt-1 leading-tight">Normal</p>
                <p class="text-xs text-green-400 font-mono">-2~+1</p>
              </div>
              <div class="rounded-xl bg-yellow-50 p-3">
                <p class="text-xl font-bold text-yellow-600">{{ statistik.risiko_lebih }}</p>
                <p class="text-xs text-slate-500 mt-1 leading-tight">Risiko BB Lebih</p>
                <p class="text-xs text-yellow-500 font-mono">&gt;+1</p>
              </div>
            </div>
            <div class="px-4 pb-4">
              <div class="flex rounded-full overflow-hidden h-1.5 bg-slate-100">
                <div class="bg-red-500"    :style="`width:${pct(statistik.gizi_buruk)}%`" />
                <div class="bg-orange-400" :style="`width:${pct(statistik.gizi_kurang)}%`" />
                <div class="bg-green-400"  :style="`width:${pct(statistik.gizi_baik)}%`" />
                <div class="bg-yellow-300" :style="`width:${pct(statistik.risiko_lebih)}%`" />
              </div>
            </div>
          </div>

          <!-- Status Stunting -->
          <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
              <h3 class="font-semibold text-slate-700 text-sm">Status Stunting TB/U</h3>
              <div class="flex items-center gap-2 mt-0.5">
                <p class="text-xs text-slate-400">Target nasional &lt;14%</p>
                <span :class="statistik.pct_stunting > 14 ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'"
                      class="text-xs font-bold px-2 py-0.5 rounded-full">
                  {{ statistik.pct_stunting }}%
                </span>
              </div>
            </div>
            <div class="p-4 grid grid-cols-4 gap-2 text-center">
              <div class="rounded-xl bg-red-50 p-3">
                <p class="text-xl font-bold text-red-600">{{ statistik.stunting_sangat_pendek }}</p>
                <p class="text-xs text-slate-500 mt-1 leading-tight">Sangat Pendek</p>
                <p class="text-xs text-red-400 font-mono">&lt;-3</p>
              </div>
              <div class="rounded-xl bg-orange-50 p-3">
                <p class="text-xl font-bold text-orange-500">{{ statistik.stunting_pendek }}</p>
                <p class="text-xs text-slate-500 mt-1 leading-tight">Pendek</p>
                <p class="text-xs text-orange-400 font-mono">-3~-2</p>
              </div>
              <div class="rounded-xl bg-green-50 p-3">
                <p class="text-xl font-bold text-green-600">{{ statistik.stunting_normal }}</p>
                <p class="text-xs text-slate-500 mt-1 leading-tight">Normal</p>
                <p class="text-xs text-green-400 font-mono">-2~+3</p>
              </div>
              <div class="rounded-xl bg-blue-50 p-3">
                <p class="text-xl font-bold text-blue-600">{{ statistik.stunting_tinggi }}</p>
                <p class="text-xs text-slate-500 mt-1 leading-tight">Tinggi</p>
                <p class="text-xs text-blue-400 font-mono">&gt;+3</p>
              </div>
            </div>
            <div class="px-4 pb-4">
              <div class="flex rounded-full overflow-hidden h-1.5 bg-slate-100">
                <div class="bg-red-500"    :style="`width:${pctS(statistik.stunting_sangat_pendek)}%`" />
                <div class="bg-orange-400" :style="`width:${pctS(statistik.stunting_pendek)}%`" />
                <div class="bg-green-400"  :style="`width:${pctS(statistik.stunting_normal)}%`" />
                <div class="bg-blue-400"   :style="`width:${pctS(statistik.stunting_tinggi)}%`" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ══ AKTIVITAS SISTEM ══ -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <!-- Akun Terbaru -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-semibold text-sm text-slate-700">Akun Terbaru</h3>
            <Link href="/users" class="text-xs text-blue-600 hover:underline">Semua →</Link>
          </div>
          <div class="divide-y divide-slate-100">
            <div v-for="u in akunTerbaru" :key="u.id"
                 class="px-4 py-3 flex items-center gap-3">
              <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0', roleColor(u.role)]">
                {{ u.nama.charAt(0) }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-800 truncate">{{ u.nama }}</p>
                <p class="text-xs text-slate-400">{{ roleLabel(u.role) }} · {{ u.created_at }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Pengukuran Terbaru -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-100">
            <h3 class="font-semibold text-sm text-slate-700">Pengukuran Terbaru</h3>
          </div>
          <div class="divide-y divide-slate-100">
            <div v-for="p in pengukuranTerbaru" :key="p.balita_nama + p.tanggal_ukur"
                 class="px-4 py-3">
              <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-800 truncate">{{ p.balita_nama }}</p>
                <span :class="p.flag_ews === 'MERAH' ? 'bg-red-100 text-red-700'
                            : p.flag_ews === 'KUNING' ? 'bg-yellow-100 text-yellow-700'
                            : 'bg-green-100 text-green-700'"
                      class="text-xs font-bold px-2 py-0.5 rounded-full shrink-0 ml-2">
                  {{ p.flag_ews }}
                </span>
              </div>
              <p class="text-xs text-slate-400 mt-0.5">{{ p.posyandu_nama }} · {{ p.tanggal_ukur }}</p>
              <p class="text-xs text-slate-400">oleh {{ p.pencatat }}</p>
            </div>
          </div>
        </div>

        <!-- EWS Belum Ditangani -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden border-t-4 border-red-500">
          <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-semibold text-sm text-slate-700">EWS Belum Ditangani</h3>
            <Link href="/peringatan" class="text-xs text-blue-600 hover:underline">Semua →</Link>
          </div>
          <div v-if="ewsTerbaru.length === 0"
               class="px-4 py-8 text-center text-slate-400 text-sm">
            <div class="text-2xl mb-2">✅</div>
            Semua EWS sudah ditangani
          </div>
          <div class="divide-y divide-slate-100">
            <div v-for="e in ewsTerbaru" :key="e.balita_nama + e.created_at"
                 class="px-4 py-3">
              <div class="flex items-center justify-between gap-2">
                <p class="text-sm font-semibold text-slate-800 truncate">{{ e.balita_nama }}</p>
                <span :class="e.level_risiko === 'MERAH' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'"
                      class="text-xs font-bold px-2 py-0.5 rounded-full shrink-0">
                  {{ e.level_risiko }}
                </span>
              </div>
              <p class="text-xs text-slate-400 mt-0.5">{{ e.posyandu_nama }}</p>
              <p class="text-xs text-slate-400">{{ e.created_at }}</p>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Components/UI/AppLayout.vue'
import dayjs from 'dayjs'
import 'dayjs/locale/id'
dayjs.locale('id')

const props = defineProps({
  sistem:              Object,
  statistik:           Object,
  akunTerbaru:         Array,
  pengukuranTerbaru:   Array,
  ewsTerbaru:          Array,
})

const tanggal      = computed(() => dayjs().format('dddd, D MMMM YYYY'))
const totalPengguna = computed(() =>
  props.sistem.total_kader + props.sistem.total_nakes +
  props.sistem.total_petugas + props.sistem.total_dinas
)

function pct(val) {
  const total = props.sistem.total_balita
  return total > 0 ? Math.round((val / total) * 100) : 0
}

function pctS(val) {
  const total = props.statistik.stunting_sangat_pendek + props.statistik.stunting_pendek
    + props.statistik.stunting_normal + props.statistik.stunting_tinggi
  return total > 0 ? Math.round((val / total) * 100) : 0
}

function roleColor(role) {
  return { kader: 'bg-blue-500', nakes: 'bg-emerald-500', petugas: 'bg-indigo-500', dinas: 'bg-teal-500' }[role] ?? 'bg-slate-400'
}

function roleLabel(role) {
  return { kader: 'Kader', nakes: 'Nakes', petugas: 'Petugas', dinas: 'Dinas' }[role] ?? role
}
</script>
