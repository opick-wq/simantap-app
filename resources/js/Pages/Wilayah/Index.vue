<template>
  <AppLayout title="Manajemen Wilayah">
    <div class="p-5 space-y-5 max-w-screen-xl mx-auto">

      <!-- Banner -->
      <div class="rounded-2xl p-5 text-white shadow-md bg-gradient-to-r from-purple-700 to-purple-900">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <h1 class="text-xl font-bold">Manajemen Wilayah</h1>
            <p class="text-purple-200 text-sm mt-0.5">Kelurahan/Desa · Kecamatan · Kabupaten/Kota</p>
          </div>
          <button @click="openTambah"
                  class="shrink-0 bg-white text-purple-700 font-semibold text-sm px-4 py-2 rounded-lg hover:bg-purple-50 transition">
            + Tambah Wilayah
          </button>
        </div>
      </div>

      <!-- Filter -->
      <div class="flex flex-col sm:flex-row gap-3">
        <input v-model="search" @input="doFilter" type="text" placeholder="Cari nama, kecamatan, kabupaten..."
               class="flex-1 border border-slate-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400" />
        <select v-model="filterKecamatan" @change="doFilter"
                class="border border-slate-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400">
          <option value="">Semua Kecamatan</option>
          <option v-for="k in kecamatans" :key="k" :value="k">{{ k }}</option>
        </select>
      </div>

      <!-- Flash -->
      <div v-if="$page.props.flash?.success"
           class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">
        {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.errors?.delete"
           class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
        {{ $page.props.errors.delete }}
      </div>

      <!-- Tabel -->
      <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
              <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Kelurahan/Desa</th>
              <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Kecamatan</th>
              <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Kabupaten/Kota</th>
              <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Provinsi</th>
              <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Kode BPS</th>
              <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Posyandu</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="wilayah.data.length === 0">
              <td colspan="7" class="px-4 py-10 text-center text-slate-400">Tidak ada data wilayah.</td>
            </tr>
            <tr v-for="w in wilayah.data" :key="w.id" class="hover:bg-slate-50">
              <td class="px-4 py-3 font-medium text-slate-800">{{ w.nama }}</td>
              <td class="px-4 py-3 text-slate-600">{{ w.kecamatan }}</td>
              <td class="px-4 py-3 text-slate-600">{{ w.kabupaten }}</td>
              <td class="px-4 py-3 text-slate-500">{{ w.provinsi }}</td>
              <td class="px-4 py-3 text-slate-400 font-mono text-xs">{{ w.kode_bps ?? '—' }}</td>
              <td class="px-4 py-3 text-center">
                <span class="bg-blue-100 text-blue-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                  {{ w.posyandu_count }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2 justify-end">
                  <button @click="openEdit(w)"
                          class="text-xs text-blue-600 hover:underline font-medium">Edit</button>
                  <button @click="confirmDelete(w)"
                          class="text-xs text-red-500 hover:underline font-medium">Hapus</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="wilayah.last_page > 1"
             class="px-4 py-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
          <span>Menampilkan {{ wilayah.from }}–{{ wilayah.to }} dari {{ wilayah.total }} wilayah</span>
          <div class="flex gap-1">
            <Link v-for="link in wilayah.links" :key="link.label"
                  :href="link.url ?? '#'"
                  :class="['px-3 py-1 rounded-lg', link.active ? 'bg-purple-600 text-white font-semibold' : 'hover:bg-slate-100', !link.url ? 'opacity-40 pointer-events-none' : '']"
                  v-html="link.label" preserve-scroll />
          </div>
        </div>
      </div>
    </div>

    <!-- ══ MODAL TAMBAH / EDIT ══ -->
    <div v-if="modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">
          {{ editTarget ? 'Edit Wilayah' : 'Tambah Wilayah Baru' }}
        </h2>

        <form @submit.prevent="submit" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
              <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Kelurahan/Desa <span class="text-red-500">*</span></label>
              <input v-model="form.nama" type="text" required
                     class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400"
                     placeholder="Kelurahan Sukamaju" />
              <p v-if="errors.nama" class="text-red-500 text-xs mt-1">{{ errors.nama }}</p>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1">Kecamatan <span class="text-red-500">*</span></label>
              <input v-model="form.kecamatan" type="text" required list="kecamatan-list"
                     class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400"
                     placeholder="Kec. Cimahi Utara" />
              <datalist id="kecamatan-list">
                <option v-for="k in kecamatans" :key="k" :value="k" />
              </datalist>
              <p v-if="errors.kecamatan" class="text-red-500 text-xs mt-1">{{ errors.kecamatan }}</p>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1">Kabupaten/Kota <span class="text-red-500">*</span></label>
              <input v-model="form.kabupaten" type="text" required
                     class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400"
                     placeholder="Kota Cimahi" />
              <p v-if="errors.kabupaten" class="text-red-500 text-xs mt-1">{{ errors.kabupaten }}</p>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1">Provinsi <span class="text-red-500">*</span></label>
              <input v-model="form.provinsi" type="text" required
                     class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400"
                     placeholder="Jawa Barat" />
              <p v-if="errors.provinsi" class="text-red-500 text-xs mt-1">{{ errors.provinsi }}</p>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1">Kode BPS</label>
              <input v-model="form.kode_bps" type="text" maxlength="20"
                     class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-purple-400"
                     placeholder="3277041001" />
              <p class="text-xs text-slate-400 mt-1">Opsional · untuk integrasi data dinas</p>
              <p v-if="errors.kode_bps" class="text-red-500 text-xs mt-1">{{ errors.kode_bps }}</p>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <button type="button" @click="modal = false"
                    class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg transition">
              Batal
            </button>
            <button type="submit" :disabled="submitting"
                    class="px-5 py-2 text-sm font-semibold bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-60 transition">
              {{ submitting ? 'Menyimpan...' : (editTarget ? 'Simpan Perubahan' : 'Tambah Wilayah') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ══ MODAL KONFIRMASI HAPUS ══ -->
    <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-2">Hapus Wilayah?</h2>
        <p class="text-sm text-slate-500 mb-5">
          Wilayah <strong>{{ deleteTarget.nama }}</strong> ({{ deleteTarget.kecamatan }}) akan dihapus secara permanen.
          Aksi ini tidak bisa dibatalkan.
        </p>
        <div class="flex justify-end gap-3">
          <button @click="deleteTarget = null"
                  class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg transition">
            Batal
          </button>
          <button @click="doDelete"
                  class="px-5 py-2 text-sm font-semibold bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
            Hapus
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/UI/AppLayout.vue'

const props = defineProps({
  wilayah:    Object,
  kecamatans: Array,
  filters:    Object,
})

const search          = ref(props.filters.search ?? '')
const filterKecamatan = ref(props.filters.kecamatan ?? '')
const modal           = ref(false)
const editTarget      = ref(null)
const deleteTarget    = ref(null)
const submitting      = ref(false)
const errors          = ref({})

const form = reactive({
  nama:      '',
  kecamatan: '',
  kabupaten: '',
  provinsi:  'Jawa Barat',
  kode_bps:  '',
})

let filterTimer
function doFilter() {
  clearTimeout(filterTimer)
  filterTimer = setTimeout(() => {
    router.get('/wilayah', { search: search.value, kecamatan: filterKecamatan.value }, { preserveState: true, replace: true })
  }, 400)
}

function openTambah() {
  editTarget.value = null
  Object.assign(form, { nama: '', kecamatan: '', kabupaten: '', provinsi: 'Jawa Barat', kode_bps: '' })
  errors.value = {}
  modal.value  = true
}

function openEdit(w) {
  editTarget.value = w
  Object.assign(form, { nama: w.nama, kecamatan: w.kecamatan, kabupaten: w.kabupaten, provinsi: w.provinsi, kode_bps: w.kode_bps ?? '' })
  errors.value = {}
  modal.value  = true
}

function confirmDelete(w) {
  deleteTarget.value = w
}

function submit() {
  submitting.value = true
  errors.value     = {}

  const url    = editTarget.value ? `/wilayah/${editTarget.value.id}` : '/wilayah'
  const method = editTarget.value ? 'put' : 'post'

  router[method](url, { ...form }, {
    onSuccess: () => { modal.value = false; submitting.value = false },
    onError:   (e) => { errors.value = e; submitting.value = false },
  })
}

function doDelete() {
  router.delete(`/wilayah/${deleteTarget.value.id}`, {
    onSuccess: () => { deleteTarget.value = null },
  })
}
</script>
