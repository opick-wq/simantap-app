<template>
  <AppLayout title="Laporan">
    <div class="p-4 space-y-4 pb-20">
      <h1 class="text-lg font-bold text-gray-800">📊 Laporan</h1>

      <!-- Ringkasan -->
      <div class="card space-y-3">
        <p class="font-semibold text-gray-700">Ringkasan Bulan Ini</p>
        <div class="grid grid-cols-3 gap-2">
          <div class="text-center p-2 bg-blue-50 rounded-lg">
            <p class="text-2xl font-bold text-blue-700">{{ ringkasan.total_balita }}</p>
            <p class="text-xs text-gray-500">Total Balita</p>
          </div>
          <div class="text-center p-2 bg-red-50 rounded-lg">
            <p class="text-2xl font-bold text-red-600">{{ ringkasan.gizi_buruk }}</p>
            <p class="text-xs text-gray-500">Gizi Buruk</p>
          </div>
          <div class="text-center p-2 bg-yellow-50 rounded-lg">
            <p class="text-2xl font-bold text-yellow-600">{{ ringkasan.gizi_kurang }}</p>
            <p class="text-xs text-gray-500">Gizi Kurang</p>
          </div>
          <div class="text-center p-2 bg-green-50 rounded-lg">
            <p class="text-2xl font-bold text-green-600">{{ ringkasan.gizi_baik }}</p>
            <p class="text-xs text-gray-500">Gizi Baik</p>
          </div>
          <div class="text-center p-2 bg-orange-50 rounded-lg">
            <p class="text-2xl font-bold text-orange-600">{{ ringkasan.stunting }}</p>
            <p class="text-xs text-gray-500">Stunting</p>
          </div>
          <div class="text-center p-2 bg-purple-50 rounded-lg">
            <p class="text-2xl font-bold text-purple-600">{{ ringkasan.ews_bulan_ini }}</p>
            <p class="text-xs text-gray-500">Peringatan Bulan Ini</p>
          </div>
        </div>

        <!-- EWS menunggak — hanya tampil jika ada -->
        <div v-if="ringkasan.ews_menunggak > 0"
             class="flex items-start gap-2 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
          <span class="text-red-500 text-base mt-0.5">⚠️</span>
          <div>
            <p class="text-sm font-semibold text-red-700">
              {{ ringkasan.ews_menunggak }} peringatan dari bulan lalu belum ditindaklanjuti
            </p>
            <p class="text-xs text-red-500 mt-0.5">
              Segera tindaklanjuti agar tidak menumpuk. Buka halaman EWS untuk detail.
            </p>
          </div>
        </div>

        <div v-else class="flex items-center gap-2 bg-green-50 border border-green-200 rounded-lg px-3 py-2">
          <span class="text-green-500 text-base">✅</span>
          <p class="text-xs text-green-700 font-medium">Tidak ada peringatan menunggak dari bulan sebelumnya</p>
        </div>

      </div>

      <!-- Export -->
      <div class="card space-y-3">
        <p class="font-semibold text-gray-700">Export Data</p>
        
        <!-- Bagian PDF (Tetap Per Bulan) -->
        <div style="padding: 12px; border: 1px solid #fca5a5; border-radius: 8px; margin-bottom: 16px; background-color: #fef2f2;">
          <label style="display: block; font-weight: bold; font-size: 14px; margin-bottom: 8px; color: #b91c1c;">Laporan Cetak (PDF)</label>
          <input v-model="bulanPdf" type="month" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 10px;" :max="maxBulan" />
          <button @click="exportPdf" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px; background-color: #dc2626; color: white; font-weight: bold; border: none; border-radius: 6px; cursor: pointer;">
            📄 Download PDF (Bulan Ini)
          </button>
        </div>

        <!-- Bagian CSV/JSON (Rentang Bulan) -->
        <div style="padding: 12px; border: 1px solid #93c5fd; border-radius: 8px; background-color: #eff6ff;">
          <label style="display: block; font-weight: bold; font-size: 14px; margin-bottom: 8px; color: #1d4ed8;">Laporan Sistem & Analisis (Rentang Bulan)</label>
          
          <div style="display: flex; gap: 10px; margin-bottom: 12px;">
            <div style="flex: 1;">
              <label style="font-size: 12px; color: #333; margin-bottom: 4px; display: block;">Dari Bulan:</label>
              <input v-model="filter.dari" type="month" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" :max="maxBulan" />
            </div>
            <div style="flex: 1;">
              <label style="font-size: 12px; color: #333; margin-bottom: 4px; display: block;">Sampai Bulan:</label>
              <input v-model="filter.sampai" type="month" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" :max="maxBulan" />
            </div>
          </div>

          <div style="display: flex; gap: 10px;">
            <button @click="exportCsv" style="flex: 1; padding: 10px; background-color: #2563eb; color: white; font-weight: bold; border: none; border-radius: 6px; cursor: pointer;">
              📥 Download CSV
            </button>
            <button @click="exportJson" style="flex: 1; padding: 10px; background-color: white; color: #2563eb; border: 1px solid #2563eb; font-weight: bold; border-radius: 6px; cursor: pointer;">
              📥 Download JSON
            </button>
          </div>
        </div>

        <p class="text-xs text-gray-400 mt-2">PDF: laporan lengkap siap cetak · CSV: untuk Excel/Spreadsheet</p>
      </div>

      <!-- Info -->
      <div class="card bg-blue-50 border-blue-100">
        <p class="text-sm font-semibold text-blue-800 mb-1">💡 Cara menggunakan laporan</p>
        <ul class="text-xs text-blue-700 space-y-1 list-disc list-inside">
          <li>Download CSV untuk input ke Excel/Spreadsheet</li>
          <li>Download JSON untuk integrasi sistem eksternal (Puskesmas)</li>
          <li>Data mencakup semua pengukuran dalam rentang yang dipilih</li>
          <li>Z-score dihitung menggunakan standar WHO 2006</li>
        </ul>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Components/UI/AppLayout.vue'

const props = defineProps({ ringkasan: Object })

const now     = new Date()
const currentMonth = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`

const maxBulan = currentMonth
const bulanPdf = ref(currentMonth)

// State khusus untuk rentang bulan CSV & JSON
const filter = ref({
  dari: currentMonth,
  sampai: currentMonth
})

function exportPdf() {
  window.location.href = route('laporan.pdf') + '?' + new URLSearchParams({ bulan: bulanPdf.value })
}

function exportCsv() {
  window.location.href = route('laporan.excel') + '?' + new URLSearchParams({ 
    dari: filter.value.dari, 
    sampai: filter.value.sampai 
  })
}

function exportJson() {
  window.location.href = route('laporan.json') + '?' + new URLSearchParams({ 
    dari: filter.value.dari, 
    sampai: filter.value.sampai 
  })
}
</script>