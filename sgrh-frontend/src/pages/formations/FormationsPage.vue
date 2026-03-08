<template>
  <div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h2 class="text-xl font-bold text-gray-800">Catalogue des formations</h2>
        <p class="text-sm text-gray-500">{{ store.meta.total }} formation(s) disponible(s)</p>
      </div>
      <button
        v-if="authStore.isRH || authStore.isAdmin"
        @click="openCreer"
        class="btn-primary flex items-center gap-2"
      >
        <PlusIcon class="w-4 h-4" /> Créer une formation
      </button>
    </div>

    <!-- Mes formations (onglet employé) -->
    <div v-if="mesFormations.length" class="space-y-2">
      <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Mes inscriptions</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="ins in mesFormations"
          :key="ins.id"
          class="card flex items-center justify-between gap-3"
        >
          <div>
            <p class="font-medium text-sm text-gray-800">{{ ins.formation?.titre }}</p>
            <p class="text-xs text-gray-500 mt-0.5">
              {{ formatDate(ins.formation?.date_debut) }} → {{ formatDate(ins.formation?.date_fin) }}
            </p>
            <p v-if="ins.note !== null" class="text-xs font-semibold text-primary-600 mt-1">
              Note : {{ ins.note }}/20
              <span v-if="ins.certificat_obtenu">🏅 Certificat</span>
            </p>
          </div>
          <StatutFormationBadge :statut="ins.statut" />
        </div>
      </div>
    </div>

    <!-- Filtres -->
    <div class="card">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <input
          v-model="store.filters.search"
          type="text"
          placeholder="Rechercher une formation..."
          class="input-field"
          @input="debouncedFetch"
        />
        <select v-model="store.filters.type" class="input-field" @change="fetchPage(1)">
          <option value="">Tous les types</option>
          <option value="interne">Interne</option>
          <option value="externe">Externe</option>
          <option value="elearning">E-Learning</option>
        </select>
        <select v-model="store.filters.statut" class="input-field" @change="fetchPage(1)">
          <option value="">Tous les statuts</option>
          <option value="planifiee">Planifiée</option>
          <option value="en_cours">En cours</option>
          <option value="terminee">Terminée</option>
        </select>
      </div>
    </div>

    <!-- Catalogue (grille de cards) -->
    <div v-if="store.loading" class="flex justify-center py-12">
      <div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" />
    </div>
    <div v-else-if="store.formations.length" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
      <FormationCard
        v-for="f in store.formations"
        :key="f.id"
        :formation="f"
        @inscrire="handleInscrire"
        @desinscrire="handleDesinscrire"
        @voir="openDetail"
        @modifier="openModifier"
      />
    </div>
    <div v-else class="text-center py-16 text-gray-400">
      Aucune formation trouvée.
    </div>

    <!-- Pagination -->
    <AppPagination :meta="store.meta" @change="fetchPage" />

    <!-- ── MODALS ──────────────────────────────────────────────────────────── -->

    <!-- Créer/Modifier -->
    <AppModal
      v-model="modalFormOpen"
      :title="isEdit ? 'Modifier la formation' : 'Créer une formation'"
      size="lg"
    >
      <FormationForm
        ref="formRef"
        :initial="formInitial"
        :errors="formErrors"
        @submit="handleSubmitForm"
      />
      <template #footer>
        <button @click="modalFormOpen = false" class="btn-secondary">Annuler</button>
        <button @click="submitForm" :disabled="submitting" class="btn-primary flex items-center gap-2">
          <span v-if="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
          {{ isEdit ? 'Enregistrer' : 'Créer la formation' }}
        </button>
      </template>
    </AppModal>

    <!-- Détail + liste inscrits (RH/Admin) -->
    <AppModal v-model="modalDetailOpen" title="Détail de la formation" size="lg">
      <FormationDetail
        v-if="selected"
        :formation="selected"
        :inscrits="store.inscrits"
        @valider-inscription="handleValiderInscription"
        @resultats-inscription="handleResultatsInscription"
      />
    </AppModal>

    <!-- Confirmation inscription -->
    <AppModal v-model="modalInscrireOpen" title="Confirmer l'inscription" size="sm">
      <p class="text-sm text-gray-600">
        Voulez-vous vous inscrire à la formation
        <strong class="text-gray-800">{{ selected?.titre }}</strong> ?
      </p>
      <template #footer>
        <button @click="modalInscrireOpen = false" class="btn-secondary">Annuler</button>
        <button @click="confirmerInscription" :disabled="submitting" class="btn-primary">
          Confirmer l'inscription
        </button>
      </template>
    </AppModal>

    <!-- Confirmation désinscription -->
    <AppModal v-model="modalDesinscrireOpen" title="Se désinscrire" size="sm">
      <p class="text-sm text-gray-600">
        Confirmer la désinscription de
        <strong>{{ selected?.titre }}</strong> ?
      </p>
      <template #footer>
        <button @click="modalDesinscrireOpen = false" class="btn-secondary">Non</button>
        <button @click="confirmerDesinscription" :disabled="submitting" class="btn-danger">
          Se désinscrire
        </button>
      </template>
    </AppModal>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { useFormationsStore } from '@/stores/formations'
import { useAuthStore }       from '@/stores/auth'
import { useDebounceFn }      from '@/composables/useDebounceFn'

import AppPagination        from '@/components/common/AppPagination.vue'
import AppModal             from '@/components/common/AppModal.vue'
import FormationCard        from './FormationCard.vue'
import FormationForm        from './FormationForm.vue'
import FormationDetail      from './FormationDetail.vue'
import StatutFormationBadge from './StatutFormationBadge.vue'

import { PlusIcon } from '@heroicons/vue/24/outline'

const store     = useFormationsStore()
const authStore = useAuthStore()
const toast     = useToast()

// ── Données ───────────────────────────────────────────────────────────────────
const mesFormations = ref([])

// ── Modals ────────────────────────────────────────────────────────────────────
const modalFormOpen       = ref(false)
const modalDetailOpen     = ref(false)
const modalInscrireOpen   = ref(false)
const modalDesinscrireOpen= ref(false)
const isEdit              = ref(false)
const selected            = ref(null)
const formRef             = ref(null)
const formInitial         = ref({})
const formErrors          = ref({})
const submitting          = ref(false)

// ── Helpers ───────────────────────────────────────────────────────────────────
function formatDate(d) {
  return d ? new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'
}

// ── Fetch ─────────────────────────────────────────────────────────────────────
async function fetchPage(page = 1) {
  await store.fetchFormations(page)
}

const debouncedFetch = useDebounceFn(() => fetchPage(1), 400)

// ── CRUD Formation ────────────────────────────────────────────────────────────
function openCreer() {
  isEdit.value      = false
  formInitial.value = {}
  formErrors.value  = {}
  modalFormOpen.value = true
}

function openModifier(f) {
  isEdit.value      = true
  formInitial.value = { ...f }
  formErrors.value  = {}
  selected.value    = f
  modalFormOpen.value = true
}

function submitForm() {
  formRef.value?.$el?.dispatchEvent(new Event('submit', { bubbles: true }))
}

async function handleSubmitForm(data) {
  submitting.value = true
  formErrors.value = {}
  try {
    if (isEdit.value) {
      await store.updateFormation(selected.value.id, data)
      toast.success('Formation mise à jour !')
    } else {
      await store.createFormation(data)
      toast.success('Formation créée avec succès !')
    }
    modalFormOpen.value = false
  } catch (err) {
    const errors = err.response?.data?.errors
    if (errors) formErrors.value = Object.fromEntries(
      Object.entries(errors).map(([k, v]) => [k, v[0]])
    )
    toast.error(err.response?.data?.message || 'Erreur.')
  } finally {
    submitting.value = false
  }
}

// ── Détail ────────────────────────────────────────────────────────────────────
async function openDetail(f) {
  selected.value    = f
  modalDetailOpen.value = true
  if (authStore.isRH || authStore.isAdmin || authStore.isManager) {
    await store.fetchInscrits(f.id)
  }
}

// ── Inscription ───────────────────────────────────────────────────────────────
function handleInscrire(f) {
  selected.value        = f
  modalInscrireOpen.value = true
}

async function confirmerInscription() {
  submitting.value = true
  try {
    await store.inscrire(selected.value.id)
    await store.fetchMesFormations().then(d => { mesFormations.value = store.mesFormations })
    toast.success('Inscription soumise avec succès !')
    modalInscrireOpen.value = false
  } catch (err) {
    toast.error(err.response?.data?.message || 'Erreur lors de l\'inscription.')
  } finally {
    submitting.value = false
  }
}

function handleDesinscrire(f) {
  selected.value           = f
  modalDesinscrireOpen.value = true
}

async function confirmerDesinscription() {
  submitting.value = true
  try {
    await store.desinscrire(selected.value.id)
    await store.fetchMesFormations().then(() => { mesFormations.value = store.mesFormations })
    toast.success('Désinscription effectuée.')
    modalDesinscrireOpen.value = false
  } catch (err) {
    toast.error(err.response?.data?.message || 'Erreur.')
  } finally {
    submitting.value = false
  }
}

// ── Gestion inscrits (RH) ─────────────────────────────────────────────────────
async function handleValiderInscription({ id, action }) {
  try {
    await store.validerInscription(id, { action })
    toast.success(action === 'valider' ? 'Inscription validée !' : 'Inscription refusée.')
  } catch (err) {
    toast.error(err.response?.data?.message || 'Erreur.')
  }
}

async function handleResultatsInscription({ id, data }) {
  try {
    await store.resultatsInscription(id, data)
    toast.success('Résultats enregistrés !')
  } catch (err) {
    toast.error(err.response?.data?.message || 'Erreur.')
  }
}

// ── Init ──────────────────────────────────────────────────────────────────────
onMounted(async () => {
  await store.fetchFormations()
  await store.fetchMesFormations()
  mesFormations.value = store.mesFormations
})
</script>