<template>
  <AppModal
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    title="Traiter la demande"
    size="sm"
  >
    <div v-if="conge" class="space-y-4">

      <!-- Infos demande -->
      <div class="bg-gray-50 rounded-lg p-4 space-y-2 text-sm">
        <div class="flex justify-between">
          <span class="text-gray-500">Employé</span>
          <span class="font-medium">{{ conge.employe?.user?.name }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-500">Type</span>
          <span class="font-medium">{{ conge.type_conge?.libelle }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-500">Période</span>
          <span class="font-medium">{{ formatDate(conge.date_debut) }} → {{ formatDate(conge.date_fin) }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-500">Durée</span>
          <span class="font-bold text-primary-600">{{ conge.nombre_jours }} jour(s)</span>
        </div>
        <div v-if="conge.commentaire" class="border-t border-gray-200 pt-2">
          <span class="text-gray-500">Commentaire :</span>
          <p class="text-gray-700 mt-1">{{ conge.commentaire }}</p>
        </div>
      </div>

      <!-- Motif refus -->
      <div v-if="action === 'refuser'">
        <label class="block text-sm font-medium text-gray-700 mb-1">Motif du refus *</label>
        <textarea
          v-model="motifRefus"
          rows="3"
          class="input-field resize-none"
          placeholder="Expliquez le motif du refus..."
        />
        <p v-if="motifError" class="text-red-500 text-xs mt-1">{{ motifError }}</p>
      </div>

    </div>

    <template #footer>
      <button @click="$emit('update:modelValue', false)" class="btn-secondary">Annuler</button>
      <button
        @click="submit('refuser')"
        :disabled="loading"
        class="btn-danger"
      >
        Refuser
      </button>
      <button
        @click="submit('valider')"
        :disabled="loading"
        class="btn-primary flex items-center gap-2"
      >
        <span v-if="loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
        Valider
      </button>
    </template>

  </AppModal>
</template>

<script setup>
import { ref } from 'vue'
import AppModal from '@/components/common/AppModal.vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  conge:      { type: Object,  default: null  },
  loading:    { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'traiter'])

const action     = ref('')
const motifRefus = ref('')
const motifError = ref('')

function formatDate(d) {
  return d ? new Date(d).toLocaleDateString('fr-FR') : '—'
}

function submit(act) {
  action.value     = act
  motifError.value = ''

  if (act === 'refuser' && !motifRefus.value.trim()) {
    motifError.value = 'Le motif est obligatoire.'
    return
  }

  emit('traiter', {
    action:      act,
    motif_refus: act === 'refuser' ? motifRefus.value : undefined,
  })
}
</script>