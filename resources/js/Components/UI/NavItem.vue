<template>
  <Link :href="href"
        :class="['flex-1 flex flex-col items-center justify-center py-2 text-xs relative',
                 active ? 'text-blue-600' : 'text-gray-500']">
    <span v-if="badge && badge > 0"
          class="absolute top-1 right-4 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
      {{ badge > 9 ? '9+' : badge }}
    </span>
    <component :is="iconComponent" class="w-6 h-6 mb-0.5" />
    <span>{{ label }}</span>
  </Link>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import {
  HomeIcon, UsersIcon, BellAlertIcon, ChartBarIcon
} from '@heroicons/vue/24/outline'
import {
  HomeIcon as HomeIconSolid, UsersIcon as UsersIconSolid,
  BellAlertIcon as BellSolid, ChartBarIcon as ChartSolid
} from '@heroicons/vue/24/solid'

const props = defineProps({
  href: String, active: Boolean, icon: String, label: String, badge: Number
})

const iconMap = {
  home: [HomeIcon, HomeIconSolid],
  users: [UsersIcon, UsersIconSolid],
  bell: [BellAlertIcon, BellSolid],
  chart: [ChartBarIcon, ChartSolid],
}

const iconComponent = computed(() =>
  props.active ? iconMap[props.icon][1] : iconMap[props.icon][0]
)
</script>
