<template>
  <span :class="['inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium', cfg.cls]">
    <span>{{ cfg.icon }}</span>{{ cfg.label }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({ type: String })

const map = {
  interne:   { label: 'Interne',   icon: '🏢', cls: 'bg-blue-100 text-blue-700' },
  externe:   { label: 'Externe',   icon: '🌍', cls: 'bg-purple-100 text-purple-700' },
  elearning: { label: 'E-Learning',icon: '💻', cls: 'bg-teal-100 text-teal-700' },
}

const cfg = computed(() => map[props.type] ?? { label: props.type, icon: '📚', cls: 'bg-gray-100 text-gray-600' })
</script>