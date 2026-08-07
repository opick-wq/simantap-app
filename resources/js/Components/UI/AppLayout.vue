<template>
  <div class="min-h-screen bg-slate-50 flex">

    <!-- Overlay mobile -->
    <div v-if="sidebarOpen"
         class="fixed inset-0 bg-black/50 z-30 lg:hidden"
         @click="sidebarOpen = false" />

    <!-- ══ SIDEBAR ══ -->
    <aside :class="[
      'fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 flex flex-col transition-transform duration-200',
      'lg:translate-x-0 lg:static lg:z-auto lg:sticky lg:top-0 lg:h-screen',
      sidebarOpen ? 'translate-x-0' : '-translate-x-full',
    ]">

      <!-- Logo -->
      <div class="flex items-center justify-between px-5 py-4 border-b border-slate-700/60">
        <Link :href="homeUrl" class="flex items-center gap-3">
          <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center shrink-0 shadow-lg">
            <span class="text-white text-sm font-bold">SM</span>
          </div>
          <div>
            <p class="font-bold text-white text-sm leading-tight">SI-MANTAP</p>
            <p class="text-xs text-slate-400 leading-tight">Monitoring Posyandu</p>
          </div>
        </Link>
        <button @click="sidebarOpen = false" class="text-slate-400 hover:text-white lg:hidden">✕</button>
      </div>

      <!-- Nav -->
      <nav class="flex-1 px-3 py-3 overflow-y-auto space-y-0.5">

        <!-- Kader / Nakes / Petugas / Admin -->
        <template v-if="['kader','nakes','petugas','admin'].includes(role)">
          <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest px-2 pt-1 pb-2">
            Menu Utama
          </p>
          <SidebarItem :href="role === 'admin' ? '/admin/dashboard' : '/'" icon="📊" label="Dashboard" />
          <SidebarItem href="/balita"               icon="👶" label="Data Balita" />
          <SidebarItem v-if="['kader','nakes','admin'].includes(role)"
                       href="/pengukuran/create"   icon="📏" label="Input Pengukuran" />
          <SidebarItem href="/peringatan"            icon="🚨" label="Peringatan EWS" :badge="totalEws" />

          <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest px-2 pt-4 pb-2">
            Laporan
          </p>
          <SidebarItem href="/sesi"                 icon="📅" label="Sesi Posyandu" />
          <SidebarItem href="/laporan"              icon="📈" label="Laporan & Ekspor" />

          <!-- Nakes: manajemen kader -->
          <template v-if="role === 'nakes'">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest px-2 pt-4 pb-2">
              Manajemen
            </p>
            <SidebarItem href="/users" icon="👥" label="Kelola Kader" />
          </template>

          <!-- Petugas: manajemen nakes & posyandu -->
          <template v-if="role === 'petugas'">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest px-2 pt-4 pb-2">
              Manajemen
            </p>
            <SidebarItem href="/users"    icon="👩‍⚕️" label="Kelola Nakes" />
            <SidebarItem href="/posyandu" icon="🏥"   label="Kelola Posyandu" />
          </template>

          <!-- Admin: pengaturan sistem -->
          <template v-if="role === 'admin'">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest px-2 pt-4 pb-2">
              Pengaturan
            </p>
            <SidebarItem href="/wilayah"        icon="🗺️" label="Manajemen Wilayah" />
            <SidebarItem href="/posyandu"       icon="🏥" label="Data Posyandu" />
            <SidebarItem href="/users"          icon="👤" label="Manajemen Akun" />
            <SidebarItem href="/dinas/dashboard" icon="🏛️" label="Ringkasan Dinas" />
          </template>
        </template>

        <!-- Dinas -->
        <template v-else-if="role === 'dinas'">
          <SidebarItem href="/dinas/dashboard" icon="📈" label="Dashboard Dinas" />
        </template>

        <!-- Orang tua -->
        <template v-else-if="role === 'orang_tua'">
          <SidebarItem href="/ortu/dashboard" icon="🏠" label="Beranda" />
          <SidebarItem href="/ortu/jadwal"    icon="📅" label="Jadwal Posyandu" />
        </template>
      </nav>

      <!-- ══ SIDEBAR FOOTER ══ -->
      <div class="px-3 pb-4 pt-2 border-t border-slate-700/60 space-y-1">
        <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-slate-800">
          <div :class="['w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-base', avatarBg]">
            {{ roleIcon }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-white text-sm font-semibold truncate">{{ $page.props.auth.user.nama }}</p>
            <p class="text-slate-400 text-xs truncate">{{ roleLabel }}</p>
          </div>
        </div>
        <Link href="/akun/ganti-password"
              class="flex items-center gap-3 w-full px-3 py-2 rounded-lg
                     text-slate-300 hover:bg-slate-800 hover:text-white
                     text-sm transition-all duration-150">
          <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
          </svg>
          <span>Ganti Password</span>
        </Link>
        <Link href="/logout" method="post" as="button"
              class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg
                     text-red-400 hover:bg-red-500/10 hover:text-red-300
                     text-sm font-medium transition-all duration-150">
          <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
          </svg>
          <span>Keluar</span>
        </Link>
      </div>

    </aside>

    <!-- ══ AREA UTAMA ══ -->
    <div class="flex-1 flex flex-col min-w-0">

      <!-- Top bar -->
      <header class="bg-white border-b border-slate-200 sticky top-0 z-20 shadow-sm">
        <div class="flex items-center gap-3 px-4 h-14">
          <!-- Hamburger (mobile) -->
          <button @click="sidebarOpen = true"
                  class="lg:hidden p-1.5 rounded-lg text-slate-500 hover:bg-slate-100">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
          </button>

          <!-- Breadcrumb -->
          <div class="flex items-center gap-2 text-sm text-slate-500 flex-1 min-w-0">
            <span class="font-semibold text-slate-800">SI-MANTAP</span>
            <span class="text-slate-300">|</span>
            <span v-if="title" class="truncate text-slate-500">{{ title }}</span>
            <span v-else class="truncate text-slate-500">Dashboard</span>
          </div>

          <!-- Bell notifikasi -->
          <button v-if="['kader','nakes','petugas','admin'].includes(role)"
                  @click="toggleNotif"
                  class="relative p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span v-if="unreadCount > 0"
                  class="absolute top-1.5 right-1.5 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold">
              {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
          </button>
        </div>
      </header>

      <!-- Panel notifikasi -->
      <div v-if="notifOpen"
           class="fixed top-14 right-0 w-80 bg-white shadow-2xl border-l border-slate-200 bottom-0 z-30 overflow-y-auto">
        <div class="flex items-center justify-between px-4 py-3 border-b sticky top-0 bg-white">
          <h3 class="font-semibold text-slate-800 text-sm">Notifikasi EWS</h3>
          <button @click="notifOpen = false" class="text-slate-400 hover:text-slate-600 text-lg leading-none">✕</button>
        </div>
        <div v-if="notifikasi.length === 0" class="px-4 py-10 text-center text-slate-400 text-sm">
          <div class="text-3xl mb-2">🔔</div>
          Tidak ada notifikasi baru
        </div>
        <div v-for="n in notifikasi" :key="n.id"
             :class="['px-4 py-3 border-b hover:bg-slate-50 cursor-pointer',
                      n.level === 'MERAH' ? 'border-l-4 border-l-red-400' : 'border-l-4 border-l-yellow-400']">
          <p class="text-sm font-semibold text-slate-800">{{ n.balita_nama }}</p>
          <p class="text-xs text-slate-600 mt-0.5">{{ n.pesan }}</p>
          <p class="text-xs text-slate-400 mt-1">{{ n.waktu }}</p>
        </div>
      </div>

      <!-- Konten halaman -->
      <main class="flex-1 overflow-auto bg-slate-50">
        <slot />
      </main>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import SidebarItem from './SidebarItem.vue'

defineProps({ title: String })

const page        = usePage()
const sidebarOpen = ref(false)
const notifOpen   = ref(false)
const notifikasi  = ref([])
const unreadCount = ref(0)

const role     = computed(() => page.props.auth.user.role)
const totalEws = computed(() => page.props.ewsCount ?? 0)

const homeUrl = computed(() => ({
  dinas:     '/dinas/dashboard',
  admin:     '/admin/dashboard',
  orang_tua: '/ortu/dashboard',
}[role.value] ?? '/'))

const roleLabel = computed(() => ({
  kader:     'Kader Posyandu',
  nakes:     'Bidan / Nakes',
  petugas:   'Petugas Puskesmas',
  admin:     'Administrator',
  dinas:     'Dinas Kesehatan',
  orang_tua: 'Orang Tua',
}[role.value] ?? role.value))

const roleIcon = computed(() => ({
  kader:     '🏘️',
  nakes:     '👩‍⚕️',
  petugas:   '🏛️',
  admin:     '⚙️',
  dinas:     '📊',
  orang_tua: '👨‍👩‍👧',
}[role.value] ?? '👤'))

const avatarBg = computed(() => ({
  admin:     'bg-purple-600',
  petugas:   'bg-blue-700',
  nakes:     'bg-emerald-600',
  kader:     'bg-blue-500',
  dinas:     'bg-teal-600',
  orang_tua: 'bg-pink-500',
}[role.value] ?? 'bg-blue-600'))

function toggleNotif() {
  notifOpen.value = !notifOpen.value
  if (notifOpen.value) fetchNotif()
}

async function fetchNotif() {
  try {
    const res  = await fetch('/notif/unread')
    const data = await res.json()
    notifikasi.value  = data.items
    unreadCount.value = data.count
  } catch {}
}

let timer
onMounted(() => {
  fetchNotif()
  timer = setInterval(fetchNotif, 60_000)
})
onUnmounted(() => clearInterval(timer))
</script>
