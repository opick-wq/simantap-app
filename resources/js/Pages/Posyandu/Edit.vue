<template>
  <AppLayout title="Edit Posyandu">
    <div class="p-4 max-w-lg">
      <div class="flex items-center gap-3 mb-4">
        <Link :href="route('posyandu.index')" class="text-gray-500 text-sm">← Kembali</Link>
        <h1 class="text-base font-bold text-gray-800">Edit Posyandu</h1>
      </div>

      <form @submit.prevent="submit" class="card space-y-4">
        <div>
          <label class="form-label">Nama Posyandu *</label>
          <input v-model="form.nama" type="text" class="form-input" required />
          <p v-if="form.errors.nama" class="form-error">{{ form.errors.nama }}</p>
        </div>

        <div>
          <label class="form-label">Wilayah / Kelurahan</label>
          <select v-model="form.wilayah_id" class="form-input">
            <option :value="null">— Pilih wilayah —</option>
            <option v-for="w in wilayah" :key="w.id" :value="w.id">{{ w.nama }} — Kec. {{ w.kecamatan }}</option>
          </select>
        </div>

        <div>
          <label class="form-label">Alamat *</label>
          <textarea v-model="form.alamat" class="form-input" rows="2" required></textarea>
          <p v-if="form.errors.alamat" class="form-error">{{ form.errors.alamat }}</p>
        </div>

        <div>
          <label class="form-label">Jadwal Posyandu</label>
          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="text-xs text-gray-500 mb-1 block">Minggu ke-</label>
              <select v-model="form.jadwal_minggu" class="form-input">
                <option :value="null">—</option>
                <option v-for="m in mingguOptions" :key="m" :value="m">{{ m }}</option>
              </select>
            </div>
            <div>
              <label class="text-xs text-gray-500 mb-1 block">Hari</label>
              <select v-model="form.jadwal_hari" class="form-input">
                <option :value="null">— Pilih —</option>
                <option v-for="h in hariOptions" :key="h" :value="h">{{ h }}</option>
              </select>
            </div>
            <div>
              <label class="text-xs text-gray-500 mb-1 block">Pukul</label>
              <input v-model="form.jadwal_jam" type="time" class="form-input" />
            </div>
          </div>
          <p class="text-xs text-gray-400 mt-1">
            Contoh: Minggu ke-2, Selasa, pukul 08.00
          </p>
        </div>

        <div class="flex items-center gap-2 pt-1">
          <input v-model="form.aktif" type="checkbox" id="aktif"
                 class="w-4 h-4 text-blue-600 rounded" />
          <label for="aktif" class="text-sm text-gray-600">Posyandu aktif</label>
        </div>

        <div class="flex gap-3 pt-2">
          <Link :href="route('posyandu.index')" class="btn-secondary flex-1 text-center">Batal</Link>
          <button type="submit" :disabled="form.processing" class="btn-primary flex-1">
            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/UI/AppLayout.vue'

const props = defineProps({ posyandu: Object, wilayah: Array, hariOptions: Array, mingguOptions: Array })

const form = useForm({
  nama:          props.posyandu.nama,
  wilayah_id:    props.posyandu.wilayah_id,
  alamat:        props.posyandu.alamat,
  jadwal_minggu: props.posyandu.jadwal_minggu,
  jadwal_hari:   props.posyandu.jadwal_hari,
  jadwal_jam:    props.posyandu.jadwal_jam,
  aktif:         props.posyandu.aktif,
})

function submit() {
  form.put(route('posyandu.update', props.posyandu.id))
}
</script>
