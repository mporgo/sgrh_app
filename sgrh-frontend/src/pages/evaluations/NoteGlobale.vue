<template>
  <span :class="['inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold', config.class]">
    <span>{{ config.emoji }}</span>
    {{ config.label }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({ note: { type: String, default: null } })

const map = {
  insuffisant: { label: 'Insuffisant', emoji: '❌', class: 'bg-red-100 text-red-700' },
  passable:    { label: 'Passable',    emoji: '🔶', class: 'bg-orange-100 text-orange-700' },
  bien:        { label: 'Bien',        emoji: '✅', class: 'bg-blue-100 text-blue-700' },
  tres_bien:   { label: 'Très bien',   emoji: '⭐', class: 'bg-indigo-100 text-indigo-700' },
  excellent:   { label: 'Excellent',   emoji: '🏆', class: 'bg-green-100 text-green-700' },
}

const config = computed(() => map[props.note] ?? { label: 'Non noté', emoji: '—', class: 'bg-gray-100 text-gray-500' })
</script>