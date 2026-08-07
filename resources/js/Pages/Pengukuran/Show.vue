<template>
  <AppLayout :title="`Pengukuran — ${pengukuran.balita?.nama}`">
    <div class="p-4 space-y-4 pb-20">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <Link :href="route('balita.show', pengukuran.balita_id)" class="text-gray-500">‹</Link>
          <h1 class="text-lg font-bold text-gray-800">Detail Pengukuran</h1>
        </div>
        <Link v-if="bisaEdit"
              :href="route('pengukuran.edit', pengukuran.id)"
              class="text-xs px-3 py-1.5 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">
          ✏️ Edit
        </Link>
      </div>

      <!-- Hasil -->
      <div class="card space-y-3">
        <div class="flex items-center justify-between">
          <div>
            <p class="font-bold text-gray-800">{{ pengukuran.balita?.nama }}</p>
            <p class="text-xs text-gray-500">{{ pengukuran.tanggal_ukur }} · Umur {{ pengukuran.umur_bulan }} bulan</p>
          </div>
          <span :class="ewsBadge(pengukuran.flag_ews)">{{ pengukuran.flag_ews }}</span>
        </div>

        <!-- Hasil ukur -->
        <div class="grid grid-cols-2 gap-3">
          <div class="text-center p-3 bg-blue-50 rounded-lg">
            <p class="text-2xl font-bold text-blue-700">{{ pengukuran.berat_badan_kg }}</p>
            <p class="text-xs text-gray-500">Berat (kg)</p>
            <p v-if="pengukuran.kenaikan_bb_gram !== null && pengukuran.kenaikan_bb_gram !== undefined"
               class="text-xs font-semibold mt-1"
               :class="pengukuran.kenaikan_bb_gram > 0 ? 'text-green-600' : 'text-red-600'">
              {{ pengukuran.kenaikan_bb_gram > 0 ? '+' : '' }}{{ pengukuran.kenaikan_bb_gram }} gr
            </p>
          </div>
          <div class="text-center p-3 bg-green-50 rounded-lg">
            <p class="text-2xl font-bold text-green-700">{{ pengukuran.tinggi_badan_cm ?? '—' }}</p>
            <p class="text-xs text-gray-500">Tinggi (cm)</p>
          </div>
        </div>

        <!-- Weight Faltering (Permenkes 2/2020) -->
        <div v-if="pengukuran.is_weight_faltering !== null && pengukuran.is_weight_faltering !== undefined"
             :class="['rounded-lg px-3 py-2 flex items-center gap-2 text-xs',
                      pengukuran.is_weight_faltering ? 'bg-red-50 border border-red-200' : 'bg-green-50 border border-green-200']">
          <span>{{ pengukuran.is_weight_faltering ? '⚠️' : '✅' }}</span>
          <span :class="pengukuran.is_weight_faltering ? 'text-red-700 font-semibold' : 'text-green-700'">
            {{ pengukuran.is_weight_faltering ? 'Risiko Gagal Tumbuh' : 'Tumbuh Normal' }}
          </span>
          <span class="text-gray-400">· Weight Increment PMK 2/2020</span>
        </div>

        <!-- Status KBB -->
        <div v-if="pengukuran.status_kbb"
             :class="['rounded-lg p-3 flex items-center gap-3',
                      pengukuran.status_kbb === 'N' ? 'bg-green-50' :
                      pengukuran.status_kbb === 'T' ? 'bg-yellow-50' : 'bg-red-50']">
          <span class="text-2xl">
            {{ pengukuran.status_kbb === 'N' ? '📈' : pengukuran.status_kbb === 'T' ? '➡️' : '📉' }}
          </span>
          <div>
            <p class="text-sm font-semibold"
               :class="pengukuran.status_kbb === 'N' ? 'text-green-700' :
                       pengukuran.status_kbb === 'T' ? 'text-yellow-700' : 'text-red-700'">
              KBB {{ labelKbb(pengukuran.status_kbb) }}
            </p>
            <p class="text-xs text-gray-500">Kenaikan Berat Badan dari pengukuran sebelumnya</p>
          </div>
          <span class="ml-auto text-lg font-bold"
                :class="pengukuran.status_kbb === 'N' ? 'text-green-700' :
                        pengukuran.status_kbb === 'T' ? 'text-yellow-700' : 'text-red-700'">
            {{ pengukuran.status_kbb }}
          </span>
        </div>

        <!-- Z-score 3 indeks -->
        <div class="grid grid-cols-3 gap-2">
          <div class="text-center p-3 bg-gray-50 rounded-lg">
            <p class="text-xl font-bold" :class="zScoreColor(pengukuran.z_score_bb_u)">
              {{ pengukuran.z_score_bb_u ?? '—' }}
            </p>
            <p class="text-xs text-gray-400 mt-0.5">Z-score</p>
            <p class="text-xs font-semibold text-gray-600">BB/U</p>
          </div>
          <div class="text-center p-3 bg-gray-50 rounded-lg">
            <p class="text-xl font-bold" :class="zScoreColor(pengukuran.z_score_tb_u)">
              {{ pengukuran.z_score_tb_u ?? '—' }}
            </p>
            <p class="text-xs text-gray-400 mt-0.5">Z-score</p>
            <p class="text-xs font-semibold text-gray-600">TB/U</p>
          </div>
          <div class="text-center p-3 bg-gray-50 rounded-lg">
            <p class="text-xl font-bold" :class="zScoreColor(pengukuran.z_score_bb_tb)">
              {{ pengukuran.z_score_bb_tb ?? '—' }}
            </p>
            <p class="text-xs text-gray-400 mt-0.5">Z-score</p>
            <p class="text-xs font-semibold text-gray-600">BB/TB</p>
          </div>
          <div class="text-center p-3 bg-gray-50 rounded-lg">
            <p class="text-xl font-bold" :class="zScoreColor(pengukuran.z_score_imt_u)">
              {{ pengukuran.z_score_imt_u ?? '—' }}
            </p>
            <p class="text-xs text-gray-400 mt-0.5">Z-score</p>
            <p class="text-xs font-semibold text-gray-600">IMT/U</p>
          </div>
        </div>

        <!-- IMT nilai -->
        <div v-if="pengukuran.imt_u" class="bg-blue-50 rounded-lg p-2 text-center">
          <span class="text-xs text-blue-500">Indeks Massa Tubuh</span>
          <p class="font-bold text-blue-700">{{ pengukuran.imt_u }} kg/m²</p>
        </div>

        <!-- Status 4 indeks -->
        <div class="grid grid-cols-2 gap-2">
          <div class="text-center p-2 rounded-lg" :class="giziColor(pengukuran.status_gizi).bg">
            <p class="text-xs font-semibold" :class="giziColor(pengukuran.status_gizi).text">
              {{ labelGizi(pengukuran.status_gizi) }}
            </p>
            <p class="text-xs text-gray-400 mt-0.5">BB/U</p>
          </div>
          <div class="text-center p-2 rounded-lg" :class="stuntingColor(pengukuran.status_stunting).bg">
            <p class="text-xs font-semibold" :class="stuntingColor(pengukuran.status_stunting).text">
              {{ labelStunting(pengukuran.status_stunting) }}
            </p>
            <p class="text-xs text-gray-400 mt-0.5">TB/U</p>
          </div>
          <div class="text-center p-2 rounded-lg" :class="wastingColor(pengukuran.status_wasting).bg">
            <p class="text-xs font-semibold" :class="wastingColor(pengukuran.status_wasting).text">
              {{ labelWasting(pengukuran.status_wasting) }}
            </p>
            <p class="text-xs text-gray-400 mt-0.5">BB/TB</p>
          </div>
          <div v-if="pengukuran.status_imt_u" class="text-center p-2 rounded-lg"
               :class="wastingColor(pengukuran.status_imt_u).bg">
            <p class="text-xs font-semibold" :class="wastingColor(pengukuran.status_imt_u).text">
              {{ labelImtU(pengukuran.status_imt_u) }}
            </p>
            <p class="text-xs text-gray-400 mt-0.5">IMT/U</p>
          </div>
        </div>

        <div v-if="pengukuran.catatan" class="bg-gray-50 rounded p-2 text-sm text-gray-600">
          <span class="font-medium">Catatan:</span> {{ pengukuran.catatan }}
        </div>
      </div>

      <!-- Rekomendasi Tindak Lanjut -->
      <div v-if="rekomendasi.length" class="space-y-2">
        <p class="font-semibold text-gray-700 text-sm">📋 Rekomendasi Tindak Lanjut</p>
        <div v-for="(r, i) in rekomendasi" :key="i"
             :class="['card border-l-4',
                      r.warna === 'red'    ? 'border-red-500' :
                      r.warna === 'orange' ? 'border-orange-400' : 'border-yellow-400']">
          <div class="flex items-start gap-2">
            <span class="text-lg leading-none mt-0.5">{{ r.icon }}</span>
            <div>
              <p class="text-sm font-semibold text-gray-800">{{ r.judul }}</p>
              <p class="text-xs text-gray-600 mt-0.5">{{ r.isi }}</p>
              <span :class="['inline-block text-[10px] font-bold uppercase mt-1.5 px-2 py-0.5 rounded-full',
                             r.level === 'rs'        ? 'bg-red-100 text-red-700' :
                             r.level === 'puskesmas' ? 'bg-orange-100 text-orange-700' : 'bg-yellow-100 text-yellow-700']">
                {{ r.level === 'rs' ? '🏥 RS / Spesialis' : r.level === 'puskesmas' ? '🏨 Puskesmas' : '🏘️ Kader → Nakes' }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Peringatan terkait -->
      <div v-if="pengukuran.peringatan?.length" class="space-y-2">
        <p class="font-semibold text-gray-700 text-sm">⚠️ Peringatan Terkait</p>
        <div v-for="p in pengukuran.peringatan" :key="p.id"
             :class="['card border-l-4', p.level_risiko === 'MERAH' ? 'border-red-500' : 'border-yellow-400']">
          <span :class="p.level_risiko === 'MERAH' ? 'badge-merah' : 'badge-kuning'" class="text-xs">{{ p.level_risiko }}</span>
          <p class="text-sm mt-1">{{ p.pesan }}</p>
        </div>
      </div>

      <!-- ── Panel Validasi Nakes ── -->
      <div v-if="bisaValidasi" class="card space-y-3">
        <div class="flex items-center justify-between">
          <p class="font-semibold text-gray-700">🩺 Validasi Nakes</p>
          <span v-if="pengukuran.is_validated"
                class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700">
            ✓ Sudah Divalidasi
          </span>
          <span v-else class="text-xs font-semibold px-2.5 py-1 rounded-full bg-orange-100 text-orange-700">
            Belum Divalidasi
          </span>
        </div>

        <!-- Sudah divalidasi -->
        <template v-if="pengukuran.is_validated">
          <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3 text-sm text-emerald-800">
            <p class="font-semibold">✅ Data pengukuran telah divalidasi</p>
            <p class="text-xs mt-1 text-emerald-600">
              oleh {{ pengukuran.validator?.nama }} · {{ pengukuran.validated_at_label }}
            </p>
          </div>
          <div v-if="pengukuran.catatan_validasi"
               class="bg-gray-50 rounded-lg p-3 text-sm text-gray-700 border border-gray-200">
            <p class="text-xs text-gray-400 mb-1">Catatan validasi:</p>
            {{ pengukuran.catatan_validasi }}
          </div>
        </template>

        <!-- Form validasi -->
        <template v-else>
          <p class="text-xs text-gray-500">
            Periksa data pengukuran di atas. Jika data sudah benar, berikan validasi.
          </p>
          <div>
            <label class="form-label">Catatan <span class="text-gray-400">(opsional)</span></label>
            <textarea v-model="formValidasi.catatan_validasi" class="form-input" rows="2"
                      placeholder="Keterangan, koreksi interpretasi, atau instruksi tindak lanjut..."></textarea>
          </div>
          <button @click="submitValidasi"
                  :disabled="formValidasi.processing"
                  class="w-full py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition">
            ✅ Validasi Data Pengukuran
          </button>
        </template>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/UI/AppLayout.vue'

const props = defineProps({ pengukuran: Object, bisaValidasi: Boolean, bisaEdit: Boolean })

const formValidasi = useForm({ catatan_validasi: '' })

const rekomendasi = computed(() => {
  const p = props.pengukuran
  const items = []

  if (p.status_kbb === 'T' || p.status_kbb === 'O') {
    items.push({
      level: 'kader',
      icon: '🏘️',
      judul: 'Laporkan ke Tenaga Kesehatan',
      isi: 'KBB ' + (p.status_kbb === 'T' ? 'Naik Kurang (T)' : 'Tidak Naik/Turun (O)') + ' — eskalasikan ke Puskesmas untuk penilaian lebih lanjut.',
      warna: 'yellow',
    })
  }

  if (p.is_weight_faltering) {
    items.push({
      level: 'puskesmas',
      icon: '📋',
      judul: 'Asuhan Gizi & Skrining Penyakit Penyerta',
      isi: 'Risiko gagal tumbuh terdeteksi. Lakukan asuhan gizi spesifik dan skrining penyakit penyerta (infeksi berulang, gangguan pencernaan, dll).',
      warna: 'orange',
    })
  }

  if (p.status_gizi === 'GIZI_BURUK' || p.status_gizi === 'GIZI_KURANG') {
    items.push({
      level: 'puskesmas',
      icon: '🔍',
      judul: 'Penilaian Menyeluruh',
      isi: 'Konfirmasi status gizi dengan seluruh indeks (TB/U, BB/TB, IMT/U). Jika terkonfirmasi, lakukan tata laksana gizi sesuai protokol Puskesmas.',
      warna: 'orange',
    })
  }

  const rujukRS = []
  if (p.status_stunting === 'SANGAT_PENDEK') rujukRS.push('Severe Stunting (TB/U < -3SD)')
  if (p.status_wasting === 'SANGAT_KURUS' || p.status_imt_u === 'SANGAT_KURUS') rujukRS.push('Gizi Buruk (BB/TB atau IMT/U < -3SD)')
  if (rujukRS.length) {
    items.push({
      level: 'rs',
      icon: '🏥',
      judul: 'Rujuk ke Dokter Spesialis Anak',
      isi: rujukRS.join(', ') + ' — diperlukan penanganan oleh dokter spesialis.',
      warna: 'red',
    })
  }

  return items
})

function submitValidasi() {
  formValidasi.patch(route('pengukuran.validasi', props.pengukuran.id), {
    onSuccess: () => window.location.reload(),
  })
}

function ewsBadge(flag) {
  return { MERAH: 'badge-merah', KUNING: 'badge-kuning', HIJAU: 'badge-hijau' }[flag] ?? 'badge-hijau'
}

function zScoreColor(z) {
  if (z === null || z === undefined) return 'text-gray-400'
  if (z < -3 || z > 3) return 'text-red-600'
  if (z < -2 || z > 2) return 'text-yellow-600'
  return 'text-green-600'
}

function labelGizi(s) {
  return {
    GIZI_BURUK:   'BB Sangat Kurang',
    GIZI_KURANG:  'BB Kurang',
    GIZI_BAIK:    'BB Normal',
    RISIKO_LEBIH: 'Risiko BB Lebih',
  }[s] ?? '—'
}
function labelStunting(s) {
  return { SANGAT_PENDEK:'Sangat Pendek', PENDEK:'Pendek', NORMAL:'Normal', TINGGI:'Tinggi' }[s] ?? '—'
}
function labelWasting(s) {
  return { SANGAT_KURUS:'Sangat Kurus', KURUS:'Kurus', NORMAL:'Normal',
           BERISIKO_GEMUK:'Berisiko Gemuk', GEMUK:'Gemuk', OBESITAS:'Obesitas' }[s] ?? '—'
}
function labelKbb(s) {
  return { N:'Naik Cukup', T:'Naik Kurang', O:'Tidak Naik/Turun' }[s] ?? '—'
}
function labelImtU(s) {
  return { SANGAT_KURUS:'Gizi Buruk', KURUS:'Gizi Kurang', NORMAL:'Gizi Baik',
           BERISIKO_GEMUK:'Berisiko Gizi Lebih', GEMUK:'Gizi Lebih', OBESITAS:'Obesitas' }[s] ?? '—'
}
function giziColor(s) {
  return {
    GIZI_BURUK:   { bg:'bg-red-50',    text:'text-red-700' },
    GIZI_KURANG:  { bg:'bg-orange-50', text:'text-orange-600' },
    GIZI_BAIK:    { bg:'bg-green-50',  text:'text-green-700' },
    RISIKO_LEBIH: { bg:'bg-yellow-50', text:'text-yellow-700' },
  }[s] ?? { bg:'bg-gray-50', text:'text-gray-500' }
}
function stuntingColor(s) {
  return {
    SANGAT_PENDEK: { bg:'bg-red-50',    text:'text-red-700' },
    PENDEK:        { bg:'bg-orange-50', text:'text-orange-600' },
    NORMAL:        { bg:'bg-green-50',  text:'text-green-700' },
    TINGGI:        { bg:'bg-blue-50',   text:'text-blue-700' },
  }[s] ?? { bg:'bg-gray-50', text:'text-gray-500' }
}
function wastingColor(s) {
  return {
    SANGAT_KURUS:   { bg:'bg-red-50',    text:'text-red-700' },
    KURUS:          { bg:'bg-orange-50', text:'text-orange-600' },
    NORMAL:         { bg:'bg-green-50',  text:'text-green-700' },
    BERISIKO_GEMUK: { bg:'bg-yellow-50', text:'text-yellow-700' },
    GEMUK:          { bg:'bg-orange-50', text:'text-orange-600' },
    OBESITAS:       { bg:'bg-red-50',    text:'text-red-700' },
  }[s] ?? { bg:'bg-gray-50', text:'text-gray-500' }
}
</script>
