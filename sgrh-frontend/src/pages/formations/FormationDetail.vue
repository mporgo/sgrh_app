<template>
  <div class="space-y-5" v-if="f">

    <!-- Infos générales -->
    <div class="grid grid-cols-2 gap-3 text-sm">
      <InfoRow label="Type">
        <template #value><TypeBadge :type="f.type" /></template>
      </InfoRow>
      <InfoRow label="Statut">
        <template #value><StatutFormationBadge :statut="f.statut" /></template>
      </InfoRow>
      <InfoRow label="Dates"       :value="`${formatDate(f.date_debut)} → ${formatDate(f.date_fin)}`" />
      <InfoRow label="Durée"       :value="`${f.duree_heures}h`" />
      <InfoRow label="Formateur"   :value="f.formateur" />
      <InfoRow label="Lieu"        :value="f.lieu || (f.type === 'elearning' ? 'En ligne' : null)" />
      <InfoRow label="Coût"        :value="f.cout > 0 ? formatCout(f.cout) + ' FCFA' : 'Gratuite'" />
      <InfoRow label="Places"      :value="f.places_max ? `${f.nb_inscrits}/${f.places_max}` : 'Illimitées'" />
    </div>

    <!-- Lien e-learning -->
    <div v-if="f.lien_elearning">
      <a :href="f.lien_elearning" target="_blank" class="text-sm text-primary-600 hover:underline flex items-center gap-1">
        🔗 Accéder à la plateforme
      </a>
    </div>

    <!-- Description -->
    <div v-if="f.description">
      <h4 class="text-sm font-semibold text-gray-700 mb-2">Description</h4>
      <p class="text-sm text-gray-600 bg-gray-50 rounded-lg p-3 whitespace-pre-line">{{ f.description }}</p>
    </div>

    <!-- Liste des inscrits (RH/Admin/Manager) -->
    <div v-if="inscrits.length && canManage" class="space-y-3">
      <h4 class="text-sm font-semibold text-gray-700 border-t border-gray-100 pt-4">
        Inscrits ({{ inscrits.length }})
      </h4>
      <div class="space-y-2">
        <div
          v-for="ins in inscrits"
          :key="ins.id"
          class="flex items-center justify-between p-3 rounded-lg bg-gray-50 text-sm"
        >
          <div>
            <p class="font-medium text-gray-800">{{ ins.employe?.user?.name }}</p>
            <p class="text-xs text-gray-500">{{ ins.employe?.poste }} · {{ ins.employe?.departement }}</p>
            <p v-if="ins.note !== null" class="text-xs font-medium text-primary-600 mt-0.5">
              Note : {{ ins.note }}/20
              <span v-if="ins.certificat_obtenu">🏅</span>
            </p>
          </div>
          <div class="flex items-center gap-2">
            <StatutFormationBadge :statut="ins.statut" />

            <!-- Valider / refuser si en attente -->
            <template v-if="ins.statut === 'en_attente'">
              <button
                @click="$emit('valider-inscription', { id: ins.id, action: 'valider' })"
                class="p-1 text-green-600 hover:bg-green-50 rounded transition"
                title="Valider"
              >
                <CheckIcon class="w-4 h-4" />
              </button>
              <button
                @click="$emit('valider-inscription', { id: ins.id, action: 'refuser' })"
                class="p-1 text-red-500 hover:bg-red-50 rounded transition"
                title="Refuser"
              >
                <XMarkIcon class="w-4 h-4" />
              </button>
            </template>

            <!-- Renseigner résultats si validé et formation terminée -->
            <button
              v-if="ins.statut === 'validee' && f.statut === 'terminee'"
              @click="openResultats(ins)"
              class="p-1 text-blue-600 hover:bg-blue-50 rounded transition"
              title="Renseigner les résultats"
            >
              <PencilSquareIcon class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal résultats inscription -->
    <AppModal v-model="modalResultatsOpen" title="Résultats de l'inscription" size="sm">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Note obtenue (/20)</label>
          <input v-model.number="resultatForm.note" type="number" min="0" max="20" class="input-field" />
        </div>
        <div class="flex items-center gap-3">
          <input v-model="resultatForm.certificat_obtenu" type="checkbox" id="cert" class="w-4 h-4 accent-primary-500" />
          <label for="cert" class="text-sm text-gray-700">Certificat obtenu</label>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Commentaire</label>
          <textarea v-model="resultatForm.commentaire" rows="2" class="input-field resize-none" />
        </div>
      </div>
      <template #footer>
        <button @click="modalResultatsOpen = false" class="btn-secondary">Annuler</button>
        <button @click="submitResultats" class="btn-primary">Enregistrer</button>
      </template>
    </AppModal>

  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import { useAuthStore } from '@/stores/auth'
import InfoRow              from '@/components/common/InfoRow.vue'
import AppModal             from '@/components/common/AppModal.vue'
import TypeBadge            from './TypeBadge.vue'
import StatutFormationBadge from './StatutFormationBadge.vue'
import { CheckIcon, XMarkIcon, PencilSquareIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  formation: { type: Object, required: true },
  inscrits:  { type: Array,  default: () => [] },
})
const emit = defineEmits(['valider-inscription', 'resultats-inscription'])

const authStore = useAuthStore()
const f = computed(() => props.formation)

const canManage = computed(() =>
  authStore.isAdmin || authStore.isRH || authStore.isManager
)

const modalResultatsOpen = ref(false)
const inscriptionSelectionnee = ref(null)
const resultatForm = reactive({ note: null, certificat_obtenu: false, commentaire: '' })

function formatDate(d) {
  return d ? new Date(d).toLocaleDateString('fr-FR') : '—'
}

function formatCout(v) {
  return Number(v).toLocaleString('fr-FR')
}

function openResultats(ins) {
  inscriptionSelectionnee.value    = ins
  resultatForm.note               = ins.note ?? null
  resultatForm.certificat_obtenu  = ins.certificat_obtenu
  resultatForm.commentaire        = ins.commentaire ?? ''
  modalResultatsOpen.value        = true
}

function submitResultats() {
  emit('resultats-inscription', {
    id:   inscriptionSelectionnee.value.id,
    data: { ...resultatForm },
  })
  modalResultatsOpen.value = false
}
</script>