<template>
  <div class="space-y-5">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h2 class="text-xl font-bold text-gray-800">Gestion des employés</h2>
        <p class="text-sm text-gray-500">{{ store.meta.total }} employé(s) enregistré(s)</p>
      </div>
      <button
        v-if="authStore.isAdmin || authStore.isRH"
        @click="openCreate"
        class="btn-primary flex items-center gap-2"
      >
        <PlusIcon class="w-4 h-4" /> Nouvel employé
      </button>
    </div>

    <!-- Filtres -->
    <div class="card">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <input
            v-model="store.filters.search"
            type="text"
            placeholder="Rechercher (nom, email, matricule)..."
            class="input-field"
            @input="debouncedFetch"
          />
        </div>
        <div>
          <select v-model="store.filters.departement_id" class="input-field" @change="fetchPage(1)">
            <option value="">Tous les départements</option>
            <option v-for="d in store.departements" :key="d.id" :value="d.id">{{ d.nom }}</option>
          </select>
        </div>
        <div>
          <select v-model="store.filters.statut" class="input-field" @change="fetchPage(1)">
            <option value="">Tous les statuts</option>
            <option value="actif">Actif</option>
            <option value="inactif">Inactif</option>
            <option value="suspendu">Suspendu</option>
          </select>
        </div>
        <div>
          <select v-model="store.filters.type_contrat" class="input-field" @change="fetchPage(1)">
            <option value="">Tous les contrats</option>
            <option value="CDI">CDI</option>
            <option value="CDD">CDD</option>
            <option value="Stage">Stage</option>
            <option value="Freelance">Freelance</option>
          </select>
        </div>
      </div>
      <div class="flex justify-end mt-3">
        <button @click="resetFilters" class="btn-secondary text-xs">
          Réinitialiser les filtres
        </button>
      </div>
    </div>

    <!-- Tableau -->
    <div class="card p-0 overflow-hidden">
      <AppTable :columns="columns" :data="store.employes" :loading="store.loading">

        <!-- Colonne Employé (avatar + nom + email) -->
        <template #cell-user.name="{ row }">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-primary-100 flex items-center justify-center shrink-0">
              <span class="text-xs font-bold text-primary-700">
                {{ initials(row.user?.name) }}
              </span>
            </div>
            <div>
              <p class="font-medium text-gray-800">{{ row.user?.name }}</p>
              <p class="text-xs text-gray-500">{{ row.user?.email }}</p>
            </div>
          </div>
        </template>

        <!-- Colonne Statut -->
        <template #cell-statut="{ value }">
          <AppBadge :value="value" type="statut" />
        </template>

        <!-- Colonne Contrat -->
        <template #cell-type_contrat="{ value }">
          <AppBadge :value="value" type="contrat" />
        </template>

        <!-- Colonne Département -->
        <template #cell-departement.nom="{ row }">
          {{ row.departement?.nom ?? '—' }}
        </template>

        <!-- Alerte fin de contrat -->
        <template #cell-fin_contrat="{ row }">
          <span v-if="row.fin_contrat" :class="row.contrat_expirant ? 'text-red-600 font-semibold' : 'text-gray-700'">
            {{ formatDate(row.fin_contrat) }}
            <span v-if="row.contrat_expirant" class="ml-1 text-xs">⚠️</span>
          </span>
          <span v-else class="text-gray-400">—</span>
        </template>

        <!-- Actions -->
        <template #actions="{ row }">
          <div class="flex justify-end gap-2">
            <router-link
              :to="`/employes/${row.id}`"
              class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition"
              title="Voir le détail"
            >
              <EyeIcon class="w-4 h-4" />
            </router-link>
            <button
              v-if="authStore.isAdmin || authStore.isRH"
              @click="openEdit(row)"
              class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg transition"
              title="Modifier"
            >
              <PencilIcon class="w-4 h-4" />
            </button>
            <button
              v-if="authStore.isAdmin"
              @click="confirmDelete(row)"
              class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition"
              title="Désactiver"
            >
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>
        </template>

      </AppTable>

      <!-- Pagination -->
      <div class="px-4 pb-4">
        <AppPagination :meta="store.meta" @change="fetchPage" />
      </div>
    </div>

    <!-- Modal Création / Édition -->
    <AppModal
      v-model="modalOpen"
      :title="isEdit ? 'Modifier l\'employé' : 'Ajouter un employé'"
      size="lg"
    >
      <EmployeForm
        ref="formRef"
        :initial="formInitial"
        :errors="formErrors"
        :departements="store.departements"
        :postes="store.postes"
        :is-edit="isEdit"
        @submit="handleSubmit"
      />
      <template #footer>
        <button @click="modalOpen = false" class="btn-secondary">Annuler</button>
        <button @click="submitForm" :disabled="submitting" class="btn-primary flex items-center gap-2">
          <span v-if="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
          {{ isEdit ? 'Enregistrer' : 'Créer l\'employé' }}
        </button>
      </template>
    </AppModal>

    <!-- Modal Confirmation suppression -->
    <AppModal v-model="deleteModal" title="Confirmation" size="sm">
      <p class="text-gray-600 text-sm">
        Voulez-vous vraiment désactiver
        <span class="font-semibold text-gray-800">{{ toDelete?.user?.name }}</span> ?
        Cette action peut être annulée.
      </p>
      <template #footer>
        <button @click="deleteModal = false" class="btn-secondary">Annuler</button>
        <button @click="handleDelete" :disabled="submitting" class="btn-danger">
          Désactiver
        </button>
      </template>
    </AppModal>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { useEmployesStore } from '@/stores/employes'
import { useAuthStore } from '@/stores/auth'
import { useDebounceFn } from '@/composables/useDebounceFn'

import AppTable      from '@/components/common/AppTable.vue'
import AppPagination from '@/components/common/AppPagination.vue'
import AppModal      from '@/components/common/AppModal.vue'
import AppBadge      from '@/components/common/AppBadge.vue'
import EmployeForm   from './EmployeForm.vue'

import {
  PlusIcon, EyeIcon, PencilIcon, TrashIcon,
} from '@heroicons/vue/24/outline'

const store     = useEmployesStore()
const authStore = useAuthStore()
const toast     = useToast()

// ── Table colonnes ────────────────────────────────────────────────────────────
const columns = [
  { key: 'user.name',       label: 'Employé' },
  { key: 'matricule',       label: 'Matricule' },
  { key: 'departement.nom', label: 'Département' },
  { key: 'type_contrat',    label: 'Contrat' },
  { key: 'statut',          label: 'Statut' },
  { key: 'fin_contrat',     label: 'Fin contrat' },
]

// ── Modal ─────────────────────────────────────────────────────────────────────
const modalOpen   = ref(false)
const isEdit      = ref(false)
const formRef     = ref(null)
const formInitial = ref({})
const formErrors  = ref({})
const submitting  = ref(false)

// ── Suppression ───────────────────────────────────────────────────────────────
const deleteModal = ref(false)
const toDelete    = ref(null)

// ── Helpers ───────────────────────────────────────────────────────────────────
function initials(name = '') {
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('fr-FR')
}

// ── Fetch ─────────────────────────────────────────────────────────────────────
async function fetchPage(page = 1) {
  await store.fetchEmployes(page)
}

const debouncedFetch = useDebounceFn(() => fetchPage(1), 400)

function resetFilters() {
  store.resetFilters()
  fetchPage(1)
}

// ── CRUD ──────────────────────────────────────────────────────────────────────
function openCreate() {
  isEdit.value      = false
  formInitial.value = {}
  formErrors.value  = {}
  modalOpen.value   = true
}

function openEdit(row) {
  isEdit.value = true
  formErrors.value = {}
  formInitial.value = {
    name:           row.user?.name,
    email:          row.user?.email,
    phone:          row.user?.phone,
    date_naissance: row.user?.date_naissance,
    genre:          row.user?.genre,
    adresse:        row.user?.adresse,
    departement_id: row.departement?.id,
    poste_id:       row.poste?.id,
    date_embauche:  row.date_embauche,
    type_contrat:   row.type_contrat,
    fin_contrat:    row.fin_contrat,
    salaire_base:   row.salaire_base,
    conge_solde:    row.conge_solde,
    statut:         row.statut,
    notes:          row.notes,
    _id:            row.id,
  }
  modalOpen.value = true
}

function submitForm() {
  // Déclencher le submit du formulaire enfant via la ref
  formRef.value?.$el?.dispatchEvent(new Event('submit', { bubbles: true }))
}

async function handleSubmit(data) {
  submitting.value = true
  formErrors.value = {}
  try {
    if (isEdit.value) {
      await store.updateEmploye(data._id, data)
      toast.success('Employé mis à jour avec succès !')
    } else {
      const res = await store.createEmploye(data)
      toast.success(`Employé créé ! Mot de passe temporaire : ${res.password_temp}`)
    }
    modalOpen.value = false
  } catch (err) {
    const errors = err.response?.data?.errors
    if (errors) formErrors.value = Object.fromEntries(
      Object.entries(errors).map(([k, v]) => [k, v[0]])
    )
    toast.error(err.response?.data?.message || 'Une erreur est survenue.')
  } finally {
    submitting.value = false
  }
}

function confirmDelete(row) {
  toDelete.value    = row
  deleteModal.value = true
}

async function handleDelete() {
  submitting.value = true
  try {
    await store.deleteEmploye(toDelete.value.id)
    toast.success('Employé désactivé.')
    deleteModal.value = false
  } catch {
    toast.error('Erreur lors de la désactivation.')
  } finally {
    submitting.value = false
  }
}

// ── Init ──────────────────────────────────────────────────────────────────────
onMounted(async () => {
  await Promise.all([
    store.fetchEmployes(),
    store.fetchDepartements(),
    store.fetchPostes(),
  ])
})
</script>