<template>
  <form @submit.prevent="$emit('submit', form)" class="space-y-5">

    <!-- Employé -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Employé *</label>
      <select v-model="form.employe_id" class="input-field">
        <option value="">— Sélectionner un employé —</option>
        <option v-for="e in employes" :key="e.id" :value="e.id">
          {{ e.user?.name }} · {{ e.poste?.titre ?? 'Sans poste' }}
        </option>
      </select>
      <p v-if="errors.employe_id" class="text-red-500 text-xs mt-1">{{ errors.employe_id }}</p>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <!-- Type -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
        <select v-model="form.type" class="input-field">
          <option value="">— Sélectionner —</option>
          <option value="annuelle">Évaluation annuelle</option>
          <option value="semestrielle">Évaluation semestrielle</option>
          <option value="periode_essai">Fin de période d'essai</option>
          <option value="autre">Autre</option>
        </select>
        <p v-if="errors.type" class="text-red-500 text-xs mt-1">{{ errors.type }}</p>
      </div>

      <!-- Date -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Date prévue *</label>
        <input v-model="form.date_evaluation" type="date" class="input-field" />
        <p v-if="errors.date_evaluation" class="text-red-500 text-xs mt-1">{{ errors.date_evaluation }}</p>
      </div>
    </div>

    <!-- Objectifs fixés -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Objectifs à évaluer</label>
      <textarea
        v-model="form.objectifs_fixes"
        rows="4"
        class="input-field resize-none"
        placeholder="Décrire les objectifs et critères d'évaluation pour cet entretien..."
      />
    </div>

    <!-- Date prochaine évaluation -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Date de la prochaine évaluation</label>
      <input v-model="form.date_prochaine" type="date" class="input-field" />
    </div>

  </form>
</template>

<script setup>
import { reactive } from 'vue'

const props = defineProps({
  employes: { type: Array,  default: () => [] },
  errors:   { type: Object, default: () => ({}) },
  initial:  { type: Object, default: () => ({}) },
})

defineEmits(['submit'])

const form = reactive({
  employe_id:       '',
  type:             '',
  date_evaluation:  '',
  date_prochaine:   '',
  objectifs_fixes:  '',
  ...props.initial,
})

defineExpose({ form })
</script>