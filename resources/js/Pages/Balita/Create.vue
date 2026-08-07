<template>
  <AppLayout title="Daftar Balita Baru">
    <div class="p-4 pb-20">
      <div class="flex items-center gap-3 mb-5">
        <button @click="$inertia.visit(route('balita.index'))" class="text-gray-500">‹</button>
        <h1 class="text-lg font-bold text-gray-800">Daftar Balita Baru</h1>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <!-- Posyandu -->
        <div v-if="posyandu.length > 1">
          <label class="form-label">Posyandu *</label>
          <select v-model="form.posyandu_id" class="form-input" required>
            <option value="">— Pilih Posyandu —</option>
            <option v-for="p in posyandu" :key="p.id" :value="p.id">{{ p.nama }}</option>
          </select>
          <p v-if="form.errors.posyandu_id" class="form-error">{{ form.errors.posyandu_id }}</p>
        </div>
        <input v-else type="hidden" v-model="form.posyandu_id" />

        <!-- Identitas Balita -->
        <div class="card space-y-3">
          <p class="font-semibold text-gray-700 text-sm">👶 Identitas Balita</p>
          <div>
            <label class="form-label">Nama Lengkap *</label>
            <input v-model="form.nama" type="text" class="form-input" required placeholder="Nama balita" />
            <p v-if="form.errors.nama" class="form-error">{{ form.errors.nama }}</p>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="form-label">Tanggal Lahir *</label>
              <input v-model="form.tanggal_lahir" type="date" class="form-input" required :max="today" />
            </div>
            <div>
              <label class="form-label">Jenis Kelamin *</label>
              <select v-model="form.jenis_kelamin" class="form-input" required>
                <option value="">Pilih</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
              </select>
            </div>
          </div>
          <div>
            <label class="form-label">NIK Balita <span class="text-red-500">*</span></label>
            <input v-model="form.nik_balita" type="text" class="form-input"
                   placeholder="16 digit NIK (dari KK/Akta Lahir)"
                   maxlength="16" minlength="16" pattern="\d{16}" required
                   inputmode="numeric" />
            <p v-if="form.errors.nik_balita" class="form-error">{{ form.errors.nik_balita }}</p>
            <p class="text-xs text-gray-400 mt-1">NIK harus 16 digit angka dan belum terdaftar</p>
          </div>
        </div>

        <!-- Identitas Ibu -->
        <div class="card space-y-3">
          <p class="font-semibold text-gray-700 text-sm">👩 Identitas Ibu</p>
          <div>
            <label class="form-label">Nama Ibu *</label>
            <input v-model="form.nama_ibu" type="text" class="form-input" required placeholder="Nama ibu kandung" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="form-label">No. HP Ibu</label>
              <input v-model="form.nomor_hp_ibu" type="tel" class="form-input" placeholder="08xx" />
            </div>
            <div>
              <label class="form-label">NIK Ibu</label>
              <input v-model="form.nik_ibu" type="text" class="form-input" placeholder="Opsional" maxlength="16" />
            </div>
          </div>
          <div>
            <label class="form-label">Nama Ayah</label>
            <input v-model="form.nama_ayah" type="text" class="form-input" placeholder="Opsional" />
          </div>
        </div>

        <!-- Alamat -->
        <div class="card space-y-3">
          <p class="font-semibold text-gray-700 text-sm">📍 Alamat</p>
          <div>
            <label class="form-label">Alamat Lengkap *</label>
            <textarea v-model="form.alamat" class="form-input" rows="2" required placeholder="Jl. ..."></textarea>
          </div>
          <div>
            <label class="form-label">RT/RW</label>
            <input v-model="form.rt_rw" type="text" class="form-input" placeholder="03/02" />
          </div>
        </div>

        <!-- Data Lahir -->
        <div class="card space-y-3">
          <p class="font-semibold text-gray-700 text-sm">📊 Data Lahir (Opsional)</p>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="form-label">Berat Lahir (kg)</label>
              <input v-model="form.berat_lahir_gram" type="number" class="form-input" placeholder="2.8" min="0.5" max="6" step="0.01" />
              <p class="text-xs text-gray-400 mt-1">Contoh: 2.8 untuk 2,8 kg</p>
            </div>
            <div>
              <label class="form-label">Panjang Lahir (cm)</label>
              <input v-model="form.panjang_lahir_cm" type="number" class="form-input" placeholder="48" min="30" max="70" step="0.1" />
            </div>
          </div>
          <div class="flex items-center gap-2">
            <input v-model="form.prematur" type="checkbox" id="prematur" class="w-4 h-4 text-blue-600 rounded" />
            <label for="prematur" class="text-sm text-gray-600">Lahir prematur (&lt; 37 minggu)</label>
          </div>
          <div v-if="form.prematur" class="space-y-2">
            <div>
              <label class="form-label">Usia Gestasi (minggu)</label>
              <input v-model="form.usia_gestasi_minggu" type="number" class="form-input" placeholder="34" min="24" max="36" />
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-lg px-3 py-2 text-xs text-blue-700 space-y-1">
              <p class="font-semibold">ℹ️ Mengapa usia gestasi penting?</p>
              <p>Bayi prematur dinilai menggunakan <strong>usia koreksi</strong>, bukan usia kalender.</p>
              <p>Rumus: <strong>Usia Koreksi = Usia Kalender − (40 − Usia Gestasi) minggu</strong></p>
              <p>Contoh: bayi usia 6 bulan, lahir di 32 minggu → usia koreksi = 6 bulan − 2 bulan = <strong>4 bulan</strong></p>
              <p>Sistem akan otomatis menggunakan usia koreksi untuk menghitung Z-score hingga anak berusia 2 tahun.</p>
            </div>
          </div>
        </div>

        <button type="submit" :disabled="form.processing" class="btn-primary w-full py-3">
          {{ form.processing ? 'Menyimpan...' : 'Simpan Data Balita' }}
        </button>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/UI/AppLayout.vue'

const props = defineProps({ posyandu: Array })

const today = new Date().toISOString().split('T')[0]

const form = useForm({
  posyandu_id: props.posyandu.length === 1 ? props.posyandu[0].id : '',
  nama: '', tanggal_lahir: '', jenis_kelamin: '',
  nik_balita: '', nik_ibu: '', nama_ibu: '', nomor_hp_ibu: '', nama_ayah: '',
  alamat: '', rt_rw: '',
  berat_lahir_gram: '', panjang_lahir_cm: '',
  prematur: false, usia_gestasi_minggu: '',
})

function submit() {
  form.post(route('balita.store'))
}
</script>
