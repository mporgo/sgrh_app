<template>
  <form @submit.prevent="$emit('submit', form)" class="space-y-5">

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

      <!-- Titre -->
      <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
        <input v-model="form.titre" type="text" class="input-field" placeholder="Nom de la formation..." />
        <p v-if="errors.titre" class="text-red-500 text-xs mt-1">{{ errors.titre }}</p>
      </div>

      <!-- Type -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
        <select v-model="form.type" class="input-field">
          <option value="">— Sélectionner —</option>
          <option value="interne">Formation interne 🏢</option>
          <option value="externe">Formation externe 🌍</option>
          <option value="elearning">E-Learning 💻</option>
        </select>
        <p v-if="errors.type" class="text-red-500 text-xs mt-1">{{ errors.type }}</p>
      </div>

      <!-- Formateur -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Formateur / Organisme</label>
        <input v-model="form.formateur" type="text" class="input-field" placeholder="Nom du formateur ou organisme" />
      </div>

      <!-- Dates -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Date de début *</label>
        <input v-model="form.date_debut" type="date" class="input-field" />
        <p v-if="errors.date_debut" class="text-red-500 text-xs mt-1">{{ errors.date_debut }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin *</label>
        <input v-model="form.date_fin" type="date" class="input-field" />
        <p v-if="errors.date_fin" class="text-red-500 text-xs mt-1">{{ errors.date_fin }}</p>
      </div>

      <!-- Durée -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Durée (heures) *</label>
        <input v-model.number="form.duree_heures" type="number" min="1" class="input-field" placeholder="14" />
        <p v-if="errors.duree_heures" class="text-red-500 text-xs mt-1">{{ errors.duree_heures }}</p>
      </div>

      <!-- Places max -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de places (vide = illimité)</label>
        <input v-model.number="form.places_max" type="number" min="1" class="input-field" placeholder="20" />
      </div>

      <!-- Coût -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Coût (FCFA)</label>
        <input v-model.number="form.cout" type="number" min="0" class="input-field" placeholder="0" />
      </div>

      <!-- Lieu -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Lieu</label>
        <input v-model="form.lieu" type="text" class="input-field" placeholder="Salle A, ville..." />
      </div>

      <!-- Lien e-learning -->
      <div v-if="form.type === 'elearning'" class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Lien de la plateforme</label>
        <input v-model="form.lien_elearning" type="url" class="input-field" placeholder="https://..." />
        <p v-if="errors.lien_elearning" class="text-red-500 text-xs mt-1">{{ errors.lien_elearning }}</p>
      </div>

    </div>

    <!-- Description -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
      <textarea
        v-model="form.description"
        rows="3"
        class="input-field resize-none"
        placeholder="Objectifs, contenu, pré-requis..."
      />
    </div>

  </form>
</template>

<script setup>
import { reactive, watch } from 'vue'

const props = defineProps({
  initial: { type: Object, default: () => ({}) },
  errors:  { type: Object, default: () => ({}) },
})

defineEmits(['submit'])

const form = reactive({
  titre: '', description: '', formateur: '', type: '',
  date_debut: '', date_fin: '', duree_heures: '',
  places_max: '', cout: 0, lieu: '', lien_elearning: '',
  ...props.initial,
})

defineExpose({ form })
</script>