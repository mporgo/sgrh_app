<template>
  <form @submit.prevent="$emit('submit', form)" class="space-y-5">

    <!-- Note globale -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Note globale *</label>
      <div class="grid grid-cols-5 gap-2">
        <button
          v-for="note in notes"
          :key="note.value"
          type="button"
          @click="form.note_globale = note.value"
          :class="[
            'flex flex-col items-center p-3 rounded-xl border-2 transition text-center',
            form.note_globale === note.value
              ? 'border-primary-500 bg-primary-50'
              : 'border-gray-200 hover:border-gray-300'
          ]"
        >
          <span class="text-2xl">{{ note.emoji }}</span>
          <span class="text-xs font-medium text-gray-600 mt-1 leading-tight">{{ note.label }}</span>
        </button>
      </div>
      <p v-if="errors.note_globale" class="text-red-500 text-xs mt-1">{{ errors.note_globale }}</p>
    </div>

    <!-- Score numérique -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">
        Score numérique (optionnel)
        <span v-if="form.score" class="ml-2 font-bold text-primary-600">{{ form.score }}/100</span>
      </label>
      <input
        v-model.number="form.score"
        type="range" min="0" max="100" step="5"
        class="w-full accent-primary-500"
      />
      <div class="flex justify-between text-xs text-gray-400 mt-1">
        <span>0</span><span>25</span><span>50</span><span>75</span><span>100</span>
      </div>
    </div>

    <!-- Objectifs atteints -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Objectifs atteints</label>
      <textarea
        v-model="form.objectifs_atteints"
        rows="3"
        class="input-field resize-none"
        placeholder="Bilan des objectifs fixés lors de la dernière évaluation..."
      />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <!-- Points forts -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Points forts</label>
        <textarea
          v-model="form.points_forts"
          rows="3"
          class="input-field resize-none"
          placeholder="Compétences et comportements remarquables..."
        />
      </div>
      <!-- Axes d'amélioration -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Axes d'amélioration</label>
        <textarea
          v-model="form.axes_amelioration"
          rows="3"
          class="input-field resize-none"
          placeholder="Points à travailler pour la prochaine période..."
        />
      </div>
    </div>

    <!-- Commentaire évaluateur -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Commentaire de l'évaluateur</label>
      <textarea
        v-model="form.commentaire_evaluateur"
        rows="3"
        class="input-field resize-none"
        placeholder="Observations générales, recommandations..."
      />
    </div>

    <!-- Prochaine évaluation -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Prochaine évaluation prévue</label>
      <input v-model="form.date_prochaine" type="date" class="input-field" />
    </div>

  </form>
</template>

<script setup>
import { reactive } from 'vue'

const props = defineProps({
  initial: { type: Object, default: () => ({}) },
  errors:  { type: Object, default: () => ({}) },
})

defineEmits(['submit'])

const notes = [
  { value: 'insuffisant', label: 'Insuffisant', emoji: '❌' },
  { value: 'passable',    label: 'Passable',    emoji: '🔶' },
  { value: 'bien',        label: 'Bien',        emoji: '✅' },
  { value: 'tres_bien',   label: 'Très bien',   emoji: '⭐' },
  { value: 'excellent',   label: 'Excellent',   emoji: '🏆' },
]

const form = reactive({
  note_globale:           '',
  score:                  50,
  objectifs_atteints:     '',
  points_forts:           '',
  axes_amelioration:      '',
  commentaire_evaluateur: '',
  date_prochaine:         '',
  statut:                 'terminee',
  ...props.initial,
})

defineExpose({ form })
</script>