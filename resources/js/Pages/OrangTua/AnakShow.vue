<template>
  <div class="min-h-screen bg-gray-50 pb-10">
    <header class="bg-blue-600 text-white px-4 py-4 sticky top-0 z-50 shadow">
      <div class="flex items-center gap-3">
        <Link href="/ortu/dashboard" class="text-blue-200 text-xl">‹</Link>
        <div>
          <h1 class="font-bold">{{ balita.nama }}</h1>
          <p class="text-blue-200 text-xs">{{ balita.umur_lengkap }}</p>
        </div>
      </div>
    </header>

    <div class="px-4 py-4 space-y-4">

      <!-- Info balita -->
      <div class="bg-white rounded-xl p-4 shadow-sm grid grid-cols-2 gap-3 text-sm">
        <div>
          <p class="text-gray-400 text-xs">Tanggal Lahir</p>
          <p class="font-medium text-gray-800">{{ balita.tanggal_lahir }}</p>
        </div>
        <div>
          <p class="text-gray-400 text-xs">Jenis Kelamin</p>
          <p class="font-medium text-gray-800">{{ balita.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
        </div>
        <div>
          <p class="text-gray-400 text-xs">Posyandu</p>
          <p class="font-medium text-gray-800">{{ balita.posyandu }}</p>
        </div>
        <div>
          <p class="text-gray-400 text-xs">Nama Ibu</p>
          <p class="font-medium text-gray-800">{{ balita.nama_ibu }}</p>
        </div>
      </div>

      <!-- Status terakhir -->
      <div v-if="pengukuranTerakhir" class="bg-white rounded-xl p-4 shadow-sm">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Pengukuran Terakhir</p>
        <div class="grid grid-cols-3 gap-3 text-center">
          <div class="bg-blue-50 rounded-lg p-3">
            <p class="text-xl font-bold text-blue-700">{{ pengukuranTerakhir.berat_badan_kg }}</p>
            <p class="text-xs text-gray-500 mt-0.5">BB (kg)</p>
          </div>
          <div class="bg-green-50 rounded-lg p-3">
            <p class="text-xl font-bold text-green-700">{{ pengukuranTerakhir.tinggi_badan_cm }}</p>
            <p class="text-xs text-gray-500 mt-0.5">TB (cm)</p>
          </div>
          <div class="bg-gray-50 rounded-lg p-3">
            <p class="text-xl font-bold text-gray-700">{{ pengukuranTerakhir.umur_bulan }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Usia (bln)</p>
          </div>
        </div>
        <p class="text-xs text-gray-400 text-right mt-2">{{ pengukuranTerakhir.tanggal_ukur }}</p>
      </div>

      <!-- Grafik pertumbuhan -->
      <div v-if="riwayat.length >= 2" class="bg-white rounded-xl p-4 shadow-sm">
        <p class="text-sm font-semibold text-gray-700 mb-3">📈 Grafik Pertumbuhan (Standar WHO)</p>
        <GrowthChart
          :pengukuran="riwayat"
          :curve-bb-u="curveBbU"
          :curve-tb-u="curveTbU"
          :curve-bb-tb="curveBbTb"
          :curve-imt-u="curveImtU"
          :gender="balita.jenis_kelamin"
        />
        <p class="text-xs text-gray-400 mt-2 text-center">
          Garis hijau = median WHO · Oranye = ±2SD · Merah putus = ±3SD
        </p>
      </div>

      <div v-else-if="riwayat.length === 1"
           class="bg-blue-50 rounded-xl p-4 text-center text-sm text-blue-700">
        📊 Grafik akan muncul setelah minimal 2 kali pengukuran
      </div>

      <!-- Riwayat pengukuran -->
      <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b bg-gray-50 flex items-center justify-between">
          <h2 class="font-semibold text-gray-700">Riwayat Pengukuran</h2>
          <span class="text-xs text-gray-400">{{ riwayat.length }} kali</span>
        </div>

        <div v-if="riwayat.length === 0" class="px-4 py-8 text-center text-gray-400 text-sm">
          Belum ada data pengukuran
        </div>

        <div v-for="(p, i) in riwayatDesc" :key="i" class="px-4 py-3 border-b last:border-0">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold text-gray-800">{{ p.tanggal_ukur }}</p>
              <p class="text-xs text-gray-500">Usia {{ p.umur_bulan }} bulan</p>
            </div>
            <span class="text-xs text-gray-400">{{ p.umur_bulan }} bulan</span>
          </div>
          <div class="flex gap-4 mt-2 text-sm">
            <div class="flex items-center gap-1">
              <span class="text-gray-400 text-xs">BB</span>
              <span class="font-semibold text-blue-600">{{ p.berat_badan_kg }} kg</span>
            </div>
            <div class="flex items-center gap-1">
              <span class="text-gray-400 text-xs">TB</span>
              <span class="font-semibold text-green-600">{{ p.tinggi_badan_cm }} cm</span>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import GrowthChart from '@/Components/Charts/GrowthChart.vue'

const props = defineProps({
  balita:   Object,
  riwayat:  Array,
  curveBbU: Array,
  curveTbU: Array,
  curveBbTb: Array,
  curveImtU: Array,
})

const riwayatDesc      = computed(() => [...props.riwayat].sort((a, b) => {
  const da = a.tanggal_ukur.split('/').reverse().join('')
  const db = b.tanggal_ukur.split('/').reverse().join('')
  return db.localeCompare(da)
}))
const pengukuranTerakhir = computed(() => riwayatDesc.value[0] ?? null)

</script>
