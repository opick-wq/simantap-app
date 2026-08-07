<template>
  <AppLayout>
    <!-- Header -->
    <div class="bg-white border-b px-4 py-3 flex items-center gap-3">
      <Link href="/balita" class="text-gray-500 text-sm">← Daftar</Link>
      <div class="flex-1">
        <h1 class="text-base font-bold text-gray-800">{{ balita.nama }}</h1>
        <p class="text-xs text-gray-500">{{ balita.posyandu.nama }}</p>
      </div>
      <div class="flex gap-2">
        <Link :href="route('balita.edit', balita.id)"
              class="btn-secondary text-xs px-3 py-2">
          ✏️ Edit
        </Link>
        <Link :href="`/pengukuran/create?balita_id=${balita.id}`"
              class="btn-primary text-xs px-3 py-2">
          + Ukur
        </Link>
      </div>
    </div>

    <div class="px-4 py-4 space-y-4">

      <!-- Profil balita -->
      <div class="card">
        <div class="flex items-center gap-3 mb-3">
          <div :class="['w-14 h-14 rounded-full flex items-center justify-center text-3xl',
                         balita.jenis_kelamin === 'L' ? 'bg-blue-100' : 'bg-pink-100']">
            {{ balita.jenis_kelamin === 'L' ? '👦' : '👧' }}
          </div>
          <div>
            <p class="font-bold text-gray-800">{{ balita.nama }}</p>
            <p class="text-xs text-gray-500">
              {{ balita.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }} ·
              Lahir {{ formatTgl(balita.tanggal_lahir) }} ·
              <span class="font-semibold text-blue-600">{{ balita.umur_lengkap }}</span>
            </p>
            <p class="text-xs text-gray-500">Ibu: {{ balita.nama_ibu }}</p>
            <p v-if="akunOrtu?.email" class="text-xs text-gray-400">📧 {{ akunOrtu.email }}</p>
          </div>
        </div>

        <!-- Status terakhir -->
        <div v-if="pengukuranTerakhir" class="pt-3 border-t space-y-3">
          <!-- BB & TB -->
          <div class="grid grid-cols-3 gap-2">
            <div class="text-center">
              <p class="text-lg font-bold text-gray-800">{{ pengukuranTerakhir.berat_badan_kg }}</p>
              <p class="text-xs text-gray-500">BB (kg)</p>
            </div>
            <div class="text-center">
              <p class="text-lg font-bold text-gray-800">{{ pengukuranTerakhir.tinggi_badan_cm ?? '-' }}</p>
              <p class="text-xs text-gray-500">TB (cm)</p>
            </div>
            <div class="text-center">
              <span :class="ewsBadgeClass(pengukuranTerakhir.flag_ews)"
                    class="text-xs font-semibold px-2 py-1 rounded-full">
                {{ pengukuranTerakhir.flag_ews }}
              </span>
              <p class="text-xs text-gray-500 mt-1">Status EWS</p>
            </div>
          </div>
          <!-- Status 3 indeks -->
          <div class="grid grid-cols-3 gap-2 text-center">
            <div class="rounded-lg p-2" :class="giziColor(pengukuranTerakhir.status_gizi).bg">
              <p class="text-xs font-semibold" :class="giziColor(pengukuranTerakhir.status_gizi).text">
                {{ labelGizi(pengukuranTerakhir.status_gizi) }}
              </p>
              <p class="text-xs text-gray-400 mt-0.5">BB/U</p>
              <p class="text-xs font-mono text-gray-500">{{ pengukuranTerakhir.z_score_bb_u ?? '-' }}</p>
            </div>
            <div class="rounded-lg p-2" :class="stuntingColor(pengukuranTerakhir.status_stunting).bg">
              <p class="text-xs font-semibold" :class="stuntingColor(pengukuranTerakhir.status_stunting).text">
                {{ labelStunting(pengukuranTerakhir.status_stunting) }}
              </p>
              <p class="text-xs text-gray-400 mt-0.5">TB/U</p>
              <p class="text-xs font-mono text-gray-500">{{ pengukuranTerakhir.z_score_tb_u ?? '-' }}</p>
            </div>
            <div class="rounded-lg p-2" :class="wastingColor(pengukuranTerakhir.status_wasting).bg">
              <p class="text-xs font-semibold" :class="wastingColor(pengukuranTerakhir.status_wasting).text">
                {{ labelWasting(pengukuranTerakhir.status_wasting) }}
              </p>
              <p class="text-xs text-gray-400 mt-0.5">BB/TB</p>
              <p class="text-xs font-mono text-gray-500">{{ pengukuranTerakhir.z_score_bb_tb ?? '-' }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Grafik pertumbuhan -->
      <div v-if="pengukuran.length >= 2" class="card">
        <p class="card-header">Grafik Pertumbuhan (Standar WHO)</p>
        <GrowthChart
          :pengukuran="pengukuran"
          :curve-bb-u="curveBbU"
          :curve-tb-u="curveTbU"
          :curve-bb-tb="curveBbTb"
          :curve-imt-u="curveImtU"
          :gender="balita.jenis_kelamin"
        />
      </div>

      <div v-else class="card text-center py-6 text-gray-400 text-sm">
        📈 Grafik akan muncul setelah minimal 2 kali pengukuran
      </div>

      <!-- Riwayat pengukuran -->
      <div class="card">
        <p class="card-header">Riwayat Pengukuran</p>
        <div v-if="pengukuran.length === 0" class="text-center py-4 text-gray-400 text-sm">
          Belum ada data pengukuran
        </div>
        <div v-for="p in pengukuranDesc" :key="p.id"
             class="flex items-center justify-between py-3 border-b last:border-0">
          <div>
            <p class="text-sm font-semibold text-gray-800">{{ p.tanggal_ukur }} · {{ p.umur_bulan }} bln</p>
            <p class="text-xs text-gray-500">
              BB: {{ p.berat_badan_kg }} kg · TB: {{ p.tinggi_badan_cm }} cm
            </p>
            <p v-if="p.z_score_bb_u" class="text-xs text-gray-400">
              BB/U: {{ p.z_score_bb_u }} ({{ labelGizi(p.status_gizi) }})
              <span v-if="p.z_score_tb_u"> · TB/U: {{ p.z_score_tb_u }} ({{ labelStunting(p.status_stunting) }})</span>
              <span v-if="p.z_score_bb_tb"> · BB/TB: {{ p.z_score_bb_tb }} ({{ labelWasting(p.status_wasting) }})</span>
            </p>
            <p v-if="p.status_kbb" class="text-xs mt-0.5">
              <span class="font-medium"
                    :class="{ 'text-green-600': p.status_kbb === 'N', 'text-yellow-600': p.status_kbb === 'T', 'text-red-600': p.status_kbb === 'O' }">
                KBB {{ labelKbb(p.status_kbb) }}
              </span>
              <span class="text-gray-400">
                ({{ p.kenaikan_bb_gram > 0 ? '+' : '' }}{{ p.kenaikan_bb_gram }} gr)
              </span>
            </p>
          </div>
          <div class="flex items-center gap-2 flex-wrap">
            <span v-if="p.is_validated"
                  class="text-xs px-2 py-0.5 rounded-full font-semibold bg-emerald-100 text-emerald-700">
              ✓ Divalidasi
            </span>
            <span v-else-if="perluValidasi(p)"
                  class="text-xs px-2 py-0.5 rounded-full font-semibold bg-orange-100 text-orange-700">
              Belum divalidasi
            </span>
            <span v-if="p.flag_ews !== 'HIJAU'" :class="ewsBadgeClass(p.flag_ews)"
                  class="text-xs px-2 py-0.5 rounded-full font-semibold">
              {{ p.flag_ews }}
            </span>
            <Link :href="`/pengukuran/${p.id}`" class="text-blue-600 text-xs">Detail</Link>
            <button v-if="bisaHapusPengukuran(p)"
                    @click="konfirmasiHapusPengukuran(p)"
                    class="text-red-500 text-xs hover:underline">Hapus</button>
          </div>
        </div>
      </div>

      <!-- Riwayat Intervensi -->
      <div v-if="riwayatIntervensi.length" class="card">
        <p class="card-header">Riwayat Tindak Lanjut Klinis</p>
        <div v-for="r in riwayatIntervensi" :key="r.id"
             class="py-3 border-b last:border-0 space-y-2">
          <!-- Header baris -->
          <div class="flex items-start justify-between gap-2">
            <div class="flex items-center gap-2 flex-wrap">
              <span :class="r.status_akhir === 'SELESAI'
                              ? 'bg-green-100 text-green-700'
                              : 'bg-yellow-100 text-yellow-700'"
                    class="text-xs font-semibold px-2 py-0.5 rounded-full">
                {{ r.status_akhir === 'SELESAI' ? 'Selesai' : 'Dalam Proses' }}
              </span>
              <span v-if="r.peringatan_level"
                    :class="r.peringatan_level === 'MERAH' ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-600'"
                    class="text-xs px-2 py-0.5 rounded-full font-medium">
                EWS {{ r.peringatan_level }}
              </span>
            </div>
            <p class="text-xs text-gray-400 shrink-0">{{ r.tanggal }}</p>
          </div>
          <!-- Pemicu peringatan -->
          <p v-if="r.peringatan_pesan" class="text-xs text-gray-500 bg-gray-50 rounded px-2 py-1">
            {{ r.peringatan_pesan }}
          </p>
          <!-- Intervensi yang dilakukan -->
          <div class="flex flex-wrap gap-1.5">
            <span v-for="t in r.jenis_tindakan" :key="t"
                  class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-medium">
              {{ labelTindakan(t) }}
            </span>
          </div>
          <!-- Dilaporkan -->
          <p v-if="r.dilaporkan_ke_atasan" class="text-xs text-blue-600">
            📤 Dilaporkan ke petugas/atasan posyandu
          </p>
          <!-- Catatan -->
          <p v-if="r.catatan" class="text-xs text-gray-600 border-l-2 border-gray-200 pl-2">
            {{ r.catatan }}
          </p>
          <!-- Pencatat -->
          <p class="text-xs text-gray-400">oleh {{ r.pencatat ?? '—' }}</p>
        </div>
      </div>

      <!-- Akun Orang Tua -->
      <div class="card">
        <p class="card-header">Akun Orang Tua</p>

        <!-- Sudah punya akun -->
        <div v-if="akunOrtu" class="space-y-3">
          <div class="flex items-center gap-3 bg-green-50 rounded-lg p-3">
            <div class="w-10 h-10 rounded-full bg-green-200 flex items-center justify-center text-xl">👩</div>
            <div class="flex-1">
              <p class="font-semibold text-gray-800 text-sm">{{ akunOrtu.nama }}</p>
              <p class="text-xs text-gray-500">{{ akunOrtu.nomor_hp }}</p>
              <p v-if="akunOrtu.email" class="text-xs text-gray-400">{{ akunOrtu.email }}</p>
            </div>
            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-semibold">Aktif</span>
          </div>
          <p class="text-xs text-gray-400">Orang tua dapat login dan memantau tumbuh kembang anak ini.</p>
          <button @click="konfirmasiHapusAkun = true"
                  class="text-xs text-red-500 underline">
            Hapus akun orang tua
          </button>

          <!-- Konfirmasi hapus -->
          <div v-if="konfirmasiHapusAkun" class="bg-red-50 rounded-lg p-3 space-y-2">
            <p class="text-sm font-semibold text-red-700">Hapus akun orang tua?</p>
            <p class="text-xs text-red-600">Akun akan dihapus permanen. Orang tua tidak bisa login lagi.</p>
            <div class="flex gap-2">
              <button @click="hapusAkun"
                      class="flex-1 py-2 bg-red-600 text-white text-sm rounded-lg font-semibold">
                Ya, Hapus
              </button>
              <button @click="konfirmasiHapusAkun = false"
                      class="flex-1 py-2 border text-sm rounded-lg text-gray-600">
                Batal
              </button>
            </div>
          </div>
        </div>

        <!-- Belum punya akun -->
        <div v-else class="space-y-3">
          <p class="text-sm text-gray-500">Orang tua belum memiliki akun. Buat akun agar mereka bisa memantau tumbuh kembang anak secara mandiri.</p>

          <div v-if="!showFormAkun">
            <button @click="showFormAkun = true"
                    class="w-full py-2.5 bg-blue-600 text-white text-sm rounded-lg font-semibold">
              + Buat Akun Orang Tua
            </button>
          </div>

          <form v-else @submit.prevent="submitAkun" class="space-y-3">
            <div>
              <label class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
              <input v-model="formAkun.nama" type="text" class="form-input"
                     :placeholder="balita.nama_ibu" required />
            </div>
            <div>
              <label class="form-label">Nomor HP <span class="text-red-500">*</span></label>
              <input v-model="formAkun.nomor_hp" type="tel" class="form-input"
                     placeholder="08xx-xxxx-xxxx" required />
            </div>
            <div>
              <label class="form-label">Email <span class="text-red-500">*</span></label>
              <input v-model="formAkun.email" type="email" class="form-input"
                     placeholder="email@contoh.com" required />
            </div>
            <div>
              <label class="form-label">Password <span class="text-red-500">*</span></label>
              <input v-model="formAkun.password" type="password" class="form-input"
                     placeholder="Min. 6 karakter" required minlength="6" />
            </div>
            <p class="text-xs text-gray-400">
              Login menggunakan email + password di atas.
            </p>
            <div class="flex gap-2">
              <button type="submit" :disabled="formAkun.processing"
                      class="flex-1 py-2.5 bg-blue-600 text-white text-sm rounded-lg font-semibold disabled:opacity-50">
                Simpan Akun
              </button>
              <button type="button" @click="showFormAkun = false"
                      class="flex-1 py-2.5 border text-sm rounded-lg text-gray-600">
                Batal
              </button>
            </div>
            <p v-if="formAkun.errors.akun" class="text-xs text-red-500">{{ formAkun.errors.akun }}</p>
          </form>
        </div>
      </div>

      <!-- ── Panel Usulan Nonaktif ────────────────────────────────── -->
      <div class="card border-orange-200">
        <p class="card-header text-orange-700">Usulan Nonaktif / Hapus Data</p>

        <!-- Ada usulan aktif yang sedang berjalan -->
        <div v-if="usulanAktif" class="space-y-3">
          <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 space-y-1">
            <p class="text-sm font-semibold text-orange-800">
              {{ usulanAktif.label_status }}
            </p>
            <p class="text-xs text-gray-600"><span class="font-medium">Alasan:</span> {{ usulanAktif.alasan }}</p>
            <p class="text-xs text-gray-500">Diusulkan oleh {{ usulanAktif.pengusul }}</p>
          </div>

          <!-- Form tindak lanjut Nakes -->
          <form v-if="roleUser === 'nakes' && usulanAktif.status === 'DIUSULKAN'"
                @submit.prevent="submitNakes" class="space-y-3 border-t pt-3">
            <p class="text-sm font-semibold text-gray-700">Tindak Lanjut Nakes</p>
            <div>
              <label class="form-label">Catatan (opsional)</label>
              <textarea v-model="formNakes.catatan_nakes" rows="2" class="form-input"
                        placeholder="Alasan persetujuan atau penolakan..." />
            </div>
            <div class="flex gap-2">
              <button type="button" @click="formNakes.aksi = 'TOLAK'; submitNakes()"
                      :disabled="formNakes.processing"
                      class="flex-1 py-2 border border-red-300 text-red-600 text-sm rounded-lg hover:bg-red-50">
                Tolak
              </button>
              <button type="button" @click="formNakes.aksi = 'TERUSKAN'; submitNakes()"
                      :disabled="formNakes.processing"
                      class="flex-1 py-2 bg-orange-500 text-white text-sm rounded-lg hover:bg-orange-600">
                Teruskan ke Petugas
              </button>
            </div>
          </form>

          <!-- Form keputusan Petugas -->
          <form v-if="['petugas','admin'].includes(roleUser) && usulanAktif.status === 'DITERUSKAN'"
                @submit.prevent="submitPetugas" class="space-y-3 border-t pt-3">
            <p class="text-sm font-semibold text-gray-700">Keputusan Petugas Puskesmas</p>
            <p class="text-xs text-gray-500">
              Diteruskan Nakes<span v-if="usulanAktif.nakes"> ({{ usulanAktif.nakes }})</span>
              <span v-if="usulanAktif.catatan_nakes"> — "{{ usulanAktif.catatan_nakes }}"</span>
            </p>
            <div>
              <label class="form-label">Catatan (opsional)</label>
              <textarea v-model="formPetugas.catatan_petugas" rows="2" class="form-input"
                        placeholder="Catatan keputusan..." />
            </div>
            <div class="space-y-2">
              <button type="button" @click="formPetugas.aksi = 'SETUJUI_NONAKTIF'; submitPetugas()"
                      :disabled="formPetugas.processing"
                      class="w-full py-2 bg-orange-500 text-white text-sm rounded-lg hover:bg-orange-600">
                Setujui — Nonaktifkan
              </button>
              <button type="button" @click="konfirmasiHapusPermanen = true"
                      :disabled="formPetugas.processing"
                      class="w-full py-2 border border-red-400 text-red-600 text-sm rounded-lg hover:bg-red-50">
                Setujui — Hapus Permanen
              </button>
              <button type="button" @click="formPetugas.aksi = 'TOLAK'; submitPetugas()"
                      :disabled="formPetugas.processing"
                      class="w-full py-2 border text-gray-600 text-sm rounded-lg hover:bg-gray-50">
                Tolak
              </button>
            </div>
          </form>
        </div>

        <!-- Tidak ada usulan aktif -->
        <div v-else class="space-y-3">
          <!-- Kader: buat usulan baru -->
          <div v-if="roleUser === 'kader'">
            <p class="text-sm text-gray-500 mb-3">
              Jika balita ini sudah tidak aktif (pindah, meninggal, atau duplikat data),
              ajukan usulan kepada Nakes untuk menonaktifkan datanya.
            </p>
            <button v-if="!showFormUsulan" @click="showFormUsulan = true"
                    class="w-full py-2.5 border border-orange-300 text-orange-600 text-sm rounded-lg hover:bg-orange-50 font-medium">
              Ajukan Usulan Nonaktif
            </button>
            <form v-if="showFormUsulan" @submit.prevent="submitUsulan" class="space-y-3">
              <div>
                <label class="form-label">Alasan Usulan <span class="text-red-500">*</span></label>
                <textarea v-model="formUsulan.alasan" rows="3" class="form-input" required
                          placeholder="Contoh: Balita pindah domisili ke luar wilayah..." />
                <p v-if="formUsulan.errors.alasan" class="form-error">{{ formUsulan.errors.alasan }}</p>
              </div>
              <div class="flex gap-2">
                <button type="button" @click="showFormUsulan = false"
                        class="flex-1 py-2 border text-sm rounded-lg text-gray-600">
                  Batal
                </button>
                <button type="submit" :disabled="formUsulan.processing"
                        class="flex-1 py-2 bg-orange-500 text-white text-sm rounded-lg font-semibold disabled:opacity-50">
                  Kirim Usulan
                </button>
              </div>
            </form>
          </div>

          <!-- Nakes / Petugas: info jika tidak ada usulan -->
          <p v-else class="text-sm text-gray-400 text-center py-2">
            Belum ada usulan nonaktif untuk balita ini.
          </p>
        </div>

        <!-- Riwayat usulan sebelumnya -->
        <div v-if="riwayatUsulan.length" class="border-t mt-4 pt-3 space-y-2">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Riwayat Usulan</p>
          <div v-for="u in riwayatUsulan" :key="u.id"
               class="text-xs bg-gray-50 rounded-lg p-2 space-y-0.5">
            <div class="flex justify-between">
              <span :class="['font-semibold', u.status === 'DISETUJUI' ? 'text-green-700' : 'text-red-600']">
                {{ u.label_status }} ({{ u.tindakan_akhir ?? '–' }})
              </span>
              <span class="text-gray-400">{{ u.tanggal }}</span>
            </div>
            <p class="text-gray-600">{{ u.alasan }}</p>
            <p v-if="u.catatan_petugas" class="text-gray-400">Catatan: {{ u.catatan_petugas }}</p>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>

  <!-- Modal konfirmasi hapus permanen -->
  <div v-if="konfirmasiHapusPermanen"
       class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
    <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl">
      <p class="text-red-600 font-bold text-base mb-2">Hapus Data Permanen?</p>
      <p class="text-sm text-gray-600 mb-1">
        Semua data balita <strong>{{ balita.nama }}</strong> termasuk riwayat pengukuran
        dan peringatan EWS akan dihapus dan <strong>tidak dapat dikembalikan</strong>.
      </p>
      <p class="text-sm text-gray-600 mb-4">Pastikan ini adalah keputusan yang tepat.</p>
      <div>
        <label class="form-label">Catatan Keputusan (opsional)</label>
        <textarea v-model="formPetugas.catatan_petugas" rows="2" class="form-input mb-3"
                  placeholder="Alasan hapus permanen..." />
      </div>
      <div class="flex gap-3">
        <button @click="konfirmasiHapusPermanen = false" class="btn-secondary flex-1">Batal</button>
        <button @click="formPetugas.aksi = 'SETUJUI_HAPUS'; submitPetugas(); konfirmasiHapusPermanen = false"
                :disabled="formPetugas.processing"
                class="flex-1 py-2 bg-red-600 text-white text-sm rounded-lg font-semibold">
          Ya, Hapus Permanen
        </button>
      </div>
    </div>
  </div>

  <!-- Modal Hapus Pengukuran -->
  <div v-if="hapusPengukuranTarget"
       class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 space-y-4">
      <p class="font-semibold text-gray-800">Hapus Data Pengukuran?</p>
      <p class="text-sm text-gray-500">
        Pengukuran tanggal <strong>{{ hapusPengukuranTarget.tanggal_ukur }}</strong>
        (BB: {{ hapusPengukuranTarget.berat_badan_kg }} kg,
        TB: {{ hapusPengukuranTarget.tinggi_badan_cm }} cm)
        akan dihapus permanen.
      </p>
      <div class="flex gap-3">
        <button @click="hapusPengukuranTarget = null" class="btn-secondary flex-1">Batal</button>
        <button @click="hapusPengukuran()" class="flex-1 py-2 bg-red-600 text-white text-sm rounded-lg font-semibold">
          Ya, Hapus
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import dayjs from 'dayjs'
import 'dayjs/locale/id'
dayjs.locale('id')
import { Link, useForm, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Components/UI/AppLayout.vue'
import GrowthChart from '@/Components/Charts/GrowthChart.vue'

const props = defineProps({
  balita:            Object,
  pengukuran:        Array,
  curveBbU:          Array,
  curveTbU:          Array,
  curveBbTb:         Array,
  curveImtU:         Array,
  riwayatIntervensi: Array,
  akunOrtu:          Object,
  bisaValidasi:      Boolean,
  roleUser:          String,
  usulanAktif:       Object,
  riwayatUsulan:     Array,
  adaUsulanAktif:    Boolean,
})

function formatTgl(tgl) {
  return tgl ? dayjs(tgl).format('D MMMM YYYY') : '-'
}

function perluValidasi(p) {
  return !p.is_validated && (
    p.flag_ews !== 'HIJAU' ||
    !['GIZI_BAIK', 'NORMAL'].includes(p.status_gizi) ||
    !['NORMAL', 'TINGGI'].includes(p.status_stunting)
  )
}

// Hapus Pengukuran
const hapusPengukuranTarget = ref(null)
const authUserId = usePage().props.auth?.user?.id

function bisaHapusPengukuran(p) {
  if (p.is_validated) return false
  const role = props.roleUser
  if (['admin', 'petugas', 'nakes'].includes(role)) return true
  if (role === 'kader') return p.dicatat_oleh === authUserId
  return false
}

function konfirmasiHapusPengukuran(p) {
  hapusPengukuranTarget.value = p
}

function hapusPengukuran() {
  router.delete(route('pengukuran.destroy', hapusPengukuranTarget.value.id), {
    onSuccess: () => { hapusPengukuranTarget.value = null },
  })
}

// Usulan Nonaktif
const showFormUsulan         = ref(false)
const konfirmasiHapusPermanen = ref(false)

const formUsulan = useForm({ alasan: '' })
function submitUsulan() {
  formUsulan.post(route('usulan-nonaktif.store', props.balita.id), {
    onSuccess: () => { showFormUsulan.value = false; formUsulan.reset() },
  })
}

const formNakes = useForm({ aksi: '', catatan_nakes: '' })
function submitNakes() {
  formNakes.patch(route('usulan-nonaktif.nakes', props.usulanAktif.id))
}

const formPetugas = useForm({ aksi: '', catatan_petugas: '' })
function submitPetugas() {
  formPetugas.patch(route('usulan-nonaktif.petugas', props.usulanAktif.id))
}

// Akun Orang Tua
const showFormAkun       = ref(false)
const konfirmasiHapusAkun = ref(false)

const formAkun = useForm({
  nama:     '',
  nomor_hp: '',
  email:    '',
  password: '',
})

function submitAkun() {
  formAkun.post(route('balita.buat-akun-ortu', props.balita.id), {
    onSuccess: () => {
      showFormAkun.value = false
      formAkun.reset()
    },
  })
}

function hapusAkun() {
  router.delete(route('balita.hapus-akun-ortu', props.balita.id), {
    onSuccess: () => { konfirmasiHapusAkun.value = false },
  })
}

const opsiTindakan = [
  { val: 'tambahan_gizi', label: 'Pemberian Tambahan Gizi' },
  { val: 'edukasi',       label: 'Edukasi Gizi & Pola Asuh' },
  { val: 'rujuk',         label: 'Rujuk ke Puskesmas/Faskes' },
  { val: 'konsultasi',    label: 'Konsultasi Bidan/Nakes' },
  { val: 'lainnya',       label: 'Lainnya' },
]

function labelTindakan(val) {
  return opsiTindakan.find(o => o.val === val)?.label ?? val
}

const pengukuranDesc = computed(() =>
  [...props.pengukuran].sort((a, b) => {
    const da = a.tanggal_ukur.split('/').reverse().join('')
    const db = b.tanggal_ukur.split('/').reverse().join('')
    return db.localeCompare(da)
  })
)

const pengukuranTerakhir = computed(() => pengukuranDesc.value[0] ?? null)

function ewsBadgeClass(flag) {
  return {
    MERAH:  'bg-red-100 text-red-700',
    KUNING: 'bg-yellow-100 text-yellow-700',
    HIJAU:  'bg-green-100 text-green-700',
  }[flag] ?? ''
}

function labelGizi(s) {
  return { GIZI_BURUK:'Gizi Buruk', GIZI_KURANG:'Gizi Kurang', GIZI_BAIK:'Normal',
           RISIKO_LEBIH:'Risiko BB Lebih' }[s] ?? '-'
}
function labelStunting(s) {
  return { SANGAT_PENDEK:'Sangat Pendek', PENDEK:'Pendek', NORMAL:'Normal', TINGGI:'Tinggi' }[s] ?? '-'
}
function labelWasting(s) {
  return { SANGAT_KURUS:'Sangat Kurus', KURUS:'Kurus', NORMAL:'Normal',
           BERISIKO_GEMUK:'Berisiko Gemuk', GEMUK:'Gemuk', OBESITAS:'Obesitas' }[s] ?? '-'
}
function labelKbb(s) {
  return { N:'Naik Cukup', T:'Naik Kurang', O:'Tidak Naik/Turun' }[s] ?? '-'
}

function giziColor(s) {
  return {
    GIZI_BURUK:     { bg: 'bg-red-50',    text: 'text-red-700' },
    GIZI_KURANG:    { bg: 'bg-orange-50', text: 'text-orange-600' },
    GIZI_BAIK:      { bg: 'bg-green-50',  text: 'text-green-700' },
    RISIKO_LEBIH:   { bg: 'bg-yellow-50', text: 'text-yellow-700' },
  }[s] ?? { bg: 'bg-gray-50', text: 'text-gray-500' }
}
function stuntingColor(s) {
  return {
    SANGAT_PENDEK: { bg: 'bg-red-50',    text: 'text-red-700' },
    PENDEK:        { bg: 'bg-orange-50', text: 'text-orange-600' },
    NORMAL:        { bg: 'bg-green-50',  text: 'text-green-700' },
    TINGGI:        { bg: 'bg-blue-50',   text: 'text-blue-700' },
  }[s] ?? { bg: 'bg-gray-50', text: 'text-gray-500' }
}
function wastingColor(s) {
  return {
    SANGAT_KURUS:   { bg: 'bg-red-50',    text: 'text-red-700' },
    KURUS:          { bg: 'bg-orange-50', text: 'text-orange-600' },
    NORMAL:         { bg: 'bg-green-50',  text: 'text-green-700' },
    BERISIKO_GEMUK: { bg: 'bg-yellow-50', text: 'text-yellow-700' },
    GEMUK:          { bg: 'bg-orange-50', text: 'text-orange-600' },
    OBESITAS:       { bg: 'bg-red-50',    text: 'text-red-700' },
  }[s] ?? { bg: 'bg-gray-50', text: 'text-gray-500' }
}
</script>
