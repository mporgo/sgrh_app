<template>
  <div class="space-y-5">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h2 class="text-xl font-bold text-gray-800">Évaluations de performance</h2>
        <p class="text-sm text-gray-500">{{ store.meta.total }} évaluation(s)</p>
      </div>
      <button
        v-if="authStore.isManager || authStore.isRH || authStore.isAdmin"
        @click="openPlanifier"
        class="btn-primary flex items-center gap-2"
      >
        <PlusIcon class="w-4 h-4" /> Planifier une évaluation
      </button>
    </div>

    <!-- Stats rapides (RH/Admin) -->
    <div v-if="authStore.isRH || authStore.isAdmin" class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <div class="card text-center">
        <p class="text-2xl font-bold text-gray-800">{{ store.stats.total ?? '—' }}</p>
        <p class="text-xs text-gray-500 mt-1">Total {{ anneeActuelle }}</p>
      </div>
      <div class="card text-center">
        <p class="text-2xl font-bold text-yellow-600">{{ store.stats.planifiees ?? '—' }}</p>
        <p class="text-xs text-gray-500 mt-1">Planifiées</p>
      </div>
      <div class="card text-center">
        <p class="text-2xl font-bold text-green-600">{{ store.stats.terminees ?? '—' }}</p>
        <p class="text-xs text-gray-500 mt-1">Terminées</p>
      </div>
      <div class="card text-center">
        <p class="text-2xl font-bold text-primary-600">
          {{ tauxCompletion }}%
        </p>
        <p class="text-xs text-gray-500 mt-1">Taux de complétion</p>
      </div>
    </div>

    <!-- Filtres -->
    <div class="card">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <select v-model="store.filters.statut" class="input-field" @change="fetchPage(1)">
          <option value="">Tous les statuts</option>
          <option value="planifiee">Planifiée</option>
          <option value="en_cours">En cours</option>
          <option value="terminee">Terminée</option>
          <option value="annulee">Annulée</option>
        </select>
        <select v-model="store.filters.type" class="input-field" @change="fetchPage(1)">
          <option value="">Tous les types</option>
          <option value="annuelle">Annuelle</option>
          <option value="semestrielle">Semestrielle</option>
          <option value="periode_essai">Période d'essai</option>
          <option value="autre">Autre</option>
        </select>
        <select v-model="store.filters.annee" class="input-field" @change="fetchPage(1)">
          <option v-for="y in annees" :key="y" :value="y">{{ y }}</option>
        </select>
      </div>
    </div>

    <!-- Tableau -->
    <div class="card p-0 overflow-hidden">
      <AppTable :columns="columns" :data="store.evaluations" :loading="store.loading">

        <!-- Employé -->
        <template #cell-employe="{ row }">
          <div>
            <p class="font-medium text-gray-800">{{ row.employe?.user?.name }}</p>
            <p class="text-xs text-gray-500">{{ row.employe?.poste }} · {{ row.employe?.departement }}</p>
          </div>
        </template>

        <!-- Type -->
        <template #cell-type_label="{ value }">
          <span class="text-sm text-gray-700">{{ value }}</span>
        </template>

        <!-- Date -->
        <template #cell-date_evaluation="{ value }">
          <span class="text-sm text-gray-700">{{ formatDate(value) }}</span>
        </template>

        <!-- Note -->
        <template #cell-note_globale="{ row }">
          <NoteGlobale :note="row.note_globale" />
        </template>

        <!-- Statut -->
        <template #cell-statut="{ value }">
          <StatutBadgeComp :statut="value" />
        </template>

        <!-- Évaluateur -->
        <template #cell-evaluateur="{ row }">
          <span class="text-sm text-gray-600">{{ row.evaluateur?.name ?? '—' }}</span>
        </template>

        <!-- Actions -->
        <template #actions="{ row }">
          <div class="flex justify-end gap-2">
            <button
              @click="openDetail(row)"
              class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition"
              title="Voir le détail"
            >
              <EyeIcon class="w-4 h-4" />
            </button>
            <button
              v-if="canRenseigner(row)"
              @click="openResultat(row)"
              class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition"
              title="Renseigner les résultats"
            >
              <PencilSquareIcon class="w-4 h-4" />
            </button>
            <button
              v-if="row.statut !== 'terminee' && row.statut !== 'annulee' && (authStore.isRH || authStore.isAdmin)"
              @click="confirmDelete(row)"
              class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition"
              title="Annuler"
            >
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>
        </template>

      </AppTable>
      <div class="px-4 pb-4">
        <AppPagination :meta="store.meta" @change="fetchPage" />
      </div>
    </div>

    <!-- ── MODALS ──────────────────────────────────────────────────────────── -->

    <!-- Planifier -->
    <AppModal v-model="modalPlanifierOpen" title="Planifier une évaluation" size="md">
      <EvaluationPlanifierForm
        ref="planifierFormRef"
        :employes="employes"
        :errors="formErrors"
        @submit="handlePlanifier"
      />
      <template #footer>
        <button @click="modalPlanifierOpen = false" class="btn-secondary">Annuler</button>
        <button @click="submitPlanifier" :disabled="submitting" class="btn-primary flex items-center gap-2">
          <span v-if="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
          Planifier
        </button>
      </template>
    </AppModal>

    <!-- Renseigner résultats -->
    <AppModal v-model="modalResultatOpen" title="Renseigner les résultats" size="lg">
      <div v-if="selected" class="mb-4 p-3 bg-gray-50 rounded-lg text-sm flex justify-between">
        <span class="text-gray-600">Employé : <strong class="text-gray-800">{{ selected.employe?.user?.name }}</strong></span>
        <span class="text-gray-600">Date : <strong>{{ formatDate(selected.date_evaluation) }}</strong></span>
      </div>
      <EvaluationResultatForm
        ref="resultatFormRef"
        :initial="{ objectifs_atteints: selected?.objectifs_fixes }"
        :errors="formErrors"
        @submit="handleResultat"
      />
      <template #footer>
        <button @click="modalResultatOpen = false" class="btn-secondary">Annuler</button>
        <button @click="submitResultat" :disabled="submitting" class="btn-primary flex items-center gap-2">
          <span v-if="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
          Enregistrer les résultats
        </button>
      </template>
    </AppModal>

    <!-- Détail -->
    <AppModal v-model="modalDetailOpen" title="Détail de l'évaluation" size="lg">
      <EvaluationDetail
        v-if="selected"
        :evaluation="selected"
        @commenter="handleCommenterEmploye"
      />
    </AppModal>

    <!-- Supprimer -->
    <AppModal v-model="modalDeleteOpen" title="Annuler l'évaluation" size="sm">
      <p class="text-sm text-gray-600">
        Confirmer l'annulation de l'évaluation de
        <strong>{{ selected?.employe?.user?.name }}</strong>
        prévue le {{ formatDate(selected?.date_evaluation) }} ?
      </p>
      <template #footer>
        <button @click="modalDeleteOpen = false" class="btn-secondary">Non</button>
        <button @click="handleDelete" :disabled="submitting" class="btn-danger">Oui, annuler</button>
      </template>
    </AppModal>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { useEvaluationsStore } from '@/stores/evaluations'
import { useAuthStore }        from '@/stores/auth'
import { employesApi }         from '@/api/employes'

import AppTable               from '@/components/common/AppTable.vue'
import AppPagination          from '@/components/common/AppPagination.vue'
import AppModal               from '@/components/common/AppModal.vue'
import NoteGlobale            from './NoteGlobale.vue'
import StatutBadgeComp        from './StatutBadge.vue'
import EvaluationPlanifierForm from './EvaluationPlanifierForm.vue'
import EvaluationResultatForm  from './EvaluationResultatForm.vue'
import EvaluationDetail        from './EvaluationDetail.vue'

import { PlusIcon, EyeIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline'

const store     = useEvaluationsStore()
const authStore = useAuthStore()
const toast     = useToast()

// ── Données ───────────────────────────────────────────────────────────────────
const employes      = ref([])
const anneeActuelle = new Date().getFullYear()
const annees        = Array.from({ length: 5 }, (_, i) => anneeActuelle - i)

// ── Computed ──────────────────────────────────────────────────────────────────
const tauxCompletion = computed(() => {
  if (!store.stats.total) return 0
  return Math.round((store.stats.terminees / store.stats.total) * 100)
})

// ── Colonnes table ────────────────────────────────────────────────────────────
const columns = [
  { key: 'employe',        label: 'Employé' },
  { key: 'type_label',     label: 'Type' },
  { key: 'date_evaluation',label: 'Date' },
  { key: 'evaluateur',     label: 'Évaluateur' },
  { key: 'note_globale',   label: 'Note' },
  { key: 'statut',         label: 'Statut' },
]

// ── Modals ────────────────────────────────────────────────────────────────────
const modalPlanifierOpen = ref(false)
const modalResultatOpen  = ref(false)
const modalDetailOpen    = ref(false)
const modalDeleteOpen    = ref(false)
const selected           = ref(null)
const planifierFormRef   = ref(null)
const resultatFormRef    = ref(null)
const formErrors         = ref({})
const submitting         = ref(false)

// ── Helpers ───────────────────────────────────────────────────────────────────
function formatDate(d) {
  return d ? new Date(d).toLocaleDateString('fr-FR') : '—'
}

function canRenseigner(row) {
  if (!['planifiee', 'en_cours'].includes(row.statut)) return false
  return authStore.isManager || authStore.isRH || authStore.isAdmin
}

// ── Fetch ─────────────────────────────────────────────────────────────────────
async function fetchPage(page = 1) {
  await store.fetchEvaluations(page)
}

// ── CRUD ──────────────────────────────────────────────────────────────────────
function openPlanifier() {
  formErrors.value       = {}
  modalPlanifierOpen.value = true
}

function submitPlanifier() {
  planifierFormRef.value?.$el?.dispatchEvent(new Event('submit', { bubbles: true }))
}

async function handlePlanifier(data) {
  submitting.value = true
  formErrors.value = {}
  try {
    await store.createEvaluation(data)
    toast.success('Évaluation planifiée avec succès !')
    modalPlanifierOpen.value = false
  } catch (err) {
    const errors = err.response?.data?.errors
    if (errors) formErrors.value = Object.fromEntries(
      Object.entries(errors).map(([k, v]) => [k, v[0]])
    )
    toast.error(err.response?.data?.message || 'Erreur lors de la planification.')
  } finally {
    submitting.value = false
  }
}

function openResultat(row) {
  selected.value         = row
  formErrors.value       = {}
  modalResultatOpen.value = true
}

function submitResultat() {
  resultatFormRef.value?.$el?.dispatchEvent(new Event('submit', { bubbles: true }))
}

async function handleResultat(data) {
  submitting.value = true
  formErrors.value = {}
  try {
    await store.updateEvaluation(selected.value.id, data)
    toast.success('Résultats enregistrés !')
    modalResultatOpen.value = false
  } catch (err) {
    const errors = err.response?.data?.errors
    if (errors) formErrors.value = Object.fromEntries(
      Object.entries(errors).map(([k, v]) => [k, v[0]])
    )
    toast.error(err.response?.data?.message || 'Erreur lors de l\'enregistrement.')
  } finally {
    submitting.value = false
  }
}

function openDetail(row) {
  selected.value      = row
  modalDetailOpen.value = true
}

async function handleCommenterEmploye(commentaire) {
  try {
    await store.commenterEmploye(selected.value.id, { commentaire_employe: commentaire })
    selected.value = store.evaluation
    toast.success('Commentaire enregistré.')
  } catch (err) {
    toast.error(err.response?.data?.message || 'Erreur.')
  }
}

function confirmDelete(row) {
  selected.value      = row
  modalDeleteOpen.value = true
}

async function handleDelete() {
  submitting.value = true
  try {
    await store.deleteEvaluation(selected.value.id)
    toast.success('Évaluation annulée.')
    modalDeleteOpen.value = false
  } catch (err) {
    toast.error(err.response?.data?.message || 'Erreur.')
  } finally {
    submitting.value = false
  }
}

// ── Init ──────────────────────────────────────────────────────────────────────
onMounted(async () => {
  await Promise.all([
    store.fetchEvaluations(),
    store.fetchStats(anneeActuelle),
  ])
  const { data } = await employesApi.getAll({ per_page: 100, statut: 'actif' })
  employes.value = data.data
})
</script>