<template>
  <AppLayout title="Detail Peringatan">
    <div class="p-4 space-y-4 max-w-lg pb-20">
      <!-- Kembali -->
      <div class="flex items-center gap-3">
        <Link href="/peringatan" class="text-gray-500 text-sm">← Peringatan</Link>
        <span :class="p.level_risiko === 'MERAH' ? 'badge-merah' : 'badge-kuning'">
          {{ p.level_risiko }}
        </span>
        <span :class="statusBadge(p.status_tindak_lanjut)"
              class="text-xs font-semibold px-2.5 py-1 rounded-full">
          {{ statusLabel(p.status_tindak_lanjut) }}
        </span>
      </div>

      <!-- Info balita -->
      <div :class="['card border-l-4', p.level_risiko === 'MERAH' ? 'border-red-500' : 'border-yellow-400']">
        <div class="flex items-center gap-3 mb-3">
          <div :class="['w-12 h-12 rounded-full flex items-center justify-center text-2xl',
                         p.balita.jenis_kelamin === 'L' ? 'bg-blue-100' : 'bg-pink-100']">
            {{ p.balita.jenis_kelamin === 'L' ? '👦' : '👧' }}
          </div>
          <div>
            <p class="font-bold text-gray-800">{{ p.balita.nama }}</p>
            <p class="text-xs text-gray-500">{{ p.balita.posyandu?.nama }}</p>
          </div>
        </div>

        <div class="bg-gray-50 rounded-lg p-3 space-y-1">
          <p class="text-sm font-semibold text-gray-700">{{ p.jenis_peringatan }}</p>
          <p class="text-sm text-gray-600">{{ p.pesan }}</p>
          <p class="text-xs text-gray-400 mt-1">Terdeteksi {{ p.created_at }}</p>
        </div>

        <div v-if="p.pengukuran" class="mt-3 pt-3 border-t grid grid-cols-3 gap-2 text-center text-sm">
          <div>
            <p class="font-bold text-gray-800">{{ p.pengukuran.berat_badan_kg }}</p>
            <p class="text-xs text-gray-400">BB (kg)</p>
          </div>
          <div>
            <p class="font-bold text-gray-800">{{ p.pengukuran.tinggi_badan_cm }}</p>
            <p class="text-xs text-gray-400">TB (cm)</p>
          </div>
          <div>
            <p class="font-bold" :class="zScoreColor(p.pengukuran.z_score_bb_u)">
              {{ p.pengukuran.z_score_bb_u ?? '-' }}
            </p>
            <p class="text-xs text-gray-400">Z BB/U</p>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════
           FORM KADER — Laporan Operasional
      ══════════════════════════════════════════ -->
      <div v-if="roleUser === 'kader'" class="card space-y-4">
        <p class="font-semibold text-gray-700">📋 Laporan Kader</p>

        <!-- Sudah dilaporkan -->
        <template v-if="p.status_tindak_lanjut !== 'BELUM'">
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 space-y-2">
            <p class="text-sm font-semibold text-blue-800">✅ Sudah dilaporkan ke Nakes</p>
            <p class="text-xs text-blue-600">oleh {{ p.pelapor_nama }} · {{ p.dilaporkan_pada }}</p>
          </div>
          <div class="space-y-2 text-sm">
            <div class="flex items-center gap-2">
              <span :class="p.sudah_verifikasi_ulang ? 'text-green-600' : 'text-gray-400'">
                {{ p.sudah_verifikasi_ulang ? '✓' : '○' }}
              </span>
              <span class="text-gray-700">Pengukuran sudah diverifikasi ulang</span>
            </div>
            <div class="flex items-center gap-2">
              <span :class="p.sudah_eskalasi_nakes ? 'text-green-600' : 'text-gray-400'">
                {{ p.sudah_eskalasi_nakes ? '✓' : '○' }}
              </span>
              <span class="text-gray-700">Sudah diarahkan ke Nakes/Bidan</span>
            </div>
          </div>
          <div v-if="p.catatan_kader"
               class="bg-gray-50 rounded-lg p-3 text-sm text-gray-700 border border-gray-200">
            <p class="text-xs text-gray-400 mb-1">Catatan kader:</p>
            {{ p.catatan_kader }}
          </div>

          <!-- Tampilkan status penanganan nakes untuk kader -->
          <div v-if="p.status_tindak_lanjut === 'DALAM_PROSES'"
               class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-sm text-yellow-800">
            🔄 Nakes sedang menangani kasus ini
          </div>
          <div v-else-if="p.status_tindak_lanjut === 'SELESAI'"
               class="bg-green-50 border border-green-200 rounded-lg p-3 text-sm text-green-800">
            ✅ Nakes telah menyelesaikan penanganan
          </div>
        </template>

        <!-- Form laporan kader (hanya jika BELUM) -->
        <template v-else>
          <!-- Link edit pengukuran jika belum divalidasi -->
          <div v-if="!p.pengukuran_is_validated"
               class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg p-3">
            <div>
              <p class="text-sm font-medium text-gray-700">Salah input angka?</p>
              <p class="text-xs text-gray-400">Edit sebelum dilaporkan ke Nakes</p>
            </div>
            <Link :href="route('pengukuran.edit', p.pengukuran_id)"
                  class="text-xs px-3 py-1.5 border border-blue-300 text-blue-600 rounded-lg hover:bg-blue-50 whitespace-nowrap">
              ✏️ Edit Pengukuran
            </Link>
          </div>

          <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm text-amber-800">
            <p class="font-semibold mb-1">⚠️ Langkah yang harus dilakukan:</p>
            <ol class="list-decimal list-inside space-y-1 text-xs">
              <li>Verifikasi ulang pengukuran — pastikan timbangan & alat benar</li>
              <li>Arahkan anak & orang tua ke meja Nakes/Bidan <strong>hari ini</strong></li>
              <li>Sampaikan ke orang tua: <em>"Berat badan perlu dipantau lebih dekat, mari ketemu Bu Bidan"</em></li>
              <li>Jangan sebut diagnosis — itu wewenang Nakes</li>
            </ol>
          </div>

          <div class="space-y-3">
            <!-- Ceklis verifikasi ulang -->
            <label :class="['flex items-start gap-3 p-3 rounded-lg border-2 cursor-pointer transition',
                            formKader.sudah_verifikasi_ulang ? 'border-blue-500 bg-blue-50' : 'border-gray-200']">
              <input type="checkbox" v-model="formKader.sudah_verifikasi_ulang"
                     class="mt-0.5 w-4 h-4 accent-blue-600" />
              <div>
                <p class="text-sm font-medium text-gray-700">Sudah verifikasi ulang pengukuran</p>
                <p class="text-xs text-gray-400">Timbangan, posisi alat, dan angka sudah dicek kembali</p>
              </div>
            </label>

            <!-- Ceklis eskalasi -->
            <label :class="['flex items-start gap-3 p-3 rounded-lg border-2 cursor-pointer transition',
                            formKader.sudah_eskalasi_nakes ? 'border-blue-500 bg-blue-50' : 'border-gray-200']">
              <input type="checkbox" v-model="formKader.sudah_eskalasi_nakes"
                     class="mt-0.5 w-4 h-4 accent-blue-600" />
              <div>
                <p class="text-sm font-medium text-gray-700">Sudah mengarahkan ke Nakes/Bidan</p>
                <p class="text-xs text-gray-400">Anak dan orang tua sudah diarahkan ke meja pelayanan kesehatan</p>
              </div>
            </label>

            <!-- Catatan -->
            <div>
              <label class="form-label">Catatan <span class="text-gray-400">(opsional)</span></label>
              <textarea v-model="formKader.catatan_kader" class="form-input" rows="3"
                        placeholder="Kondisi anak saat ditimbang, respons orang tua, nomor kontak yang bisa dihubungi..."></textarea>
            </div>

            <button @click="laporKader"
                    :disabled="formKader.processing || !formKader.sudah_verifikasi_ulang"
                    :class="['w-full py-3 text-sm font-semibold rounded-lg transition',
                             formKader.sudah_verifikasi_ulang
                               ? 'bg-blue-600 text-white hover:bg-blue-700'
                               : 'bg-gray-200 text-gray-400 cursor-not-allowed']">
              📤 Laporkan ke Nakes
            </button>
            <p v-if="!formKader.sudah_verifikasi_ulang" class="text-xs text-center text-gray-400">
              Pastikan pengukuran sudah diverifikasi ulang sebelum melapor
            </p>
          </div>
        </template>
      </div>

      <!-- ══════════════════════════════════════════
           FORM NAKES — Intervensi Klinis
      ══════════════════════════════════════════ -->
      <div v-if="['nakes','petugas','admin'].includes(roleUser)" class="card space-y-4">
        <div class="flex items-center justify-between">
          <p class="font-semibold text-gray-700">🩺 Tindak Lanjut Klinis</p>
          <span :class="statusBadge(p.status_tindak_lanjut)"
                class="text-xs font-semibold px-2 py-0.5 rounded-full">
            {{ statusLabel(p.status_tindak_lanjut) }}
          </span>
        </div>

        <!-- Info laporan kader -->
        <div v-if="p.status_tindak_lanjut === 'DILAPORKAN'"
             class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm text-blue-800">
          <p class="font-semibold">📋 Dilaporkan kader: {{ p.pelapor_nama }}</p>
          <p class="text-xs mt-1">
            <span :class="p.sudah_verifikasi_ulang ? 'text-green-700' : 'text-red-600'">
              {{ p.sudah_verifikasi_ulang ? '✓' : '✗' }} Verifikasi ulang pengukuran
            </span>
            ·
            <span :class="p.sudah_eskalasi_nakes ? 'text-green-700' : 'text-orange-600'">
              {{ p.sudah_eskalasi_nakes ? '✓' : '○' }} Eskalasi ke Nakes
            </span>
          </p>
          <p v-if="p.catatan_kader" class="text-xs mt-1 text-blue-700 italic">"{{ p.catatan_kader }}"</p>
        </div>

        <!-- Sudah selesai -->
        <template v-if="p.status_tindak_lanjut === 'SELESAI'">
          <div v-if="p.jenis_tindakan?.length" class="space-y-1">
            <p class="text-xs text-gray-500 font-medium">Intervensi yang dilakukan:</p>
            <div class="flex flex-wrap gap-2">
              <span v-for="t in p.jenis_tindakan" :key="t"
                    class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 font-medium">
                {{ labelTindakan(t) }}
              </span>
            </div>
          </div>
          <div v-if="p.catatan_petugas"
               class="bg-gray-50 rounded-lg p-3 text-sm text-gray-700 border border-gray-200">
            <p class="text-xs text-gray-400 mb-1">Catatan klinis:</p>
            {{ p.catatan_petugas }}
          </div>
          <div class="bg-green-50 rounded-lg p-3 text-sm text-green-700 text-center font-medium">
            ✅ Penanganan klinis selesai
          </div>
        </template>

        <!-- Form intervensi nakes -->
        <template v-else>
          <!-- Pilihan intervensi -->
          <div>
            <label class="form-label">
              Intervensi yang dilakukan
              <span v-if="formNakes.status_tindak_lanjut === 'SELESAI'" class="text-red-500"> *</span>
            </label>
            <div class="grid grid-cols-2 gap-2 mt-1">
              <label v-for="opt in opsiTindakanNakes" :key="opt.val"
                     :class="['flex items-center gap-2 p-3 rounded-lg border-2 cursor-pointer transition-colors',
                              formNakes.jenis_tindakan.includes(opt.val)
                                ? 'border-emerald-500 bg-emerald-50'
                                : 'border-gray-200 bg-white hover:border-gray-300']">
                <input type="checkbox" :value="opt.val" v-model="formNakes.jenis_tindakan" class="hidden" />
                <span class="text-lg leading-none">{{ opt.icon }}</span>
                <span class="text-xs font-medium text-gray-700 leading-tight">{{ opt.label }}</span>
              </label>
            </div>
            <p v-if="formNakes.errors?.jenis_tindakan" class="form-error mt-1">
              {{ formNakes.errors.jenis_tindakan }}
            </p>
          </div>

          <!-- Catatan klinis -->
          <div>
            <label class="form-label">Catatan klinis <span class="text-gray-400">(opsional)</span></label>
            <textarea v-model="formNakes.catatan_petugas" class="form-input" rows="3"
                      placeholder="Temuan klinis, keputusan rujukan, instruksi ke kader..."></textarea>
          </div>

          <!-- Tombol -->
          <div class="flex gap-2">
            <button v-if="['DILAPORKAN','BELUM'].includes(p.status_tindak_lanjut)"
                    @click="updateNakes('DALAM_PROSES')"
                    :disabled="formNakes.processing"
                    class="flex-1 py-2.5 text-sm border-2 border-yellow-400 text-yellow-700 rounded-lg font-medium hover:bg-yellow-50 transition">
              🔄 Dalam Proses
            </button>
            <button @click="updateNakes('SELESAI')"
                    :disabled="formNakes.processing || formNakes.jenis_tindakan.length === 0"
                    :class="['flex-1 py-2.5 text-sm rounded-lg font-semibold transition',
                             formNakes.jenis_tindakan.length > 0
                               ? 'bg-emerald-600 text-white hover:bg-emerald-700'
                               : 'bg-gray-200 text-gray-400 cursor-not-allowed']">
              ✅ Selesai Ditangani
            </button>
          </div>
          <p v-if="formNakes.jenis_tindakan.length === 0" class="text-xs text-center text-gray-400">
            Pilih minimal 1 intervensi untuk menandai selesai
          </p>
        </template>
      </div>

      <!-- ── Panel Verifikasi Petugas ── -->
      <div v-if="bisaVerifikasi && p.status_tindak_lanjut === 'SELESAI'" class="card space-y-3">
        <div class="flex items-center justify-between">
          <p class="font-semibold text-gray-700">Verifikasi Petugas</p>
          <span :class="verifikasiBadge(p.status_verifikasi)"
                class="text-xs font-semibold px-2.5 py-1 rounded-full">
            {{ verifikasiLabel(p.status_verifikasi) }}
          </span>
        </div>

        <template v-if="p.status_verifikasi !== 'BELUM'">
          <div :class="p.status_verifikasi === 'DISETUJUI'
                        ? 'bg-green-50 border border-green-200 text-green-800'
                        : 'bg-red-50 border border-red-200 text-red-800'"
               class="rounded-lg p-3 text-sm">
            <p class="font-semibold">
              {{ p.status_verifikasi === 'DISETUJUI' ? '✅ Disetujui' : '❌ Ditolak — perlu tindak ulang' }}
            </p>
            <p class="text-xs mt-1 opacity-70">
              oleh {{ p.diverifikasi_oleh_nama }} · {{ p.diverifikasi_pada }}
            </p>
          </div>
          <div v-if="p.catatan_verifikasi"
               class="bg-gray-50 rounded-lg p-3 text-sm text-gray-700 border">
            <p class="text-xs text-gray-400 mb-1">Catatan verifikasi:</p>
            {{ p.catatan_verifikasi }}
          </div>
        </template>

        <template v-else>
          <p class="text-xs text-gray-500">Periksa tindak lanjut nakes, lalu berikan keputusan.</p>
          <div>
            <label class="form-label">Catatan <span class="text-gray-400">(opsional)</span></label>
            <textarea v-model="formVerif.catatan_verifikasi" class="form-input" rows="2"
                      placeholder="Komentar atau instruksi tambahan..."></textarea>
          </div>
          <div class="flex gap-2">
            <button @click="verifikasi('DITOLAK')" :disabled="formVerif.processing"
                    class="flex-1 py-2.5 text-sm border-2 border-red-300 text-red-700 rounded-lg font-medium hover:bg-red-50 transition">
              ❌ Tolak
            </button>
            <button @click="verifikasi('DISETUJUI')" :disabled="formVerif.processing"
                    class="flex-1 py-2.5 text-sm bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
              ✅ Setujui
            </button>
          </div>
        </template>
      </div>

      <!-- Riwayat tindak lanjut nakes -->
      <div v-if="riwayatTindakan.length" class="card space-y-3">
        <p class="font-semibold text-gray-700">Riwayat Tindak Lanjut</p>
        <div v-for="r in riwayatTindakan" :key="r.id"
             class="border border-gray-100 rounded-lg p-3 space-y-2 bg-gray-50">
          <div class="flex items-center justify-between">
            <span :class="r.status_akhir === 'SELESAI' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
                  class="text-xs font-semibold px-2 py-0.5 rounded-full">
              {{ r.status_akhir === 'SELESAI' ? 'Selesai' : 'Dalam Proses' }}
            </span>
            <span class="text-xs text-gray-400">{{ r.created_at }} · {{ r.pencatat }}</span>
          </div>
          <div class="flex flex-wrap gap-1.5">
            <span v-for="t in r.jenis_tindakan" :key="t"
                  class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">
              {{ labelTindakan(t) }}
            </span>
          </div>
          <p v-if="r.catatan" class="text-xs text-gray-600 border-t border-gray-200 pt-2">{{ r.catatan }}</p>
        </div>
      </div>

      <Link :href="route('balita.show', p.balita.id)"
            class="block text-center py-3 border-2 border-blue-200 text-blue-600 rounded-xl text-sm font-semibold">
        Lihat Profil Lengkap {{ p.balita.nama }}
      </Link>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/UI/AppLayout.vue'

const props = defineProps({
  peringatan:      Object,
  riwayatTindakan: Array,
  bisaVerifikasi:  Boolean,
  roleUser:        String,
})

const p = props.peringatan

// Form kader — laporan operasional
const formKader = useForm({
  sudah_verifikasi_ulang: p.sudah_verifikasi_ulang ?? false,
  sudah_eskalasi_nakes:   p.sudah_eskalasi_nakes ?? false,
  catatan_kader:          p.catatan_kader ?? '',
})

// Form nakes — intervensi klinis
const formNakes = useForm({
  status_tindak_lanjut: '',
  catatan_petugas:      p.catatan_petugas ?? '',
  jenis_tindakan:       p.jenis_tindakan ?? [],
  dilaporkan_ke_atasan: p.dilaporkan_ke_atasan ?? false,
})

// Form verifikasi petugas
const formVerif = useForm({ status_verifikasi: '', catatan_verifikasi: '' })

const opsiTindakanNakes = [
  { val: 'tambahan_gizi', icon: '🥗', label: 'PMT / Tambahan Gizi' },
  { val: 'edukasi',       icon: '📋', label: 'Edukasi Gizi & Pola Asuh' },
  { val: 'rujuk',         icon: '🏥', label: 'Rujuk ke Puskesmas/Faskes' },
  { val: 'konsultasi',    icon: '👩‍⚕️', label: 'Konsultasi Klinis' },
  { val: 'suplemen',      icon: '💊', label: 'Pemberian Suplemen' },
  { val: 'lainnya',       icon: '📝', label: 'Lainnya' },
]

function laporKader() {
  formKader.patch(route('peringatan.lapor', p.id), {
    onSuccess: () => window.location.reload(),
  })
}

function updateNakes(status) {
  formNakes.status_tindak_lanjut = status
  formNakes.patch(route('peringatan.update', p.id), {
    onSuccess: () => window.location.reload(),
  })
}

function verifikasi(status) {
  formVerif.status_verifikasi = status
  formVerif.patch(route('peringatan.verifikasi', p.id), {
    onSuccess: () => window.location.reload(),
  })
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

function verifikasiLabel(s) {
  return { BELUM: 'Menunggu Verifikasi', DISETUJUI: 'Disetujui', DITOLAK: 'Ditolak' }[s] ?? s
}

function verifikasiBadge(s) {
  return {
    BELUM:     'bg-gray-100 text-gray-600',
    DISETUJUI: 'bg-green-100 text-green-700',
    DITOLAK:   'bg-red-100 text-red-700',
  }[s] ?? 'bg-gray-100 text-gray-600'
}

function labelTindakan(val) {
  return opsiTindakanNakes.find(o => o.val === val)?.label ?? val
}

function zScoreColor(z) {
  if (z === null || z === undefined) return 'text-gray-400'
  if (z < -3) return 'text-red-600'
  if (z < -2) return 'text-orange-500'
  return 'text-green-600'
}
</script>
