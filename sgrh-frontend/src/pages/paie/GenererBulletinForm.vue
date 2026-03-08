<template>
  <form @submit.prevent="$emit('submit', form)" class="space-y-5">

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

      <!-- Employé -->
      <div class="sm:col-span-3">
        <label class="block text-sm font-medium text-gray-700 mb-1">Employé *</label>
        <select v-model="form.employe_id" class="input-field" @change="onEmployeChange">
          <option value="">— Sélectionner un employé —</option>
          <option v-for="e in employes" :key="e.id" :value="e.id">
            {{ e.user?.name }} · {{ e.matricule }}
          </option>
        </select>
        <p v-if="errors.employe_id" class="text-red-500 text-xs mt-1">{{ errors.employe_id }}</p>
      </div>

      <!-- Mois -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Mois *</label>
        <select v-model.number="form.mois" class="input-field" @change="onPeriodeChange">
          <option value="">— Mois —</option>
          <option v-for="(label, val) in moisOptions" :key="val" :value="parseInt(val)">
            {{ label }}
          </option>
        </select>
        <p v-if="errors.mois" class="text-red-500 text-xs mt-1">{{ errors.mois }}</p>
      </div>

      <!-- Année -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Année *</label>
        <select v-model.number="form.annee" class="input-field" @change="onPeriodeChange">
          <option v-for="y in annees" :key="y" :value="y">{{ y }}</option>
        </select>
      </div>

      <!-- Bouton aperçu -->
      <div class="flex items-end">
        <button
          type="button"
          @click="$emit('apercu', { employe_id: form.employe_id, elements: form.elements })"
          :disabled="!form.employe_id"
          class="btn-secondary w-full text-sm"
        >
          👁 Aperçu calcul
        </button>
      </div>

    </div>

    <!-- Éléments variables -->
    <div class="space-y-3">
      <div class="flex items-center justify-between">
        <h4 class="text-sm font-semibold text-gray-700">Éléments variables</h4>
        <button type="button" @click="ajouterElement" class="btn-secondary text-xs px-3 py-1.5">
          + Ajouter
        </button>
      </div>

      <div
        v-for="(el, i) in form.elements"
        :key="i"
        class="grid grid-cols-12 gap-2 items-center bg-gray-50 rounded-lg p-3"
      >
        <!-- Libelle -->
        <div class="col-span-4">
          <input
            v-model="el.libelle"
            type="text"
            class="input-field text-xs"
            placeholder="Libellé..."
            @input="onElementChange"
          />
        </div>
        <!-- Type -->
        <div class="col-span-3">
          <select v-model="el.type" class="input-field text-xs" @change="onElementChange">
            <option value="prime">Prime 🟢</option>
            <option value="deduction">Déduction 🔴</option>
            <option value="avantage">Avantage 🔵</option>
          </select>
        </div>
        <!-- Montant -->
        <div class="col-span-3">
          <input
            v-model.number="el.montant"
            type="number"
            min="0"
            class="input-field text-xs"
            placeholder="Montant"
            @input="onElementChange"
          />
        </div>
        <!-- Supprimer -->
        <div class="col-span-2 flex justify-center">
          <button
            type="button"
            @click="supprimerElement(i)"
            class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded transition"
          >
            <XMarkIcon class="w-4 h-4" />
          </button>
        </div>
      </div>

      <p v-if="!form.elements.length" class="text-xs text-gray-400 italic text-center py-2">
        Aucun élément variable — seul le salaire de base et les avantages attribués seront calculés.
      </p>
    </div>

    <!-- Notes -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Notes internes</label>
      <textarea v-model="form.notes" rows="2" class="input-field resize-none" placeholder="Observations..." />
    </div>

  </form>
</template>

<script setup>
import { reactive } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  employes: { type: Array,  default: () => [] },
  errors:   { type: Object, default: () => ({}) },
  initial:  { type: Object, default: () => ({}) },
})

const emit = defineEmits(['submit', 'apercu', 'element-change'])

const anneeActuelle = new Date().getFullYear()
const annees        = Array.from({ length: 5 }, (_, i) => anneeActuelle - i + 1)

const moisOptions = {
  1: 'Janvier', 2: 'Février', 3: 'Mars', 4: 'Avril',
  5: 'Mai', 6: 'Juin', 7: 'Juillet', 8: 'Août',
  9: 'Septembre', 10: 'Octobre', 11: 'Novembre', 12: 'Décembre',
}

const form = reactive({
  employe_id: '',
  mois:       new Date().getMonth() + 1,
  annee:      anneeActuelle,
  elements:   [],
  notes:      '',
  ...props.initial,
})

function ajouterElement() {
  form.elements.push({ libelle: '', type: 'prime', montant: 0, commentaire: '' })
}

function supprimerElement(index) {
  form.elements.splice(index, 1)
  onElementChange()
}

function onEmployeChange() {
  emit('apercu', { employe_id: form.employe_id, elements: form.elements })
}

function onPeriodeChange() {
  // peut servir à vérifier si un bulletin existe déjà
}

function onElementChange() {
  emit('element-change', form.elements)
}

defineExpose({ form })
</script>