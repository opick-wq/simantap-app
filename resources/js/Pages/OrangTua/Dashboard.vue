<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-blue-600 text-white px-4 py-4 sticky top-0 z-50 shadow">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="font-bold text-lg">SI-MANTAP</h1>
          <p class="text-blue-200 text-xs">Portal Orang Tua</p>
        </div>
        <Link href="/logout" method="post" as="button"
              class="text-blue-200 text-sm hover:text-white">
          Keluar
        </Link>
      </div>
    </header>

    <div class="px-4 py-4 space-y-4 pb-8">
      <!-- Sambutan -->
      <div class="bg-white rounded-xl p-4 shadow-sm">
        <p class="text-gray-500 text-sm">Selamat datang,</p>
        <p class="font-bold text-gray-800 text-lg">{{ $page.props.auth.user.nama }}</p>
      </div>

      <!-- Daftar anak -->
      <div v-if="balita.length > 0" class="space-y-3">
        <h2 class="font-semibold text-gray-700 text-sm">Data Anak Anda</h2>

        <div v-for="b in balita" :key="b.id"
             class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
          <div class="flex items-start justify-between">
            <div>
              <p class="font-bold text-gray-800">{{ b.nama }}</p>
              <p class="text-xs text-gray-500 mt-0.5">{{ b.umur_lengkap }} · {{ b.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
            </div>
          </div>

          <!-- Pengukuran terakhir -->
          <div v-if="b.pengukuran_terakhir" class="mt-3 grid grid-cols-2 gap-2">
            <div class="bg-gray-50 rounded-lg p-2 text-center">
              <p class="text-xl font-bold text-blue-600">{{ b.pengukuran_terakhir.berat_badan_kg }}</p>
              <p class="text-xs text-gray-500">kg</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-2 text-center">
              <p class="text-xl font-bold text-green-600">{{ b.pengukuran_terakhir.tinggi_badan_cm }}</p>
              <p class="text-xs text-gray-500">cm</p>
            </div>
          </div>
          <p v-if="b.pengukuran_terakhir" class="text-xs text-gray-400 mt-2 text-center">
            Diukur: {{ b.pengukuran_terakhir.tanggal_ukur }}
          </p>
          <p v-else class="text-xs text-gray-400 mt-2 text-center italic">
            Belum ada data pengukuran
          </p>

          <Link :href="`/ortu/anak/${b.id}`"
                class="block mt-3 text-center text-sm text-blue-600 font-medium bg-blue-50 rounded-lg py-2">
            Lihat Grafik Pertumbuhan →
          </Link>
        </div>
      </div>

      <!-- Tidak ada anak terdaftar -->
      <div v-else class="bg-white rounded-xl p-8 shadow-sm text-center">
        <p class="text-4xl mb-3">👶</p>
        <p class="text-gray-600 font-medium">Belum ada data anak</p>
        <p class="text-gray-400 text-sm mt-1">
          Hubungi kader Posyandu untuk menghubungkan akun Anda dengan data anak.
        </p>
      </div>

      <!-- Banner notifikasi jika sesi dalam 7 hari -->
      <div v-if="sesiDekat" class="bg-orange-500 rounded-xl p-4 shadow-sm text-white">
        <div class="flex items-start gap-3">
          <span class="text-2xl">🔔</span>
          <div class="flex-1">
            <p class="font-bold text-sm">Pengingat Posyandu!</p>
            <p class="text-orange-100 text-sm mt-0.5">
              <span v-if="sesiDekat.sisa_hari === 0">Hari ini</span>
              <span v-else-if="sesiDekat.sisa_hari === 1">Besok</span>
              <span v-else>{{ sesiDekat.sisa_hari }} hari lagi</span>
              — <strong>{{ sesiDekat.posyandu_nama }}</strong>
            </p>
            <p class="text-orange-200 text-xs mt-1">{{ sesiDekat.hari }}, {{ sesiDekat.tanggal }}</p>
            <p v-if="sesiDekat.tema" class="text-orange-200 text-xs">{{ sesiDekat.tema }}</p>
          </div>
        </div>
      </div>

      <!-- Sesi Mendatang -->
      <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <div class="flex items-center gap-2 mb-3">
          <span class="text-lg">📅</span>
          <p class="font-semibold text-gray-700">Jadwal Sesi Posyandu</p>
        </div>

        <div v-if="sesiMendatang.length > 0" class="space-y-2">
          <div v-for="s in sesiMendatang" :key="s.id"
               :class="['flex items-start gap-3 rounded-lg p-3',
                        s.sisa_hari <= 7 ? 'bg-orange-50 border border-orange-200' : 'bg-blue-50']">
            <div :class="['text-center rounded-lg px-2 py-1 min-w-[52px]',
                          s.sisa_hari <= 7 ? 'bg-orange-500 text-white' : 'bg-blue-600 text-white']">
              <p class="text-xs font-medium">{{ s.hari.substring(0,3) }}</p>
              <p class="text-base font-bold leading-none">{{ s.tanggal.split(' ')[0] }}</p>
            </div>
            <div class="flex-1">
              <div class="flex items-center gap-2">
                <p class="text-sm font-semibold text-gray-800">{{ s.posyandu_nama }}</p>
                <span v-if="s.sisa_hari <= 7"
                      class="text-xs px-1.5 py-0.5 bg-orange-100 text-orange-700 rounded-full font-medium">
                  {{ s.sisa_hari === 0 ? 'Hari ini' : s.sisa_hari === 1 ? 'Besok' : s.sisa_hari + ' hari lagi' }}
                </span>
              </div>
              <p v-if="s.tema" class="text-xs text-gray-500 mt-0.5">{{ s.tema }}</p>
              <p class="text-xs text-gray-400">{{ s.hari }}, {{ s.tanggal }}</p>
            </div>
          </div>
        </div>

        <!-- Jadwal rutin sebagai fallback -->
        <div v-else-if="posyandus.length > 0" class="space-y-2">
          <p class="text-xs text-gray-400 mb-2">Jadwal rutin posyandu:</p>
          <div v-for="p in posyandus" :key="p.id" class="bg-gray-50 rounded-lg p-3">
            <p class="text-sm font-semibold text-gray-800">{{ p.nama }}</p>
            <p v-if="p.jadwal_hari" class="text-xs text-gray-600 mt-0.5">🗓 {{ p.jadwal_hari }}</p>
            <p v-if="p.jadwal_jam" class="text-xs text-gray-500">⏰ {{ p.jadwal_jam }}</p>
            <p v-if="p.alamat" class="text-xs text-gray-400 mt-0.5">📍 {{ p.alamat }}</p>
          </div>
          <p class="text-xs text-gray-400 text-center mt-2 italic">
            Belum ada sesi terjadwal. Pantau secara rutin.
          </p>
        </div>

        <div v-else class="text-center py-4 text-gray-400 text-sm">
          Belum ada jadwal sesi mendatang
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const props = defineProps({
  balita:        Array,
  sesiMendatang: Array,
  posyandus:     Array,
})

// Sesi dalam 7 hari ke depan untuk banner notifikasi
const sesiDekat = computed(() =>
  props.sesiMendatang.find(s => s.sisa_hari <= 7) ?? null
)

</script>
