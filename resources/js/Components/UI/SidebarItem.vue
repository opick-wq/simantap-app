<template>
  <Link :href="href"
        :class="[
          'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150',
          active
            ? 'bg-blue-600 text-white shadow-sm'
            : 'text-slate-300 hover:bg-slate-700/60 hover:text-white',
        ]">
    <span class="text-base w-5 text-center shrink-0 leading-none">{{ icon }}</span>
    <span class="flex-1 truncate">{{ label }}</span>
    <span v-if="badge && badge > 0"
          class="bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full shrink-0 font-semibold">
      {{ badge > 9 ? '9+' : badge }}
    </span>
  </Link>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const props = defineProps({
  href:  String,
  icon:  String,
  label: String,
  badge: Number,
})

const page = usePage()
const active = computed(() => {
  const path    = new URL(props.href, window.location.origin).pathname
  const current = page.url.split('?')[0]
  if (path === '/') return current === '/'
  return current === path || current.startsWith(path + '/')
})
</script>
