<template>
  <div class="space-y-3 text-sm">

    <!-- Éléments de rémunération -->
    <div class="bg-gray-50 rounded-xl p-4 space-y-2">
      <h4 class="font-semibold text-gray-700 mb-3">Détail du bulletin</h4>

      <!-- Salaire de base -->
      <div class="flex justify-between">
        <span class="text-gray-600">Salaire de base</span>
        <span class="font-medium text-gray-800">{{ fmt(calcul.salaire_base) }}</span>
      </div>

      <!-- Primes -->
      <template v-if="primes.length">
        <div class="border-t border-gray-200 pt-2 mt-2">
          <p class="text-xs font-semibold text-green-700 mb-1">Primes</p>
          <div v-for="el in primes" :key="el.libelle" class="flex justify-between text-green-700">
            <span>+ {{ el.libelle }}</span>
            <span>{{ fmt(el.montant) }}</span>
          </div>
        </div>
      </template>

      <!-- Avantages -->
      <template v-if="avantages.length">
        <div class="border-t border-gray-200 pt-2 mt-2">
          <p class="text-xs font-semibold text-blue-700 mb-1">Avantages en nature</p>
          <div v-for="el in avantages" :key="el.libelle" class="flex justify-between text-blue-700">
            <span>+ {{ el.libelle }}</span>
            <span>{{ fmt(el.montant) }}</span>
          </div>
        </div>
      </template>

      <!-- Déductions -->
      <template v-if="deductions.length">
        <div class="border-t border-gray-200 pt-2 mt-2">
          <p class="text-xs font-semibold text-red-600 mb-1">Déductions</p>
          <div v-for="el in deductions" :key="el.libelle" class="flex justify-between text-red-600">
            <span>- {{ el.libelle }}</span>
            <span>{{ fmt(el.montant) }}</span>
          </div>
        </div>
      </template>

      <!-- Cotisations obligatoires -->
      <div class="border-t border-gray-200 pt-2 mt-2 space-y-1">
        <p class="text-xs font-semibold text-gray-500 mb-1">Cotisations obligatoires</p>
        <div class="flex justify-between text-orange-600">
          <span>- CNSS (5.5%)</span>
          <span>{{ fmt(calcul.cotisation_cnss) }}</span>
        </div>
        <div class="flex justify-between text-orange-600">
          <span>- IUTS</span>
          <span>{{ fmt(calcul.impot_iuts) }}</span>
        </div>
      </div>
    </div>

    <!-- Net à payer -->
    <div class="flex items-center justify-between bg-primary-700 text-white rounded-xl px-5 py-4">
      <span class="font-semibold text-lg">NET À PAYER</span>
      <span class="text-2xl font-bold">{{ fmt(calcul.net_a_payer) }}</span>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  calcul:   { type: Object, required: true },
  elements: { type: Array,  default: () => [] },
})

function fmt(v) {
  return Number(v ?? 0).toLocaleString('fr-FR') + ' FCFA'
}

const primes     = computed(() => props.elements.filter(e => e.type === 'prime'))
const deductions = computed(() => props.elements.filter(e => e.type === 'deduction'))
const avantages  = computed(() => props.elements.filter(e => e.type === 'avantage'))
</script>