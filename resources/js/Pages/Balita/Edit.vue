<template>
  <AppLayout :title="`Edit — ${balita.nama}`">
    <div class="p-4 pb-20">
      <div class="flex items-center gap-3 mb-5">
        <Link :href="route('balita.show', balita.id)" class="text-gray-500">‹</Link>
        <h1 class="text-lg font-bold text-gray-800">Edit Data Balita</h1>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div class="card space-y-3">
          <div>
            <label class="form-label">NIK Balita <span class="text-red-500">*</span></label>
            <input v-model="form.nik_balita" type="text" class="form-input"
                   placeholder="16 digit NIK" maxlength="16" minlength="16"
                   pattern="\d{16}" required inputmode="numeric" />
            <p v-if="form.errors.nik_balita" class="form-error">{{ form.errors.nik_balita }}</p>
          </div>
          <div>
            <label class="form-label">Nama Lengkap *</label>
            <input v-model="form.nama" type="text" class="form-input" required />
            <p v-if="form.errors.nama" class="form-error">{{ form.errors.nama }}</p>
          </div>
          <div>
            <label class="form-label">Tanggal Lahir *</label>
            <input v-model="form.tanggal_lahir" type="date" class="form-input" required />
            <p v-if="form.errors.tanggal_lahir" class="form-error">{{ form.errors.tanggal_lahir }}</p>
          </div>
          <div>
            <label class="form-label">Nama Ibu *</label>
            <input v-model="form.nama_ibu" type="text" class="form-input" required />
          </div>
          <div>
            <label class="form-label">No. HP Ibu</label>
            <input v-model="form.nomor_hp_ibu" type="tel" class="form-input" />
          </div>
          <div>
            <label class="form-label">Alamat *</label>
            <textarea v-model="form.alamat" class="form-input" rows="2" required></textarea>
          </div>
        </div>

        <button type="submit" :disabled="form.processing" class="btn-primary w-full py-3">
          {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
        </button>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/UI/AppLayout.vue'

const props = defineProps({ balita: Object, posyandu: Array })

const form = useForm({
  nik_balita:    props.balita.nik_balita ?? '',
  nama:          props.balita.nama,
  tanggal_lahir: props.balita.tanggal_lahir?.substring(0, 10) ?? '',
  nama_ibu:      props.balita.nama_ibu,
  nomor_hp_ibu:  props.balita.nomor_hp_ibu ?? '',
  alamat:        props.balita.alamat,
})

function submit() {
  form.put(route('balita.update', props.balita.id))
}
</script>
