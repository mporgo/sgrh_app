<template>
  <div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h2 class="text-xl font-bold text-gray-800">Congés & Absences</h2>
        <p class="text-sm text-gray-500">Gérez vos demandes et suivez vos soldes</p>
      </div>
      <button @click="modalDemandeOpen = true" class="btn-primary flex items-center gap-2">
        <PlusIcon class="w-4 h-4" /> Nouvelle demande
      </button>
    </div>

    <!-- Soldes de congés -->
    <div>
      <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Mes soldes</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
        <SoldeCard v-for="s in store.soldes" :key="s.type_conge_id" :solde="s" />
      </div>
    </div>

    <!-- Onglets -->
    <div class="border-b border-gray-200">
      <nav class="flex gap-6">
        <button
          v-for="tab in tabs"
          :key="tab.value"
          @click="activeTab = tab.value"
          :class="[
            'pb-3 text-sm font-medium border-b-2 transition',
            activeTab === tab.value
              ? 'border-primary-500 text-primary-600'
              : 'border-transparent text-gray-500 hover:text-gray-700'
          ]"
        >
          {{ tab.label }}
          <span
            v-if="tab.count !== undefined"
            class="ml-1.5 px-1.5 py-0.5 rounded-full text-xs"
            :class="tab.value === 'en_attente' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600'"
          >
            {{ tab.count }}
          </span>
        </button>
      </nav>
    </div>

    <!-- Tableau des demandes -->
    <div class="card p-0 overflow-hidden">
      <AppTable :columns="columns" :data="store.conges" :loading="store.loading">

        <!-- Employé -->
        <template #cell-employe="{ row }">
          <div>
            <p class="font-medium text-gray-800">{{ row.employe?.user?.name }}</p>
            <p class="text-xs text-gray-500">{{ row.employe?.departement }}</p>
          </div>
        </template>

        <!-- Type de congé -->
        <template #cell-type_conge="{ row }">
          <div class="flex items-center gap-2">
            <span
              class="w-2.5 h-2.5 rounded-full shrink-0"
              :style="{ backgroundColor: row.type_conge?.couleur }"
            />
            <span class="text-sm text-gray-700">{{ row.type_conge?.libelle }}</span>
          </div>
        </template>

        <!-- Période -->
        <template #cell-periode="{ row }">
          <div class="text-sm">
            <p class="text-gray-700">{{ formatDate(row.date_debut) }} → {{ formatDate(row.date_fin) }}</p>
            <p class="text-xs text-gray-400">{{ row.nombre_jours }} jour(s)</p>
          </div>
        </template>

        <!-- Statut -->
        <template #cell-statut="{ value }">
          <StatutBadge :statut="value" />
        </template>

        <!-- Actions -->
        <template #actions="{ row }">
          <div class="flex justify-end gap-2">
            <!-- Traiter (manager/RH) -->
            <button
              v-if="row.statut === 'en_attente' && (authStore.isRH || authStore.isManager || authStore.isAdmin)"
              @click="openTraiter(row)"
              class="p-1.5 text-primary-600 hover:bg-primary-50 rounded-lg transition"
              title="Traiter"
            >
              <CheckCircleIcon class="w-4 h-4" />
            </button>
            <!-- Annuler (employé) -->
            <button
              v-if="row.statut === 'en_attente' && isMyConge(row)"
              @click="confirmAnnuler(row)"
              class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition"
              title="Annuler"
            >
              <XCircleIcon class="w-4 h-4" />
            </button>
          </div>
        </template>

      </AppTable>

      <div class="px-4 pb-4">
        <AppPagination :meta="store.meta" @change="fetchPage" />
      </div>
    </div>

    <!-- Modal Nouvelle demande -->
    <AppModal v-model="modalDemandeOpen" title="Nouvelle demande de congé" size="md">
      <CongeForm
        ref="congeFormRef"
        :type-conges="store.typeConges"
        :errors="formErrors"
        @submit="handleCreateConge"
      />
      <template #footer>
        <button @click="modalDemandeOpen = false" class="btn-secondary">Annuler</button>
        <button @click="submitCongeForm" :disabled="submitting" class="btn-primary flex items-center gap-2">
          <span v-if="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
          Soumettre la demande
        </button>
      </template>
    </AppModal>

    <!-- Modal Traiter -->
    <TraiterCongeModal
      v-model="modalTraiterOpen"
      :conge="congeSelectionne"
      :loading="submitting"
      @traiter="handleTraiter"
    />

    <!-- Modal Annulation -->
    <AppModal v-model="modalAnnulerOpen" title="Confirmer l'annulation" size="sm">
      <p class="text-sm text-gray-600">
        Voulez-vous annuler votre demande de
        <strong>{{ congeSelectionne?.type_conge?.libelle }}</strong>
        du {{ formatDate(congeSelectionne?.date_debut) }} au {{ formatDate(congeSelectionne?.date_fin) }} ?
      </p>
      <template #footer>
        <button @click="modalAnnulerOpen = false" class="btn-secondary">Non</button>
        <button @click="handleAnnuler" :disabled="submitting" class="btn-danger">Oui, annuler</button>
      </template>
    </AppModal>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { useCongesStore } from '@/stores/conges'
import { useAuthStore }   from '@/stores/auth'

import AppTable          from '@/components/common/AppTable.vue'
import AppPagination     from '@/components/common/AppPagination.vue'
import AppModal          from '@/components/common/AppModal.vue'
import SoldeCard         from './SoldeCard.vue'
import CongeForm         from './CongeForm.vue'
import TraiterCongeModal from './TraiterCongeModal.vue'

import { PlusIcon, CheckCircleIcon, XCircleIcon } from '@heroicons/vue/24/outline'

const store     = useCongesStore()
const authStore = useAuthStore()
const toast     = useToast()

// ── Statut badge inline ───────────────────────────────────────────────────────
const StatutBadge = {
  props: ['statut'],
  template: `
    <span :class="cls">{{ label }}</span>
  `,
  computed: {
    cls() {
      return {
        en_attente: 'badge-warning',
        valide:     'badge-success',
        refuse:     'badge-danger',
        annule:     'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500',
      }[this.statut] ?? 'badge-info'
    },
    label() {
      return { en_attente: 'En attente', valide: 'Validé', refuse: 'Refusé', annule: 'Annulé' }[this.statut]
    }
  }
}

// ── Tabs ──────────────────────────────────────────────────────────────────────
const activeTab = ref('')

const tabs = computed(() => [
  { label: 'Toutes',      value: '' },
  { label: 'En attente',  value: 'en_attente', count: store.conges.filter(c => c.statut === 'en_attente').length },
  { label: 'Validées',    value: 'valide' },
  { label: 'Refusées',    value: 'refuse' },
])

// ── Colonnes table ────────────────────────────────────────────────────────────
const showEmployeCol = computed(() =>
  authStore.isRH || authStore.isManager || authStore.isAdmin
)

const columns = computed(() => {
  const cols = []
  if (showEmployeCol.value) cols.push({ key: 'employe', label: 'Employé' })
  cols.push(
    { key: 'type_conge', label: 'Type' },
    { key: 'periode',    label: 'Période' },
    { key: 'statut',     label: 'Statut' },
  )
  return cols
})

// ── Modals ────────────────────────────────────────────────────────────────────
const modalDemandeOpen = ref(false)
const modalTraiterOpen = ref(false)
const modalAnnulerOpen = ref(false)
const congeSelectionne = ref(null)
const congeFormRef     = ref(null)
const formErrors       = ref({})
const submitting       = ref(false)

// ── Helpers ───────────────────────────────────────────────────────────────────
function formatDate(d) {
  return d ? new Date(d).toLocaleDateString('fr-FR') : '—'
}

function isMyConge(row) {
  return row.employe?.user?.id === authStore.user?.id
}

// ── Fetch ─────────────────────────────────────────────────────────────────────
async function fetchPage(page = 1) {
  store.filters.statut = activeTab.value
  await store.fetchConges(page)
}

// ── CRUD ──────────────────────────────────────────────────────────────────────
function submitCongeForm() {
  congeFormRef.value?.$el?.dispatchEvent(new Event('submit', { bubbles: true }))
}

async function handleCreateConge(data) {
  submitting.value = true
  formErrors.value = {}
  try {
    await store.createConge(data)
    toast.success('Demande soumise avec succès !')
    modalDemandeOpen.value = false
  } catch (err) {
    const errors = err.response?.data?.errors
    if (errors) formErrors.value = Object.fromEntries(
      Object.entries(errors).map(([k, v]) => [k, v[0]])
    )
    toast.error(err.response?.data?.message || 'Erreur lors de la soumission.')
  } finally {
    submitting.value = false
  }
}

function openTraiter(row) {
  congeSelectionne.value = row
  modalTraiterOpen.value = true
}

async function handleTraiter(payload) {
  submitting.value = true
  try {
    await store.traiterConge(congeSelectionne.value.id, payload)
    toast.success(payload.action === 'valider' ? 'Demande validée !' : 'Demande refusée.')
    modalTraiterOpen.value = false
  } catch (err) {
    toast.error(err.response?.data?.message || 'Erreur lors du traitement.')
  } finally {
    submitting.value = false
  }
}

function confirmAnnuler(row) {
  congeSelectionne.value = row
  modalAnnulerOpen.value = true
}

async function handleAnnuler() {
  submitting.value = true
  try {
    await store.annulerConge(congeSelectionne.value.id)
    toast.success('Demande annulée.')
    modalAnnulerOpen.value = false
  } catch {
    toast.error('Erreur lors de l\'annulation.')
  } finally {
    submitting.value = false
  }
}

// ── Init ──────────────────────────────────────────────────────────────────────
onMounted(async () => {
  await Promise.all([
    store.fetchConges(),
    store.fetchSoldes(),
    store.fetchTypeConges(),
  ])
})
</script>