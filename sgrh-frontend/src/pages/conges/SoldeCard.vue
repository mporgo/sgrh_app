<template>
  <div class="card">
    <div class="flex items-center justify-between mb-3">
      <div class="flex items-center gap-2">
        <span
          class="w-3 h-3 rounded-full shrink-0"
          :style="{ backgroundColor: solde.couleur }"
        />
        <span class="text-sm font-semibold text-gray-700">{{ solde.libelle }}</span>
      </div>
      <span class="text-2xl font-bold" :style="{ color: solde.couleur }">
        {{ solde.restant }}j
      </span>
    </div>

    <!-- Barre de progression -->
    <div class="w-full bg-gray-100 rounded-full h-2 mb-3">
      <div
        class="h-2 rounded-full transition-all duration-500"
        :style="{
          width: solde.total > 0 ? (pourcent + '%') : '0%',
          backgroundColor: solde.couleur,
        }"
      />
    </div>

    <div class="flex justify-between text-xs text-gray-500">
      <span>{{ solde.pris }}j pris</span>
      <span v-if="solde.en_attente > 0" class="text-yellow-600">{{ solde.en_attente }}j en attente</span>
      <span>{{ solde.total }}j total</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  solde: { type: Object, required: true },
})

const pourcent = computed(() => {
  if (!props.solde.total) return 0
  return Math.round((props.solde.restant / props.solde.total) * 100)
})
</script>