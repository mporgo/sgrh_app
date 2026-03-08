<template>
  <div class="card flex items-center gap-4">
    <div :class="['w-12 h-12 rounded-xl flex items-center justify-center shrink-0', bgColor]">
      <component :is="iconComponent" class="w-6 h-6" :class="iconColor" />
    </div>
    <div>
      <p class="text-2xl font-bold text-gray-800">{{ value }}</p>
      <p class="text-sm text-gray-500">{{ label }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  UsersIcon, CalendarDaysIcon, AcademicCapIcon,
  ChartBarIcon, StarIcon, BellIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  label: String,
  value: [String, Number],
  icon:  String,
  color: String,
})

const iconMap = {
  users: UsersIcon, calendar: CalendarDaysIcon,
  academic: AcademicCapIcon, chart: ChartBarIcon,
  star: StarIcon, bell: BellIcon,
}

const colorMap = {
  blue:   { bg: 'bg-blue-50',   icon: 'text-blue-500'   },
  yellow: { bg: 'bg-yellow-50', icon: 'text-yellow-500' },
  green:  { bg: 'bg-green-50',  icon: 'text-green-500'  },
  red:    { bg: 'bg-red-50',    icon: 'text-red-500'    },
  purple: { bg: 'bg-purple-50', icon: 'text-purple-500' },
}

const iconComponent = computed(() => iconMap[props.icon] || UsersIcon)
const bgColor   = computed(() => colorMap[props.color]?.bg   || 'bg-gray-50')
const iconColor = computed(() => colorMap[props.color]?.icon || 'text-gray-500')
</script>