<template>
  <div class="space-y-5">

    <!-- Infos générales -->
    <div class="grid grid-cols-2 gap-4 text-sm">
      <InfoRow label="Employé"    :value="e.employe?.user?.name" />
      <InfoRow label="Évaluateur" :value="e.evaluateur?.name" />
      <InfoRow label="Type"       :value="e.type_label" />
      <InfoRow label="Date"       :value="formatDate(e.date_evaluation)" />
      <InfoRow label="Prochaine"  :value="formatDate(e.date_prochaine)" />
      <InfoRow label="Statut">
        <template #value><StatutBadge :statut="e.statut" /></template>
      </InfoRow>
    </div>

    <!-- Note & Score -->
    <div v-if="e.note_globale" class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl">
      <NoteGlobale :note="e.note_globale" />
      <div v-if="e.score" class="flex-1">
        <div class="flex justify-between text-xs text-gray-500 mb-1">
          <span>Score</span><span class="font-bold text-gray-800">{{ e.score }}/100</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3">
          <div
            class="h-3 rounded-full bg-primary-500 transition-all"
            :style="{ width: e.score + '%' }"
          />
        </div>
      </div>
    </div>

    <!-- Sections contenu -->
    <template v-for="section in sections" :key="section.key">
      <div v-if="e[section.key]" class="space-y-1">
        <h4 class="text-sm font-semibold text-gray-700">{{ section.label }}</h4>
        <p class="text-sm text-gray-600 bg-gray-50 rounded-lg p-3 whitespace-pre-line">
          {{ e[section.key] }}
        </p>
      </div>
    </template>

    <!-- Commentaire employé -->
    <div class="border-t border-gray-100 pt-4">
      <h4 class="text-sm font-semibold text-gray-700 mb-2">Commentaire de l'employé</h4>
      <div v-if="e.commentaire_employe" class="text-sm text-gray-600 bg-blue-50 rounded-lg p-3">
        {{ e.commentaire_employe }}
        <p class="text-xs text-green-600 mt-2">✅ Signé par l'employé</p>
      </div>
      <div v-else-if="e.statut === 'terminee' && isMyEvaluation" class="space-y-2">
        <textarea
          v-model="commentaire"
          rows="3"
          class="input-field resize-none"
          placeholder="Votre commentaire sur cette évaluation..."
        />
        <button @click="$emit('commenter', commentaire)" class="btn-primary text-sm">
          Signer et commenter
        </button>
      </div>
      <p v-else class="text-sm text-gray-400 italic">Aucun commentaire de l'employé.</p>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import InfoRow     from '@/components/common/InfoRow.vue'
import NoteGlobale from './NoteGlobale.vue'
import StatutBadge from './StatutBadge.vue'

const props = defineProps({ evaluation: { type: Object, required: true } })
defineEmits(['commenter'])

const authStore   = useAuthStore()
const commentaire = ref('')
const e           = computed(() => props.evaluation)

const isMyEvaluation = computed(() =>
  e.value.employe?.user?.id === authStore.user?.id
)

function formatDate(d) {
  return d ? new Date(d).toLocaleDateString('fr-FR') : '—'
}

const sections = [
  { key: 'objectifs_fixes',        label: 'Objectifs fixés' },
  { key: 'objectifs_atteints',     label: 'Objectifs atteints' },
  { key: 'points_forts',           label: 'Points forts' },
  { key: 'axes_amelioration',      label: "Axes d'amélioration" },
  { key: 'commentaire_evaluateur', label: "Commentaire de l'évaluateur" },
]
</script>