<template>
  <component :is="to ? Link : 'div'" v-bind="to ? { href: to.url } : {}"
             :class="['card flex items-center gap-3 p-3', to ? 'cursor-pointer active:opacity-80' : '']">
    <div :class="['w-10 h-10 rounded-lg flex items-center justify-center text-lg flex-shrink-0',
                  bgMap[color]]">
      {{ icon }}
    </div>
    <div>
      <p :class="['text-2xl font-bold', textMap[color]]">{{ value }}</p>
      <p class="text-xs text-gray-500 leading-tight">{{ label }}</p>
    </div>
  </component>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  label: String, value: [Number, String], color: String, icon: String, to: Object
})

const bgMap   = { blue: 'bg-blue-100', green: 'bg-green-100', red: 'bg-red-100', yellow: 'bg-yellow-100' }
const textMap  = { blue: 'text-blue-700', green: 'text-green-700', red: 'text-red-700', yellow: 'text-yellow-700' }
</script>
