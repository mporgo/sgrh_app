<template>
  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', colorClass]">
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  value:  { type: String, required: true },
  type:   { type: String, default: 'statut' }, // statut | contrat
})

const maps = {
  statut: {
    actif:     'bg-green-100 text-green-800',
    inactif:   'bg-gray-100 text-gray-600',
    suspendu:  'bg-red-100 text-red-700',
  },
  contrat: {
    CDI:       'bg-blue-100 text-blue-800',
    CDD:       'bg-orange-100 text-orange-700',
    Stage:     'bg-purple-100 text-purple-700',
    Freelance: 'bg-teal-100 text-teal-700',
  },
}

const colorClass = computed(() => maps[props.type]?.[props.value] ?? 'bg-gray-100 text-gray-600')
const label      = computed(() => props.value)
</script>