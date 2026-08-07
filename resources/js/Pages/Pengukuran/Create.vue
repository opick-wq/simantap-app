<template>
  <AppLayout title="Input Pengukuran">

    <!-- ── STEP 1: Pilih Balita (jika belum dipilih) ── -->
    <div v-if="!balita">
      <div class="bg-white border-b px-4 py-3">
        <h1 class="text-base font-bold text-gray-800">Input Pengukuran</h1>
        <p class="text-xs text-gray-500">Pilih balita yang akan diukur</p>
      </div>

      <div class="px-4 py-4 space-y-3">
        <!-- Search -->
        <div class="relative">
          <input v-model="cari" type="text" placeholder="Cari nama, NIK, atau nama ibu..."
                 class="form-input pl-9" />
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">🔍</span>
        </div>

        <div v-if="daftarFiltered.length === 0" class="text-center text-gray-400 py-10 text-sm">
          Tidak ada balita ditemukan
        </div>

        <div v-else class="overflow-x-auto rounded-lg border border-gray-200">
          <table class="w-full text-sm border-collapse min-w-[520px]">
            <thead>
              <tr class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wide">
                <th class="px-3 py-2.5 text-center w-8">No</th>
                <th class="px-3 py-2.5 text-left">Nama</th>
                <th class="px-3 py-2.5 text-left">NIK</th>
                <th class="px-3 py-2.5 text-left">Nama Ibu</th>
                <th class="px-3 py-2.5 text-center">Usia</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(b, i) in daftarFiltered" :key="b.id"
                  @click="pilihBalita(b)"
                  class="border-b border-gray-100 hover:bg-blue-50 cursor-pointer transition-colors">
                <td class="px-3 py-2.5 text-center text-xs text-gray-400">{{ i + 1 }}</td>
                <td class="px-3 py-2.5">
                  <div class="flex items-center gap-2">
                    <span>{{ b.jenis_kelamin === 'L' ? '👦' : '👧' }}</span>
                    <span class="font-semibold text-gray-800">{{ b.nama }}</span>
                  </div>
                </td>
                <td class="px-3 py-2.5 text-xs text-gray-500 font-mono">{{ b.nik_balita ?? '—' }}</td>
                <td class="px-3 py-2.5 text-xs text-gray-500">{{ b.nama_ibu ?? '—' }}</td>
                <td class="px-3 py-2.5 text-center text-xs text-gray-500">{{ umurBalita(b.tanggal_lahir) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── STEP 2: Form Pengukuran ── -->
    <div v-else>
      <div class="bg-white border-b px-4 py-3 flex items-center gap-3">
        <Link href="/pengukuran/create" class="text-gray-500 text-lg">←</Link>
        <div>
          <h1 class="text-base font-bold text-gray-800">Input Pengukuran</h1>
          <p class="text-xs text-gray-500">{{ balita.nama }} · {{ balita.umur_lengkap }}</p>
        </div>
      </div>

      <div class="px-4 py-4 space-y-4">
        <!-- Info balita -->
        <div class="card flex items-center gap-3">
          <div :class="['w-10 h-10 rounded-full flex items-center justify-center text-lg shrink-0',
                         balita.jenis_kelamin === 'L' ? 'bg-blue-100' : 'bg-pink-100']">
            {{ balita.jenis_kelamin === 'L' ? '👦' : '👧' }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-sm text-gray-800">{{ balita.nama }}</p>
            <p class="text-xs text-gray-500">{{ balita.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
            <p class="text-xs text-gray-400 font-mono mt-0.5">NIK: {{ balita.nik_balita ?? '—' }}</p>
          </div>
        </div>

        <!-- Usia saat pengukuran -->
        <div class="card bg-blue-50 border border-blue-100">
          <p class="text-xs text-blue-500 font-medium mb-1">Usia saat pengukuran</p>
          <p class="text-lg font-bold text-blue-800">{{ usiaHariIni }}</p>
          <p class="text-xs text-blue-400 mt-0.5">Lahir: {{ balita.tanggal_lahir_format }}</p>
        </div>

        <!-- Riwayat terakhir -->
        <div v-if="riwayat.length" class="card">
          <p class="text-xs font-medium text-gray-500 mb-2">Pengukuran Terakhir</p>
          <div class="flex gap-4">
            <div>
              <p class="text-xs text-gray-500">Berat Badan</p>
              <p class="font-bold text-gray-800">{{ riwayat[0].berat_badan_kg }} kg</p>
            </div>
            <div>
              <p class="text-xs text-gray-500">Tinggi Badan</p>
              <p class="font-bold text-gray-800">{{ riwayat[0].tinggi_badan_cm }} cm</p>
            </div>
            <div>
              <p class="text-xs text-gray-500">Tanggal</p>
              <p class="font-bold text-gray-800">{{ riwayat[0].tanggal_ukur.split(' ')[0] }}</p>
            </div>
          </div>
        </div>

        <!-- Grafik pertumbuhan -->
        <div v-if="semuaRiwayat && semuaRiwayat.length >= 2" class="card">
          <p class="text-xs font-medium text-gray-500 mb-2">Grafik Pertumbuhan</p>
          <GrowthChart
            :pengukuran="semuaRiwayat"
            :curve-bb-u="curveBbU"
            :curve-tb-u="curveTbU"
            :gender="balita.jenis_kelamin"
          />
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="form-label">Tanggal Pengukuran <span class="text-red-500">*</span></label>
            <input v-model="form.tanggal_ukur" type="date" class="form-input" :max="hari_ini" required />
            <p v-if="errors.tanggal_ukur" class="form-error">{{ errors.tanggal_ukur }}</p>
          </div>

          <div>
            <label class="form-label">Berat Badan (kg) <span class="text-red-500">*</span></label>
            <div class="relative">
              <input v-model.number="form.berat_badan_kg" type="number"
                     step="0.01" min="1" max="50" placeholder="Contoh: 8.5"
                     class="form-input pr-12" required />
              <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">kg</span>
            </div>
            <p v-if="errors.berat_badan_kg" class="form-error">{{ errors.berat_badan_kg }}</p>
          </div>

          <div>
            <label class="form-label">Tinggi / Panjang Badan (cm) <span class="text-red-500">*</span></label>
            <div class="relative">
              <input v-model.number="form.tinggi_badan_cm" type="number"
                     step="0.1" min="40" max="130" placeholder="Contoh: 72.5"
                     class="form-input pr-12" required />
              <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">cm</span>
            </div>
            <p v-if="errors.tinggi_badan_cm" class="form-error">{{ errors.tinggi_badan_cm }}</p>
            <!-- Posisi ukur — Permenkes 2020: ≤24 bln = terlentang (PB), >24 bln = berdiri (TB) -->
            <div class="mt-2">
              <p class="text-xs text-gray-500 mb-1.5">Posisi saat diukur:</p>
              <div class="flex gap-2">
                <button type="button"
                        @click="form.posisi_ukur = 'terlentang'"
                        :class="['flex-1 py-1.5 text-xs font-medium rounded-lg border transition',
                                 form.posisi_ukur === 'terlentang'
                                   ? 'bg-blue-600 text-white border-blue-600'
                                   : 'bg-white text-gray-600 border-gray-300']">
                  🤸 Terlentang (PB)
                </button>
                <button type="button"
                        @click="form.posisi_ukur = 'berdiri'"
                        :class="['flex-1 py-1.5 text-xs font-medium rounded-lg border transition',
                                 form.posisi_ukur === 'berdiri'
                                   ? 'bg-blue-600 text-white border-blue-600'
                                   : 'bg-white text-gray-600 border-gray-300']">
                  🧍 Berdiri (TB)
                </button>
              </div>
              <p v-if="posisiTidakSesuai" class="text-xs text-amber-600 mt-1.5">
                ⚠️ Usia {{ umurBulan }} bln — lazimnya {{ umurBulan <= 24 ? 'terlentang' : 'berdiri' }}.
                Koreksi 0.7 cm akan diterapkan otomatis.
              </p>
            </div>
          </div>

          <details class="card">
            <summary class="text-sm font-medium text-gray-600 cursor-pointer">
              Pengukuran Tambahan (opsional)
            </summary>
            <div class="mt-3 space-y-3">
              <div>
                <label class="form-label">LiLA — Lingkar Lengan Atas (cm)</label>
                <input v-model.number="form.lingkar_lengan_atas_cm" type="number"
                       step="0.1" min="8" max="25" placeholder="Contoh: 14.5" class="form-input" />
              </div>
              <div>
                <label class="form-label">Lingkar Kepala (cm)</label>
                <input v-model.number="form.lingkar_kepala_cm" type="number"
                       step="0.1" min="25" max="60" placeholder="Contoh: 45.0" class="form-input" />
              </div>
            </div>
          </details>

          <div>
            <label class="form-label">Catatan (opsional)</label>
            <textarea v-model="form.catatan" rows="3" class="form-input resize-none"
                      placeholder="Kondisi kesehatan, keterangan tambahan..." />
          </div>

          <button type="submit" :disabled="form.processing"
                  class="btn-primary w-full py-4 text-base font-semibold">
            <span v-if="form.processing">⏳ Menyimpan & menjalankan EWS...</span>
            <span v-else>💾 Simpan Pengukuran</span>
          </button>
        </form>
      </div>
    </div>

  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/UI/AppLayout.vue'
import GrowthChart from '@/Components/Charts/GrowthChart.vue'

const props = defineProps({
  balita:       Object,
  daftarBalita: Array,
  sesi:         Object,
  tanggal:      String,
  riwayat:      Array,
  semuaRiwayat: Array,
  curveBbU:     Array,
  curveTbU:     Array,
})

const cari     = ref('')
const hari_ini = new Date().toISOString().split('T')[0]

const usiaHariIni = computed(() => {
  if (!props.balita?.tanggal_lahir) return '—'
  const lahir = new Date(props.balita.tanggal_lahir)
  const ukur  = form.tanggal_ukur ? new Date(form.tanggal_ukur) : new Date()
  const totalBulan =
    (ukur.getFullYear() - lahir.getFullYear()) * 12 +
    (ukur.getMonth() - lahir.getMonth()) -
    (ukur.getDate() < lahir.getDate() ? 1 : 0)
  const thn = Math.floor(totalBulan / 12)
  const bln = totalBulan % 12
  if (thn === 0) return `${bln} bulan`
  return bln === 0 ? `${thn} tahun` : `${thn} tahun ${bln} bulan`
})

const daftarFiltered = computed(() => {
  if (!cari.value) return props.daftarBalita
  const q = cari.value.toLowerCase()
  return props.daftarBalita.filter(b =>
    b.nama.toLowerCase().includes(q) ||
    (b.nik_balita ?? '').includes(q) ||
    (b.nama_ibu ?? '').toLowerCase().includes(q)
  )
})

function pilihBalita(b) {
  router.get('/pengukuran/create', { balita_id: b.id })
}

function umurBalita(tglLahir) {
  const diff = Math.floor((Date.now() - new Date(tglLahir)) / (1000 * 60 * 60 * 24 * 30.44))
  return diff < 24 ? `${diff} bln` : `${Math.floor(diff/12)} thn ${diff % 12} bln`
}

const form = useForm({
  balita_id:              props.balita?.id ?? null,
  sesi_id:                props.sesi?.id ?? null,
  tanggal_ukur:           props.tanggal,
  berat_badan_kg:         null,
  tinggi_badan_cm:        null,
  posisi_ukur:            'terlentang',
  lingkar_lengan_atas_cm: null,
  lingkar_kepala_cm:      null,
  catatan:                '',
})

const errors = form.errors

// Deteksi ketidaksesuaian posisi ukur dengan usia
const umurBulan = computed(() => {
  if (!props.balita?.tanggal_lahir || !form.tanggal_ukur) return null
  const lahir = new Date(props.balita.tanggal_lahir)
  const ukur  = new Date(form.tanggal_ukur)
  return Math.floor((ukur - lahir) / (1000 * 60 * 60 * 24 * 30.44))
})

const posisiTidakSesuai = computed(() => {
  if (!umurBulan.value || !form.posisi_ukur) return false
  if (umurBulan.value <= 24 && form.posisi_ukur === 'berdiri') return true
  if (umurBulan.value > 24  && form.posisi_ukur === 'terlentang') return true
  return false
})

function submit() {
  form.post('/pengukuran')
}
</script>
