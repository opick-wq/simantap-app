<template>
  <AppLayout title="Sesi Posyandu">
    <div class="p-4 space-y-4 pb-20">
      <div class="flex items-center justify-between">
        <h1 class="text-lg font-bold text-gray-800">📅 Sesi Posyandu</h1>
        <button v-if="bisaBuatJadwal" @click="bukaFormBaru" class="btn-primary px-3 py-1.5 text-sm">+ Buat Jadwal</button>
      </div>

      <!-- Flash -->
      <div v-if="$page.props.flash?.success"
           class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">
        {{ $page.props.flash.success }}
      </div>

      <!-- Form buat / edit sesi -->
      <div v-if="showForm" class="card space-y-3 border-2 border-blue-200">
        <p class="font-semibold text-gray-700">{{ editTarget ? 'Edit Jadwal' : 'Buat Sesi Baru' }}</p>

        <div v-if="!editTarget && posyandu.length > 1">
          <label class="form-label">Posyandu <span class="text-red-500">*</span></label>
          <select v-model="form.posyandu_id" class="form-input" required>
            <option value="" disabled>Pilih posyandu...</option>
            <option v-for="p in posyandu" :key="p.id" :value="p.id">{{ p.nama }}</option>
          </select>
          <p v-if="form.errors.posyandu_id" class="form-error">{{ form.errors.posyandu_id }}</p>
        </div>

        <div>
          <label class="form-label">Tanggal <span class="text-red-500">*</span></label>
          <input v-model="form.tanggal" type="date" class="form-input" required />
          <p v-if="form.errors.tanggal" class="form-error">{{ form.errors.tanggal }}</p>
        </div>
        <div>
          <label class="form-label">Tema / Keterangan</label>
          <input v-model="form.tema" type="text" class="form-input"
                 placeholder="Cth: Posyandu Rutin Juli 2026, Imunisasi DPT..." />
        </div>
        <div class="flex gap-2">
          <button @click="tutupForm" class="flex-1 py-2 text-sm border rounded text-gray-600">Batal</button>
          <button @click="submit" :disabled="form.processing" class="flex-1 btn-primary py-2 text-sm">
            {{ form.processing ? 'Menyimpan...' : (editTarget ? 'Simpan Perubahan' : 'Simpan') }}
          </button>
        </div>
      </div>

      <!-- Konfirmasi hapus -->
      <div v-if="hapusTarget" class="card border-2 border-red-200 space-y-3">
        <p class="font-semibold text-red-700">Hapus Jadwal?</p>
        <p class="text-sm text-gray-600">
          Jadwal <strong>{{ hapusTarget.tanggal }}</strong> — {{ hapusTarget.tema || 'Posyandu Rutin' }}
          akan dihapus permanen.
        </p>
        <div class="flex gap-2">
          <button @click="hapusTarget = null" class="flex-1 py-2 text-sm border rounded text-gray-600">Batal</button>
          <button @click="konfirmasiHapus" class="flex-1 py-2 text-sm bg-red-600 text-white rounded font-semibold">
            Ya, Hapus
          </button>
        </div>
      </div>

      <!-- List sesi -->
      <div v-if="sesi.data.length === 0" class="text-center text-gray-400 py-12">
        <p class="text-4xl mb-2">📅</p>
        <p>Belum ada sesi posyandu</p>
      </div>

      <div v-for="s in sesi.data" :key="s.id"
           :class="['card space-y-1 border-l-4',
                    s.status === 'BERLANGSUNG' ? 'border-green-500' :
                    s.status === 'TERJADWAL'   ? 'border-blue-400' : 'border-gray-200']">
        <div class="flex items-start justify-between gap-2">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
              <p class="font-semibold text-gray-800">{{ s.tanggal }}</p>
              <span v-if="isHariIni(s.tanggal_raw)"
                    class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">
                Hari ini
              </span>
            </div>
            <p class="text-sm text-gray-600 mt-0.5">{{ s.tema || 'Posyandu Rutin' }}</p>
            <p class="text-xs text-gray-400">{{ s.posyandu_nama }}</p>
          </div>
          <div class="flex items-center gap-2 shrink-0">
            <span :class="statusClass(s.status)" class="text-xs px-2 py-0.5 rounded-full font-medium">
              {{ statusLabel(s.status) }}
            </span>
            <!-- Tombol edit & hapus hanya untuk nakes/admin, dan sesi belum selesai -->
            <template v-if="bisaBuatJadwal && s.status !== 'SELESAI'">
              <button @click="bukaFormEdit(s)"
                      class="text-xs text-blue-600 hover:underline px-1">Edit</button>
              <button @click="hapusTarget = s"
                      class="text-xs text-red-500 hover:underline px-1">Hapus</button>
            </template>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/UI/AppLayout.vue'

const props = defineProps({ sesi: Object, posyandu: Array, bisaBuatJadwal: Boolean })

const showForm   = ref(false)
const editTarget = ref(null)
const hapusTarget = ref(null)
const today      = new Date().toISOString().split('T')[0]
const defaultPosyandu = props.posyandu.length === 1 ? props.posyandu[0].id : null

const form = useForm({
  posyandu_id: defaultPosyandu,
  tanggal: today,
  tema: '',
})

function bukaFormBaru() {
  editTarget.value = null
  form.reset()
  form.posyandu_id = defaultPosyandu
  form.tanggal = today
  showForm.value = true
}

function bukaFormEdit(s) {
  editTarget.value = s
  form.tanggal = s.tanggal_raw
  form.tema    = s.tema ?? ''
  showForm.value = true
}

function tutupForm() {
  showForm.value = false
  editTarget.value = null
}

function submit() {
  if (editTarget.value) {
    form.put(route('sesi.update', editTarget.value.id), {
      onSuccess: () => tutupForm(),
    })
  } else {
    form.post(route('sesi.store'), {
      onSuccess: () => { tutupForm(); form.tanggal = today },
    })
  }
}

function konfirmasiHapus() {
  router.delete(route('sesi.destroy', hapusTarget.value.id), {
    onSuccess: () => { hapusTarget.value = null },
  })
}

function isHariIni(tglRaw) { return tglRaw === today }

function statusLabel(s) {
  return { TERJADWAL: 'Terjadwal', BERLANGSUNG: 'Berlangsung', SELESAI: 'Selesai' }[s] ?? s
}

function statusClass(s) {
  return {
    TERJADWAL:   'bg-blue-100 text-blue-700',
    BERLANGSUNG: 'bg-green-100 text-green-700',
    SELESAI:     'bg-gray-100 text-gray-500',
  }[s] ?? 'bg-gray-100'
}
</script>
