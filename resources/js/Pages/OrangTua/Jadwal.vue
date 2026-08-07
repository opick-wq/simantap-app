<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-blue-600 text-white px-4 py-4 sticky top-0 z-50 shadow">
      <div class="flex items-center gap-3">
        <Link href="/ortu/dashboard" class="text-blue-200 text-xl">‹</Link>
        <h1 class="font-bold">Jadwal Posyandu</h1>
      </div>
    </header>

    <div class="px-4 py-4">
      <div v-if="jadwal" class="bg-white rounded-xl p-5 shadow-sm space-y-3">
        <div class="flex items-center gap-3">
          <span class="text-3xl">🏥</span>
          <div>
            <p class="font-bold text-gray-800">{{ jadwal.posyandu_nama }}</p>
            <p class="text-gray-500 text-sm">{{ jadwal.alamat }}</p>
          </div>
        </div>
        <hr />
        <div class="grid grid-cols-2 gap-3 text-sm">
          <div>
            <p class="text-gray-400 text-xs">Jadwal Rutin</p>
            <p class="font-medium text-gray-800">{{ jadwal.jadwal_hari ?? '-' }}</p>
          </div>
          <div>
            <p class="text-gray-400 text-xs">Pukul</p>
            <p class="font-medium text-gray-800">
              {{ jadwal.jadwal_jam ? jadwal.jadwal_jam.substring(0,5) + ' WIB' : '-' }}
            </p>
          </div>
        </div>
      </div>

      <div v-else class="bg-white rounded-xl p-8 shadow-sm text-center text-gray-400">
        <p class="text-4xl mb-3">📅</p>
        <p>Informasi jadwal belum tersedia.</p>
        <p class="text-sm mt-1">Hubungi kader Posyandu untuk informasi lebih lanjut.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
defineProps({ jadwal: Object })
</script>
