<template>
  <AppLayout :title="`Edit Pengukuran — ${pengukuran.balita?.nama}`">
    <div class="p-4 space-y-4 pb-20 max-w-lg">
      <div class="flex items-center gap-3">
        <Link :href="route('pengukuran.show', pengukuran.id)" class="text-gray-500">‹</Link>
        <h1 class="text-lg font-bold text-gray-800">Edit Pengukuran</h1>
      </div>

      <!-- Info balita -->
      <div class="card bg-blue-50 border border-blue-200">
        <p class="font-semibold text-blue-800">{{ pengukuran.balita?.nama }}</p>
        <p class="text-xs text-blue-600">{{ pengukuran.balita?.posyandu?.nama }}</p>
        <div class="mt-2 p-2 bg-amber-50 border border-amber-200 rounded text-xs text-amber-700">
          ⚠️ Pastikan data yang diedit sudah benar — perubahan akan menghitung ulang Z-score dan EWS.
        </div>
      </div>

      <!-- Form edit -->
      <div class="card space-y-4">
        <!-- Tanggal ukur -->
        <div>
          <label class="form-label">Tanggal Pengukuran <span class="text-red-500">*</span></label>
          <input type="date" v-model="form.tanggal_ukur"
                 :max="today"
                 class="form-input"
                 :class="{ 'border-red-400': form.errors.tanggal_ukur }" />
          <p v-if="form.errors.tanggal_ukur" class="form-error">{{ form.errors.tanggal_ukur }}</p>
        </div>

        <!-- BB & TB -->
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="form-label">Berat Badan (kg) <span class="text-red-500">*</span></label>
            <input type="number" v-model="form.berat_badan_kg"
                   step="0.1" min="1" max="50"
                   class="form-input"
                   :class="{ 'border-red-400': form.errors.berat_badan_kg }"
                   placeholder="mis. 8.5" />
            <p v-if="form.errors.berat_badan_kg" class="form-error">{{ form.errors.berat_badan_kg }}</p>
          </div>
          <div>
            <label class="form-label">Tinggi Badan (cm) <span class="text-red-500">*</span></label>
            <input type="number" v-model="form.tinggi_badan_cm"
                   step="0.1" min="40" max="130"
                   class="form-input"
                   :class="{ 'border-red-400': form.errors.tinggi_badan_cm }"
                   placeholder="mis. 75.5" />
            <p v-if="form.errors.tinggi_badan_cm" class="form-error">{{ form.errors.tinggi_badan_cm }}</p>
          </div>
        </div>

        <!-- Posisi ukur -->
        <div v-if="form.tinggi_badan_cm">
          <label class="form-label">Posisi saat diukur:</label>
          <div class="flex gap-2 mt-1">
            <button type="button"
                    @click="form.posisi_ukur = 'terlentang'"
                    :class="['flex-1 py-2 rounded-lg text-sm font-medium border-2 transition',
                             form.posisi_ukur === 'terlentang'
                               ? 'bg-blue-600 text-white border-blue-600'
                               : 'bg-white text-gray-600 border-gray-300']">
              🤸 Terlentang (PB)
            </button>
            <button type="button"
                    @click="form.posisi_ukur = 'berdiri'"
                    :class="['flex-1 py-2 rounded-lg text-sm font-medium border-2 transition',
                             form.posisi_ukur === 'berdiri'
                               ? 'bg-blue-600 text-white border-blue-600'
                               : 'bg-white text-gray-600 border-gray-300']">
              🧍 Berdiri (TB)
            </button>
          </div>
          <p v-if="posisiTidakSesuai" class="mt-1.5 text-xs text-amber-600">
            ⚠️ Usia {{ umurBulan }} bln — lazimnya {{ umurBulan <= 24 ? 'terlentang' : 'berdiri' }}. Koreksi 0.7 cm akan diterapkan otomatis.
          </p>
        </div>

        <!-- LLA & LK (opsional) -->
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="form-label">
              LLA (cm) <span class="text-gray-400 font-normal text-xs">(opsional)</span>
            </label>
            <input type="number" v-model="form.lingkar_lengan_atas_cm"
                   step="0.1" min="8" max="25"
                   class="form-input" placeholder="mis. 13.5" />
          </div>
          <div>
            <label class="form-label">
              LK (cm) <span class="text-gray-400 font-normal text-xs">(opsional)</span>
            </label>
            <input type="number" v-model="form.lingkar_kepala_cm"
                   step="0.1" min="25" max="60"
                   class="form-input" placeholder="mis. 45.0" />
          </div>
        </div>

        <!-- Catatan -->
        <div>
          <label class="form-label">Catatan <span class="text-gray-400 font-normal text-xs">(opsional)</span></label>
          <textarea v-model="form.catatan" class="form-input" rows="2"
                    placeholder="Kondisi saat pengukuran, keterangan tambahan..."></textarea>
        </div>

        <!-- Tombol -->
        <div class="flex gap-3 pt-2">
          <Link :href="route('pengukuran.show', pengukuran.id)"
                class="flex-1 py-3 text-sm text-center border-2 border-gray-300 text-gray-600 rounded-xl font-medium hover:bg-gray-50 transition">
            Batal
          </Link>
          <button @click="submit"
                  :disabled="form.processing"
                  class="flex-1 py-3 text-sm bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition disabled:opacity-50">
            {{ form.processing ? 'Menyimpan...' : '💾 Simpan Perubahan' }}
          </button>
        </div>
      </div>

      <!-- Data sebelumnya -->
      <div class="card bg-gray-50 space-y-2">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Data Sebelumnya</p>
        <div class="grid grid-cols-2 gap-2 text-sm">
          <div>
            <span class="text-gray-400">BB lama:</span>
            <span class="font-medium ml-1">{{ pengukuran.berat_badan_kg }} kg</span>
          </div>
          <div>
            <span class="text-gray-400">TB lama:</span>
            <span class="font-medium ml-1">{{ pengukuran.tinggi_badan_cm }} cm</span>
          </div>
          <div>
            <span class="text-gray-400">Z BB/U:</span>
            <span class="font-medium ml-1">{{ pengukuran.z_score_bb_u ?? '—' }}</span>
          </div>
          <div>
            <span class="text-gray-400">Z TB/U:</span>
            <span class="font-medium ml-1">{{ pengukuran.z_score_tb_u ?? '—' }}</span>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/UI/AppLayout.vue'

const props = defineProps({ pengukuran: Object })

const today = new Date().toISOString().split('T')[0]

const form = useForm({
  tanggal_ukur:           props.pengukuran.tanggal_ukur,
  berat_badan_kg:         props.pengukuran.berat_badan_kg,
  tinggi_badan_cm:        props.pengukuran.tinggi_badan_cm,
  posisi_ukur:            props.pengukuran.posisi_ukur ?? 'terlentang',
  lingkar_lengan_atas_cm: props.pengukuran.lingkar_lengan_atas_cm ?? '',
  lingkar_kepala_cm:      props.pengukuran.lingkar_kepala_cm ?? '',
  catatan:                props.pengukuran.catatan ?? '',
})

const umurBulan = computed(() => {
  const lahir = props.pengukuran.balita?.tanggal_lahir
  if (!lahir || !form.tanggal_ukur) return null
  const d1 = new Date(lahir), d2 = new Date(form.tanggal_ukur)
  return Math.floor((d2 - d1) / (1000 * 60 * 60 * 24 * 30.44))
})

const posisiTidakSesuai = computed(() => {
  if (!umurBulan.value || !form.posisi_ukur) return false
  if (umurBulan.value <= 24 && form.posisi_ukur === 'berdiri') return true
  if (umurBulan.value > 24  && form.posisi_ukur === 'terlentang') return true
  return false
})

function submit() {
  form.patch(route('pengukuran.update', props.pengukuran.id))
}
</script>
