<template>
  <AppLayout title="Kelola Kader">
    <div class="p-4 space-y-4 pb-20">

      <div class="flex items-center justify-between">
        <h1 class="text-lg font-bold text-gray-800">Kelola Kader</h1>
        <button @click="showForm = true"
                class="btn-primary text-sm px-3 py-2">
          + Tambah Kader
        </button>
      </div>

      <!-- Flash success -->
      <div v-if="$page.props.flash?.success"
           class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-2">
        {{ $page.props.flash.success }}
      </div>

      <!-- Form tambah kader -->
      <div v-if="showForm" class="card space-y-3">
        <p class="font-semibold text-gray-700">Buat Akun Kader Baru</p>

        <div>
          <label class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
          <input v-model="form.nama" type="text" class="form-input" placeholder="Nama kader" />
          <p v-if="form.errors.nama" class="form-error">{{ form.errors.nama }}</p>
        </div>

        <div>
          <label class="form-label">Nomor HP <span class="text-red-500">*</span></label>
          <input v-model="form.nomor_hp" type="tel" class="form-input" placeholder="08xx-xxxx-xxxx" />
          <p v-if="form.errors.nomor_hp" class="form-error">{{ form.errors.nomor_hp }}</p>
        </div>

        <div>
          <label class="form-label">Email <span class="text-gray-400 text-xs">(opsional)</span></label>
          <input v-model="form.email" type="email" class="form-input" placeholder="email@contoh.com" />
          <p v-if="form.errors.email" class="form-error">{{ form.errors.email }}</p>
        </div>

        <div>
          <label class="form-label">Posyandu <span class="text-red-500">*</span></label>
          <select v-model="form.posyandu_id" class="form-input">
            <option value="">-- Pilih Posyandu --</option>
            <option v-for="p in posyandu" :key="p.id" :value="p.id">{{ p.nama }}</option>
          </select>
          <p v-if="form.errors.posyandu_id" class="form-error">{{ form.errors.posyandu_id }}</p>
        </div>

        <div>
          <label class="form-label">Password <span class="text-red-500">*</span></label>
          <input v-model="form.password" type="password" class="form-input" placeholder="Min. 6 karakter" />
          <p v-if="form.errors.password" class="form-error">{{ form.errors.password }}</p>
        </div>

        <div class="flex gap-2">
          <button @click="submitForm" :disabled="form.processing"
                  class="flex-1 py-2.5 bg-blue-600 text-white text-sm rounded-lg font-semibold disabled:opacity-50">
            Simpan
          </button>
          <button @click="tutupForm"
                  class="flex-1 py-2.5 border text-sm rounded-lg text-gray-600">
            Batal
          </button>
        </div>
      </div>

      <!-- Form edit kader -->
      <div v-if="editTarget" class="card space-y-3 border-2 border-blue-200">
        <p class="font-semibold text-gray-700">Edit Kader: {{ editTarget.nama }}</p>

        <div>
          <label class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
          <input v-model="editForm.nama" type="text" class="form-input" />
          <p v-if="editForm.errors.nama" class="form-error">{{ editForm.errors.nama }}</p>
        </div>

        <div>
          <label class="form-label">Nomor HP <span class="text-red-500">*</span></label>
          <input v-model="editForm.nomor_hp" type="tel" class="form-input" />
          <p v-if="editForm.errors.nomor_hp" class="form-error">{{ editForm.errors.nomor_hp }}</p>
        </div>

        <div>
          <label class="form-label">Email <span class="text-gray-400 text-xs">(opsional)</span></label>
          <input v-model="editForm.email" type="email" class="form-input" />
        </div>

        <div>
          <label class="form-label">Posyandu <span class="text-red-500">*</span></label>
          <select v-model="editForm.posyandu_id" class="form-input">
            <option v-for="p in posyandu" :key="p.id" :value="p.id">{{ p.nama }}</option>
          </select>
        </div>

        <div>
          <label class="form-label">Password Baru <span class="text-gray-400 text-xs">(kosongkan jika tidak diubah)</span></label>
          <input v-model="editForm.password" type="password" class="form-input" placeholder="Min. 6 karakter" />
        </div>

        <div class="flex gap-2">
          <button @click="submitEdit" :disabled="editForm.processing"
                  class="flex-1 py-2.5 bg-blue-600 text-white text-sm rounded-lg font-semibold disabled:opacity-50">
            Simpan Perubahan
          </button>
          <button @click="editTarget = null"
                  class="flex-1 py-2.5 border text-sm rounded-lg text-gray-600">
            Batal
          </button>
        </div>
      </div>

      <!-- Daftar kader -->
      <div v-if="kader.length === 0" class="card text-center py-8 text-gray-400 text-sm">
        Belum ada kader terdaftar. Tambahkan kader baru di atas.
      </div>

      <div v-for="k in kader" :key="k.id" class="card">
        <div class="flex items-start justify-between gap-2">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center font-bold text-blue-600 text-sm shrink-0">
              {{ k.nama.charAt(0).toUpperCase() }}
            </div>
            <div>
              <p class="font-semibold text-gray-800 text-sm">{{ k.nama }}</p>
              <p class="text-xs text-gray-500">{{ k.nomor_hp }}</p>
              <p v-if="k.email" class="text-xs text-gray-400">{{ k.email }}</p>
              <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium mt-1 inline-block">
                {{ k.posyandu }}
              </span>
            </div>
          </div>
          <div class="flex gap-2 shrink-0">
            <button @click="bukaEdit(k)"
                    class="text-xs text-blue-600 border border-blue-200 rounded-lg px-3 py-1.5 hover:bg-blue-50">
              Edit
            </button>
            <button @click="hapus(k)"
                    class="text-xs text-red-500 border border-red-200 rounded-lg px-3 py-1.5 hover:bg-red-50">
              Hapus
            </button>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/UI/AppLayout.vue'

const props = defineProps({
  kader:    Array,
  posyandu: Array,
})

// ── Tambah ────────────────────────────────────
const showForm = ref(false)

const form = useForm({
  nama:        '',
  nomor_hp:    '',
  email:       '',
  password:    '',
  posyandu_id: props.posyandu.length === 1 ? props.posyandu[0].id : '',
})

function submitForm() {
  form.post(route('kader.store'), {
    onSuccess: () => {
      tutupForm()
    },
  })
}

function tutupForm() {
  showForm.value = false
  form.reset()
}

// ── Edit ──────────────────────────────────────
const editTarget = ref(null)
const editForm   = useForm({ nama: '', nomor_hp: '', email: '', posyandu_id: '', password: '' })

function bukaEdit(k) {
  editTarget.value = k
  editForm.nama        = k.nama
  editForm.nomor_hp    = k.nomor_hp
  editForm.email       = k.email ?? ''
  editForm.posyandu_id = k.posyandu_id
  editForm.password    = ''
  showForm.value = false
}

function submitEdit() {
  editForm.patch(route('kader.update', editTarget.value.id), {
    onSuccess: () => { editTarget.value = null },
  })
}

// ── Hapus ─────────────────────────────────────
function hapus(k) {
  if (!confirm(`Hapus akun kader ${k.nama}? Tindakan ini tidak bisa dibatalkan.`)) return
  useForm({}).delete(route('kader.destroy', k.id))
}
</script>
