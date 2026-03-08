<template>
  <form @submit.prevent="$emit('submit', form)" class="space-y-5">

    <!-- Type de congé -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Type de congé *</label>
      <div class="grid grid-cols-2 gap-2">
        <button
          v-for="type in typeConges"
          :key="type.id"
          type="button"
          @click="form.type_conge_id = type.id"
          :class="[
            'px-3 py-2.5 rounded-lg border-2 text-sm font-medium transition text-left',
            form.type_conge_id === type.id
              ? 'border-primary-500 bg-primary-50 text-primary-700'
              : 'border-gray-200 hover:border-gray-300 text-gray-600'
          ]"
        >
          <span
            class="inline-block w-2 h-2 rounded-full mr-2"
            :style="{ backgroundColor: type.couleur }"
          />
          {{ type.libelle }}
          <span class="block text-xs font-normal text-gray-400 mt-0.5">
            {{ type.jours_annuels }}j/an
          </span>
        </button>
      </div>
      <p v-if="errors.type_conge_id" class="text-red-500 text-xs mt-1">{{ errors.type_conge_id }}</p>
    </div>

    <!-- Dates -->
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Date de début *</label>
        <input v-model="form.date_debut" type="date" class="input-field" :min="today" />
        <p v-if="errors.date_debut" class="text-red-500 text-xs mt-1">{{ errors.date_debut }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin *</label>
        <input v-model="form.date_fin" type="date" class="input-field" :min="form.date_debut || today" />
        <p v-if="errors.date_fin" class="text-red-500 text-xs mt-1">{{ errors.date_fin }}</p>
      </div>
    </div>

    <!-- Aperçu jours ouvrables -->
    <div v-if="form.date_debut && form.date_fin" class="bg-blue-50 border border-blue-200 rounded-lg p-3 flex items-center gap-2">
      <CalendarDaysIcon class="w-5 h-5 text-blue-500 shrink-0" />
      <p class="text-sm text-blue-700">
        Durée estimée : <strong>{{ joursOuvrables }} jour(s) ouvrable(s)</strong>
      </p>
    </div>

    <!-- Commentaire -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Commentaire</label>
      <textarea
        v-model="form.commentaire"
        rows="3"
        class="input-field resize-none"
        placeholder="Informations complémentaires (optionnel)..."
      />
    </div>

  </form>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { CalendarDaysIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  typeConges: { type: Array,  default: () => [] },
  errors:     { type: Object, default: () => ({}) },
})

defineEmits(['submit'])

const today = new Date().toISOString().split('T')[0]

const form = reactive({
  type_conge_id: '',
  date_debut:    '',
  date_fin:      '',
  commentaire:   '',
})

// Calcule les jours ouvrables côté frontend (preview)
const joursOuvrables = computed(() => {
  if (!form.date_debut || !form.date_fin) return 0
  let count = 0
  const current = new Date(form.date_debut)
  const fin     = new Date(form.date_fin)
  while (current <= fin) {
    const day = current.getDay()
    if (day !== 0 && day !== 6) count++
    current.setDate(current.getDate() + 1)
  }
  return count
})

defineExpose({ form })
</script>