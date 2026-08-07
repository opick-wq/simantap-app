<template>
  <AppLayout title="Dashboard Dinas">
    <div class="p-5 space-y-5 max-w-screen-xl mx-auto">

      <!-- ══ BANNER ══ -->
      <div class="rounded-2xl p-5 text-white shadow-md bg-gradient-to-r from-teal-600 to-teal-800">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <h1 class="text-xl font-bold">Dashboard Dinas Kesehatan</h1>
            <p class="text-teal-200 text-sm mt-0.5">Monitoring gizi balita seluruh posyandu · {{ tanggal }}</p>
          </div>
          <div class="text-right shrink-0">
            <p class="text-lg font-semibold">{{ totalNasional.total_posyandu }} Posyandu</p>
            <p class="text-teal-300 text-xs">Aktif dalam sistem</p>
          </div>
        </div>
      </div>

      <!-- ══ 4 KARTU DAERAH ══ -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="bg-white rounded-xl p-4 shadow-sm border-t-4 border-blue-500">
          <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Total Balita</p>
          <p class="text-3xl font-bold text-slate-800">{{ totalNasional.total_balita }}</p>
          <p class="text-xs text-slate-400 mt-2">Seluruh posyandu di daerah</p>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border-t-4 border-red-500">
          <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Stunting</p>
          <p class="text-3xl font-bold text-red-600">{{ totalNasional.stunting }}</p>
          <div class="mt-2 flex items-center gap-2">
            <span :class="totalNasional.pct_stunting > 14 ? 'text-red-500' : 'text-green-600'"
                  class="text-sm font-bold">{{ totalNasional.pct_stunting }}%</span>
            <span class="text-xs text-slate-400">target &lt;14%</span>
          </div>
          <div class="mt-1.5 h-1.5 bg-slate-100 rounded-full overflow-hidden">
            <div class="h-full rounded-full transition-all"
                 :class="totalNasional.pct_stunting > 14 ? 'bg-red-500' : 'bg-green-500'"
                 :style="`width:${Math.min(totalNasional.pct_stunting, 100)}%`" />
          </div>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border-t-4 border-orange-400">
          <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Gizi Kurang</p>
          <p class="text-3xl font-bold text-orange-500">{{ totalNasional.gizi_kurang }}</p>
          <p class="text-xs text-slate-400 mt-2">Gizi buruk + gizi kurang</p>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border-t-4 border-rose-600">
          <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">EWS Merah Aktif</p>
          <p class="text-3xl font-bold text-rose-600">{{ totalNasional.ews_merah }}</p>
          <p class="text-xs text-slate-400 mt-2">Belum selesai ditangani</p>
        </div>
      </div>

      <!-- ══ STATUS GIZI DAERAH ══ -->
      <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
          <h2 class="font-semibold text-slate-700 text-sm">Status Gizi Daerah</h2>
          <p class="text-xs text-slate-400">Berdasarkan Z-score BB/U (WHO) · Pengukuran terakhir per balita</p>
        </div>
        <div class="p-4 grid grid-cols-5 gap-2 text-center">
          <div class="rounded-xl bg-red-50 p-3">
            <p class="text-2xl font-bold text-red-600">{{ totalNasional.gizi_buruk }}</p>
            <p class="text-xs text-slate-500 mt-1 leading-tight">Gizi Buruk</p>
            <p class="text-xs text-red-400 font-mono mt-0.5">&lt;-3 SD</p>
          </div>
          <div class="rounded-xl bg-orange-50 p-3">
            <p class="text-2xl font-bold text-orange-500">{{ totalNasional.gizi_kurang_n }}</p>
            <p class="text-xs text-slate-500 mt-1 leading-tight">Gizi Kurang</p>
            <p class="text-xs text-orange-400 font-mono mt-0.5">-3~-2</p>
          </div>
          <div class="rounded-xl bg-green-50 p-3">
            <p class="text-2xl font-bold text-green-600">{{ totalNasional.gizi_baik }}</p>
            <p class="text-xs text-slate-500 mt-1 leading-tight">Normal</p>
            <p class="text-xs text-green-400 font-mono mt-0.5">-2~+1</p>
          </div>
          <div class="rounded-xl bg-yellow-50 p-3">
            <p class="text-2xl font-bold text-yellow-600">{{ totalNasional.risiko_lebih }}</p>
            <p class="text-xs text-slate-500 mt-1 leading-tight">Risiko BB Lebih</p>
            <p class="text-xs text-yellow-500 font-mono mt-0.5">&gt;+1</p>
          </div>
        </div>
        <div v-if="totalNasional.hadir_diukur > 0" class="px-4 pb-4">
          <div class="flex rounded-full overflow-hidden h-2 bg-slate-100">
            <div class="bg-red-500"    :style="`width:${pct(totalNasional.gizi_buruk)}%`" />
            <div class="bg-orange-400" :style="`width:${pct(totalNasional.gizi_kurang_n)}%`" />
            <div class="bg-green-400"  :style="`width:${pct(totalNasional.gizi_baik)}%`" />
            <div class="bg-yellow-300" :style="`width:${pct(totalNasional.risiko_lebih)}%`" />
          </div>
          <p class="text-xs text-slate-400 mt-1.5 text-right">dari {{ totalNasional.hadir_diukur }} balita diukur</p>
        </div>
      </div>

      <!-- ══ STATUS STUNTING DAERAH ══ -->
      <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
          <h2 class="font-semibold text-slate-700 text-sm">Status Stunting Daerah TB/U</h2>
          <p class="text-xs text-slate-400">WHO 2006 · Permenkes No.2/2020</p>
        </div>
        <div class="p-4 grid grid-cols-4 gap-2 text-center">
          <div class="rounded-xl bg-red-50 p-3">
            <p class="text-2xl font-bold text-red-600">{{ totalNasional.stunting_sangat_pendek }}</p>
            <p class="text-xs text-slate-500 mt-1 leading-tight">Sangat Pendek</p>
            <p class="text-xs text-red-400 font-mono mt-0.5">&lt;-3 SD</p>
          </div>
          <div class="rounded-xl bg-orange-50 p-3">
            <p class="text-2xl font-bold text-orange-500">{{ totalNasional.stunting_pendek }}</p>
            <p class="text-xs text-slate-500 mt-1 leading-tight">Pendek</p>
            <p class="text-xs text-orange-400 font-mono mt-0.5">-3~-2</p>
          </div>
          <div class="rounded-xl bg-green-50 p-3">
            <p class="text-2xl font-bold text-green-600">{{ totalNasional.stunting_normal }}</p>
            <p class="text-xs text-slate-500 mt-1 leading-tight">Normal</p>
            <p class="text-xs text-green-400 font-mono mt-0.5">-2~+3</p>
          </div>
          <div class="rounded-xl bg-blue-50 p-3">
            <p class="text-2xl font-bold text-blue-600">{{ totalNasional.stunting_tinggi }}</p>
            <p class="text-xs text-slate-500 mt-1 leading-tight">Tinggi</p>
            <p class="text-xs text-blue-400 font-mono mt-0.5">&gt;+3</p>
          </div>
        </div>
        <div v-if="totalNasional.hadir_diukur > 0" class="px-4 pb-4">
          <div class="flex rounded-full overflow-hidden h-2 bg-slate-100">
            <div class="bg-red-500"    :style="`width:${pctS(totalNasional.stunting_sangat_pendek)}%`" />
            <div class="bg-orange-400" :style="`width:${pctS(totalNasional.stunting_pendek)}%`" />
            <div class="bg-green-400"  :style="`width:${pctS(totalNasional.stunting_normal)}%`" />
            <div class="bg-blue-400"   :style="`width:${pctS(totalNasional.stunting_tinggi)}%`" />
          </div>
        </div>
      </div>

      <!-- ══ TREN + TABEL ══ -->
      <div class="flex flex-col lg:flex-row gap-5 items-start">

        <!-- Kolom kiri: tren + per posyandu -->
        <div class="flex-1 space-y-5 min-w-0">

          <!-- Tren Stunting -->
          <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
              <h2 class="font-semibold text-slate-700 text-sm">Tren Stunting 6 Bulan Terakhir</h2>
              <p class="text-xs text-slate-400">Persentase stunting dari balita yang diukur</p>
            </div>
            <div class="p-5">
              <!-- Bar chart sederhana -->
              <div class="flex items-end gap-3 h-32">
                <div v-for="t in tren" :key="t.bulan"
                     class="flex-1 flex flex-col items-center gap-1">
                  <span class="text-xs font-semibold"
                        :class="t.pct_stunting > 14 ? 'text-red-500' : 'text-green-600'">
                    {{ t.pct_stunting }}%
                  </span>
                  <div class="w-full rounded-t-md transition-all"
                       :class="t.pct_stunting > 14 ? 'bg-red-400' : 'bg-green-400'"
                       :style="`height:${Math.max((t.pct_stunting / maxPct) * 96, 4)}px`" />
                  <span class="text-xs text-slate-400 text-center leading-tight">{{ t.bulan }}</span>
                </div>
              </div>
              <div class="mt-3 flex items-center gap-4 text-xs text-slate-400">
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-green-400 inline-block"></span> Di bawah target (&lt;14%)</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-red-400 inline-block"></span> Melebihi target</span>
              </div>
            </div>
          </div>

          <!-- Tabel Per Posyandu -->
          <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
              <div>
                <h2 class="font-semibold text-slate-700 text-sm">Rekap Per Posyandu</h2>
                <p class="text-xs text-slate-400">Klik baris untuk detail</p>
              </div>
              <span class="text-xs text-slate-400">{{ ringkasan.length }} posyandu</span>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-sm min-w-[640px]">
                <thead>
                  <tr class="bg-slate-50 border-b border-slate-200 text-xs text-slate-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-left font-semibold">Posyandu</th>
                    <th class="px-4 py-3 text-center font-semibold cursor-pointer hover:text-blue-600"
                        @click="sortTabel('total_balita')">
                      Balita <SortIconDinas col="total_balita" />
                    </th>
                    <th class="px-4 py-3 text-center font-semibold cursor-pointer hover:text-blue-600"
                        @click="sortTabel('pct_stunting')">
                      Stunting <SortIconDinas col="pct_stunting" />
                    </th>
                    <th class="px-4 py-3 text-center font-semibold cursor-pointer hover:text-blue-600"
                        @click="sortTabel('pct_gizi_kurang')">
                      BB Kurang <SortIconDinas col="pct_gizi_kurang" />
                    </th>
                    <th class="px-4 py-3 text-center font-semibold cursor-pointer hover:text-blue-600"
                        @click="sortTabel('ews_merah')">
                      EWS Merah <SortIconDinas col="ews_merah" />
                    </th>
                    <th class="px-4 py-3 text-center font-semibold">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="p in ringkasanSorted" :key="p.id"
                      class="border-b border-slate-100 hover:bg-slate-50 transition">
                    <td class="px-4 py-3">
                      <p class="font-semibold text-slate-800">{{ p.nama }}</p>
                      <p class="text-xs text-slate-400">{{ p.wilayah }}</p>
                    </td>
                    <td class="px-4 py-3 text-center font-semibold text-slate-700">{{ p.total_balita }}</td>
                    <td class="px-4 py-3 text-center">
                      <span class="font-bold" :class="p.pct_stunting > 14 ? 'text-red-600' : 'text-green-600'">
                        {{ p.stunting }}
                      </span>
                      <span class="text-xs text-slate-400 ml-1">({{ p.pct_stunting }}%)</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                      <span class="font-bold text-orange-500">{{ p.gizi_kurang }}</span>
                      <span class="text-xs text-slate-400 ml-1">({{ p.pct_gizi_kurang }}%)</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                      <span v-if="p.ews_merah > 0"
                            class="bg-red-100 text-red-700 font-bold text-xs px-2 py-0.5 rounded-full">
                        {{ p.ews_merah }}
                      </span>
                      <span v-else class="text-slate-300 text-xs">—</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                      <span :class="statusPosyandu(p).cls"
                            class="text-xs font-semibold px-2 py-1 rounded-full whitespace-nowrap">
                        {{ statusPosyandu(p).label }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

        </div><!-- /kolom kiri -->

        <!-- Kolom kanan: perhatian -->
        <div class="w-full lg:w-72 space-y-4 lg:sticky lg:top-5">

          <!-- Cakupan penimbangan -->
          <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100">
              <h3 class="font-semibold text-sm text-slate-700">Cakupan Penimbangan</h3>
              <p class="text-xs text-slate-400">Bulan {{ bulanIni }}</p>
            </div>
            <div class="p-4 space-y-3">
              <div v-for="p in ringkasan" :key="p.id">
                <div class="flex items-center justify-between text-xs mb-1">
                  <span class="text-slate-600 truncate max-w-[140px]">{{ p.nama }}</span>
                  <span class="font-semibold shrink-0 ml-2"
                        :class="p.pct_cakupan >= 80 ? 'text-green-600' : p.pct_cakupan >= 50 ? 'text-yellow-600' : 'text-red-500'">
                    {{ p.pct_cakupan }}%
                  </span>
                </div>
                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                  <div class="h-full rounded-full transition-all"
                       :class="p.pct_cakupan >= 80 ? 'bg-green-500' : p.pct_cakupan >= 50 ? 'bg-yellow-400' : 'bg-red-400'"
                       :style="`width:${p.pct_cakupan}%`" />
                </div>
              </div>
            </div>
          </div>

        </div><!-- /kolom kanan -->

      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, ref, h } from 'vue'
import AppLayout from '@/Components/UI/AppLayout.vue'
import dayjs from 'dayjs'
import 'dayjs/locale/id'
dayjs.locale('id')

const props = defineProps({
  ringkasan:     Array,
  totalNasional: Object,
  tren:          Array,
})

const tanggal  = computed(() => dayjs().format('dddd, D MMMM YYYY'))
const bulanIni = computed(() => dayjs().format('MMMM YYYY'))

// Sort tabel
const sortCol = ref('pct_stunting')
const sortDir = ref('desc')

function sortTabel(col) {
  if (sortCol.value === col) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortCol.value = col
    sortDir.value = 'desc'
  }
}

const ringkasanSorted = computed(() => {
  return [...props.ringkasan].sort((a, b) => {
    const diff = (a[sortCol.value] ?? 0) - (b[sortCol.value] ?? 0)
    return sortDir.value === 'asc' ? diff : -diff
  })
})

const SortIconDinas = {
  props: ['col'],
  setup(p) {
    return () => {
      const active = sortCol.value === p.col
      const asc    = sortDir.value === 'asc'
      return h('span', { class: 'inline-flex flex-col leading-none ml-0.5 align-middle' }, [
        h('svg', { class: `w-2.5 h-2.5 ${active && asc  ? 'text-blue-600' : 'text-gray-300'}`, viewBox: '0 0 10 6', fill: 'currentColor' },
          [h('path', { d: 'M5 0L0 6h10z' })]),
        h('svg', { class: `w-2.5 h-2.5 ${active && !asc ? 'text-blue-600' : 'text-gray-300'}`, viewBox: '0 0 10 6', fill: 'currentColor' },
          [h('path', { d: 'M5 6L0 0h10z' })]),
      ])
    }
  }
}


const maxPct = computed(() =>
  Math.max(...props.tren.map(t => t.pct_stunting), 14, 1)
)

function pct(val) {
  const total = props.totalNasional.hadir_diukur
  if (!total) return 0
  return Math.round((val / total) * 100)
}

function pctS(val) {
  const total = props.totalNasional.stunting_sangat_pendek
    + props.totalNasional.stunting_pendek
    + props.totalNasional.stunting_normal
    + props.totalNasional.stunting_tinggi
  if (!total) return 0
  return Math.round((val / total) * 100)
}

function statusPosyandu(p) {
  if (p.ews_merah > 0 || p.pct_stunting > 20)
    return { label: 'Tindak Lanjut', cls: 'bg-red-100 text-red-700' }
  if (p.pct_stunting > 14 || p.pct_cakupan < 50)
    return { label: 'Pantau', cls: 'bg-yellow-100 text-yellow-700' }
  return { label: 'Baik', cls: 'bg-green-100 text-green-700' }
}
</script>
