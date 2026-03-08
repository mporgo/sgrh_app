<template>
  <form @submit.prevent="$emit('submit', form)" class="space-y-6">

    <!-- Section : Informations personnelles -->
    <fieldset class="space-y-4">
      <legend class="text-sm font-semibold text-primary-700 uppercase tracking-wide border-b border-gray-200 pb-2 w-full">
        Informations personnelles
      </legend>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet *</label>
          <input v-model="form.name" type="text" class="input-field" placeholder="Kader Traoré" />
          <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
          <input v-model="form.email" type="email" class="input-field" placeholder="kader@sgrh.com" />
          <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
          <input v-model="form.phone" type="tel" class="input-field" placeholder="+226 70 00 00 00" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date de naissance</label>
          <input v-model="form.date_naissance" type="date" class="input-field" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Genre</label>
          <select v-model="form.genre" class="input-field">
            <option value="">— Sélectionner —</option>
            <option value="M">Masculin</option>
            <option value="F">Féminin</option>
            <option value="autre">Autre</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
          <input v-model="form.adresse" type="text" class="input-field" placeholder="Bobo-Dioulasso, BF" />
        </div>
      </div>
    </fieldset>

    <!-- Section : Informations professionnelles -->
    <fieldset class="space-y-4">
      <legend class="text-sm font-semibold text-primary-700 uppercase tracking-wide border-b border-gray-200 pb-2 w-full">
        Informations professionnelles
      </legend>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Département</label>
          <select v-model="form.departement_id" class="input-field" @change="onDeptChange">
            <option value="">— Sélectionner —</option>
            <option v-for="d in departements" :key="d.id" :value="d.id">{{ d.nom }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Poste</label>
          <select v-model="form.poste_id" class="input-field">
            <option value="">— Sélectionner —</option>
            <option v-for="p in postesFiltres" :key="p.id" :value="p.id">{{ p.titre }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date d'embauche *</label>
          <input v-model="form.date_embauche" type="date" class="input-field" />
          <p v-if="errors.date_embauche" class="text-red-500 text-xs mt-1">{{ errors.date_embauche }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Type de contrat *</label>
          <select v-model="form.type_contrat" class="input-field">
            <option value="">— Sélectionner —</option>
            <option value="CDI">CDI</option>
            <option value="CDD">CDD</option>
            <option value="Stage">Stage</option>
            <option value="Freelance">Freelance</option>
          </select>
          <p v-if="errors.type_contrat" class="text-red-500 text-xs mt-1">{{ errors.type_contrat }}</p>
        </div>
        <div v-if="['CDD', 'Stage'].includes(form.type_contrat)">
          <label class="block text-sm font-medium text-gray-700 mb-1">Fin de contrat</label>
          <input v-model="form.fin_contrat" type="date" class="input-field" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Salaire de base (FCFA) *</label>
          <input v-model="form.salaire_base" type="number" min="0" class="input-field" placeholder="450000" />
          <p v-if="errors.salaire_base" class="text-red-500 text-xs mt-1">{{ errors.salaire_base }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Solde congés (jours/an)</label>
          <input v-model="form.conge_solde" type="number" min="0" class="input-field" placeholder="25" />
        </div>
        <div v-if="isEdit">
          <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
          <select v-model="form.statut" class="input-field">
            <option value="actif">Actif</option>
            <option value="inactif">Inactif</option>
            <option value="suspendu">Suspendu</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Notes internes</label>
        <textarea v-model="form.notes" rows="3" class="input-field resize-none" placeholder="Remarques, informations complémentaires..." />
      </div>
    </fieldset>

  </form>
</template>

<script setup>
import { reactive, computed, watch } from 'vue'

const props = defineProps({
  initial:      { type: Object, default: () => ({}) },
  errors:       { type: Object, default: () => ({}) },
  departements: { type: Array,  default: () => [] },
  postes:       { type: Array,  default: () => [] },
  isEdit:       { type: Boolean, default: false },
})

const emit = defineEmits(['submit'])

const form = reactive({
  name: '', email: '', phone: '', date_naissance: '',
  genre: '', adresse: '',
  departement_id: '', poste_id: '', manager_id: '',
  date_embauche: '', type_contrat: '', fin_contrat: '',
  salaire_base: '', conge_solde: 25,
  statut: 'actif', notes: '',
  ...props.initial,
})

// Filtrer les postes par département sélectionné
const postesFiltres = computed(() => {
  if (!form.departement_id) return props.postes
  return props.postes.filter(p => p.departement?.id === form.departement_id)
})

function onDeptChange() {
  form.poste_id = '' // reset poste si on change de département
}

// Exposer le form au parent via defineExpose
defineExpose({ form })
</script>