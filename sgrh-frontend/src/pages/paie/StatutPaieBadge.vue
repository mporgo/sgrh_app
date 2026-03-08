<template>
  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', cfg.cls]">
    {{ cfg.label }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({ statut: String })

const map = {
  brouillon: { label: 'Brouillon', cls: 'bg-gray-100 text-gray-600'   },
  valide:    { label: 'Validé',    cls: 'bg-blue-100 text-blue-700'   },
  paye:      { label: 'Payé',      cls: 'bg-green-100 text-green-700' },
}

const cfg = computed(() => map[props.statut] ?? { label: props.statut, cls: 'bg-gray-100 text-gray-600' })
</script>