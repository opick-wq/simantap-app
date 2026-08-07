<template>
  <AppLayout title="Import Data Balita">
    <div class="p-4 max-w-2xl space-y-5 pb-20">
      <div class="flex items-center gap-3">
        <Link :href="route('balita.index')" class="text-gray-500 text-sm">← Kembali</Link>
        <h1 class="text-base font-bold text-gray-800">Import Data Balita</h1>
      </div>

      <!-- Hasil import -->
      <div v-if="importResult" :class="importResult.errors.length && !importResult.pengukuran_baru
              ? 'bg-red-50 border-red-200 text-red-700'
              : 'bg-green-50 border-green-200 text-green-700'"
           class="border rounded-lg px-4 py-3 space-y-2">
        <p class="font-semibold text-sm">
          ✅ Import selesai —
          {{ importResult.balita_baru }} balita baru,
          {{ importResult.pengukuran_baru }} pengukuran ditambahkan
        </p>
        <div v-if="importResult.errors.length" class="space-y-1">
          <p class="text-xs font-semibold text-orange-700">⚠️ {{ importResult.errors.length }} baris dilewati:</p>
          <ul class="text-xs text-orange-600 space-y-0.5 max-h-40 overflow-y-auto">
            <li v-for="(e, i) in importResult.errors" :key="i">• {{ e }}</li>
          </ul>
        </div>
      </div>

      <!-- Download template -->
      <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 space-y-2">
        <p class="font-semibold text-blue-800 text-sm">📥 Langkah 1 — Download Template</p>
        <p class="text-xs text-blue-700">
          Isi data balita dan hasil ukur menggunakan template Excel berikut.
          Setiap baris = 1 kunjungan pengukuran. Balita yang sama dapat muncul
          di beberapa baris untuk kunjungan berbeda.
        </p>
        <a :href="route('balita.import.template')"
           class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition">
          ⬇ Download Template Excel
        </a>
      </div>

      <!-- Panduan kolom -->
      <div class="card text-xs space-y-2">
        <p class="font-semibold text-gray-700 text-sm">📋 Format Kolom</p>
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-50">
              <th class="px-2 py-1 border border-gray-200">Kolom</th>
              <th class="px-2 py-1 border border-gray-200">Format</th>
              <th class="px-2 py-1 border border-gray-200">Contoh</th>
              <th class="px-2 py-1 border border-gray-200">Wajib</th>
            </tr>
          </thead>
          <tbody class="text-gray-600">
            <tr v-for="k in kolom" :key="k.nama" class="border-b border-gray-100">
              <td class="px-2 py-1 border border-gray-200 font-medium">{{ k.nama }}</td>
              <td class="px-2 py-1 border border-gray-200">{{ k.format }}</td>
              <td class="px-2 py-1 border border-gray-200 text-gray-400">{{ k.contoh }}</td>
              <td class="px-2 py-1 border border-gray-200 text-center">
                <span v-if="k.wajib" class="text-red-500 font-bold">✓</span>
                <span v-else class="text-gray-300">—</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Form upload -->
      <div class="card space-y-4">
        <p class="font-semibold text-gray-700 text-sm">📤 Langkah 2 — Upload File</p>

        <div v-if="posyandu.length > 1">
          <label class="form-label">Posyandu <span class="text-red-500">*</span></label>
          <select v-model="form.posyandu_id" class="form-input" required>
            <option value="">— Pilih posyandu —</option>
            <option v-for="p in posyandu" :key="p.id" :value="p.id">{{ p.nama }}</option>
          </select>
          <p v-if="form.errors.posyandu_id" class="form-error">{{ form.errors.posyandu_id }}</p>
        </div>

        <div>
          <label class="form-label">File Excel (.xlsx) atau CSV <span class="text-red-500">*</span></label>
          <input type="file" accept=".xlsx,.csv" @change="pilihFile"
                 class="block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3
                        file:rounded-lg file:border-0 file:text-sm file:font-semibold
                        file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
          <p v-if="form.errors.file" class="form-error">{{ form.errors.file }}</p>
          <p class="text-xs text-gray-400 mt-1">Maksimal 5 MB</p>
        </div>

        <button @click="upload" :disabled="form.processing || !form.posyandu_id || !fileObj"
                class="btn-primary w-full py-3 disabled:opacity-50">
          {{ form.processing ? 'Memproses...' : '⬆ Upload & Import' }}
        </button>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Components/UI/AppLayout.vue'

const props = defineProps({ posyandu: Array, roleAktor: String })

const defaultPosyandu = props.posyandu.length === 1 ? props.posyandu[0].id : ''
const fileObj = ref(null)

const form = useForm({
  posyandu_id: defaultPosyandu,
  file: null,
})

const importResult = computed(() => usePage().props.flash?.importResult ?? null)

function pilihFile(e) {
  fileObj.value = e.target.files[0] ?? null
  form.file = fileObj.value
}

function upload() {
  if (!fileObj.value || !form.posyandu_id) return
  form.transform(data => ({ ...data, file: fileObj.value }))
    .post(route('balita.import'), {
      forceFormData: true,
      onSuccess: () => { fileObj.value = null },
    })
}

const kolom = [
  { nama: 'NIK Balita',               format: '16 digit angka',     contoh: '3273040000000001', wajib: true },
  { nama: 'Nama Balita',              format: 'Teks',               contoh: 'Anisa Putri',      wajib: true },
  { nama: 'Tanggal Lahir',            format: 'YYYY-MM-DD',         contoh: '2024-01-15',       wajib: true },
  { nama: 'Jenis Kelamin',            format: 'L atau P',           contoh: 'P',                wajib: true },
  { nama: 'Nama Ibu',                 format: 'Teks',               contoh: 'Siti Rahayu',      wajib: false },
  { nama: 'Alamat',                   format: 'Teks',               contoh: 'Jl. Melati No. 1', wajib: false },
  { nama: 'Tanggal Ukur',             format: 'YYYY-MM-DD',         contoh: '2026-07-10',       wajib: true },
  { nama: 'Berat Badan (kg)',         format: 'Angka desimal',      contoh: '8.5',              wajib: true },
  { nama: 'Tinggi Badan (cm)',        format: 'Angka desimal',      contoh: '72.0',             wajib: true },
  { nama: 'Prematur',                 format: 'Y atau N',           contoh: 'N',                wajib: false },
  { nama: 'Usia Gestasi (minggu)',    format: 'Angka (28–36)',      contoh: '35',               wajib: false },
]
</script>
