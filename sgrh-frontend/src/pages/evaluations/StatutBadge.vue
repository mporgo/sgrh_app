<template>
  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', cls]">
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({ statut: String })

const map = {
  planifiee: { label: 'Planifiée',  cls: 'bg-yellow-100 text-yellow-700' },
  en_cours:  { label: 'En cours',   cls: 'bg-blue-100 text-blue-700'    },
  terminee:  { label: 'Terminée',   cls: 'bg-green-100 text-green-700'  },
  annulee:   { label: 'Annulée',    cls: 'bg-gray-100 text-gray-500'    },
}

const cls   = computed(() => map[props.statut]?.cls   ?? 'bg-gray-100 text-gray-500')
const label = computed(() => map[props.statut]?.label ?? props.statut)
</script>