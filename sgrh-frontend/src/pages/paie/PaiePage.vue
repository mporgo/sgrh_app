<template>
  <div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h2 class="text-xl font-bold text-gray-800">Gestion de la paie</h2>
        <p class="text-sm text-gray-500">{{ periodeLabel }}</p>
      </div>
      <button
        v-if="authStore.isRH || authStore.isAdmin"
        @click="openGenerer"
        class="btn-primary flex items-center gap-2"
      >
        <PlusIcon class="w-4 h-4" /> Générer un bulletin
      </button>
    </div>

    <!-- Stats du mois -->
    <div v-if="authStore.isRH || authStore.isAdmin" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">

      <div class="card text-center col-span-2 sm:col-span-1 lg:col-span-2">
        <p class="text-2xl font-bold text-gray-800">{{ fmt(store.stats.masse_salariale) }}</p>
        <p class="text-xs text-gray-500 mt-1">Masse salariale payée</p>
      </div>

      <div class="card text-center">
        <p class="text-2xl font-bold text-gray-800">{{ store.stats.total_bulletins ?? '—' }}</p>
        <p class="text-xs text-gray-500 mt-1">Bulletins générés</p>
      </div>
      <div class="card text-center">
        <p class="text-2xl font-bold text-yellow-600">{{ store.stats.brouillons ?? '—' }}</p>
        <p class="text-xs text-gray-500 mt-1">Brouillons</p>
      </div>
      <div class="card text-center">
        <p class="text-2xl font-bold text-blue-600">{{ store.stats.valides ?? '—' }}</p>
        <p class="text-xs text-gray-500 mt-1">Validés</p>
      </div>
      <div class="card text-center">
        <p class="text-2xl font-bold text-green-600">{{ store.stats.payes ?? '—' }}</p>
        <p class="text-xs text-gray-500 mt-1">Payés</p>
      </div>

    </div>

    <!-- Filtres -->
    <div class="card">
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <select v-model.number="store.filters.mois" class="input-field" @change="fetchPage(1)">
          <option value="">Tous les mois</option>
          <option v-for="(label, val) in moisOptions" :key="val" :value="parseInt(val)">{{ label }}</option>
        </select>
        <select v-model.number="store.filters.annee" class="input-field" @change="fetchPage(1)">
          <option v-for="y in annees" :key="y" :value="y">{{ y }}</option>
        </select>
        <select v-model="store.filters.statut" class="input-field" @change="fetchPage(1)">
          <option value="">Tous les statuts</option>
          <option value="brouillon">Brouillon</option>
          <option value="valide">Validé</option>
          <option value="paye">Payé</option>
        </select>
        <button @click="resetFiltres" class="btn-secondary text-sm">Réinitialiser</button>
      </div>
    </div>

    <!-- Tableau bulletins -->
    <div class="card p-0 overflow-hidden">
      <AppTable :columns="columns" :data="store.paies" :loading="store.loading">

        <!-- Employé -->
        <template #cell-employe="{ row }">
          <div>
            <p class="font-medium text-gray-800">{{ row.employe?.user?.name }}</p>
            <p class="text-xs text-gray-400">{{ row.employe?.matricule }}</p>
          </div>
        </template>

        <!-- Période -->
        <template #cell-periode="{ value }">
          <span class="text-sm text-gray-700">{{ value }}</span>
        </template>

        <!-- Net à payer -->
        <template #cell-net_a_payer="{ value }">
          <span class="font-semibold text-gray-800">{{ fmt(value) }}</span>
        </template>

        <!-- Statut -->
        <template #cell-statut="{ value }">
          <StatutPaieBadge :statut="value" />
        </template>

        <!-- Référence -->
        <template #cell-reference="{ value }">
          <span class="text-xs font-mono text-gray-500">{{ value }}</span>
        </template>

        <!-- Actions -->
        <template #actions="{ row }">
          <div class="flex justify-end gap-1.5">
            <!-- Voir détail -->
            <button
              @click="openDetail(row)"
              class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition"
              title="Voir le bulletin"
            >
              <EyeIcon class="w-4 h-4" />
            </button>
            <!-- Valider -->
            <button
              v-if="row.statut === 'brouillon' && (authStore.isRH || authStore.isAdmin)"
              @click="handleValider(row)"
              class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition"
              title="Valider"
            >
              <CheckCircleIcon class="w-4 h-4" />
            </button>
            <!-- Marquer payé -->
            <button
              v-if="row.statut === 'valide' && (authStore.isRH || authStore.isAdmin)"
              @click="openMarquerPaye(row)"
              class="p-1.5 text-primary-600 hover:bg-primary-50 rounded-lg transition"
              title="Marquer payé"
            >
              <BanknotesIcon class="w-4 h-4" />
            </button>
            <!-- Supprimer -->
            <button
              v-if="row.statut === 'brouillon' && authStore.isAdmin"
              @click="confirmDelete(row)"
              class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition"
              title="Supprimer"
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

    <!-- Générer bulletin -->
    <AppModal v-model="modalGenererOpen" title="Générer un bulletin de paie" size="lg">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Formulaire -->
        <GenererBulletinForm
          ref="genererFormRef"
          :employes="employes"
          :errors="formErrors"
          @submit="handleGenerer"
          @apercu="handleApercu"
          @element-change="handleElementChange"
        />

        <!-- Aperçu calcul -->
        <div class="space-y-3">
          <h4 class="text-sm font-semibold text-gray-700">Aperçu du calcul</h4>
          <div v-if="store.apercu">
            <RecapBulletin
              :calcul="store.apercu"
              :elements="apercuElements"
            />
          </div>
          <div v-else class="flex items-center justify-center h-40 bg-gray-50 rounded-xl text-gray-400 text-sm">
            Sélectionner un employé pour voir l'aperçu
          </div>
        </div>

      </div>
      <template #footer>
        <button @click="modalGenererOpen = false" class="btn-secondary">Annuler</button>
        <button @click="submitGenerer" :disabled="submitting" class="btn-primary flex items-center gap-2">
          <span v-if="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
          Générer le bulletin
        </button>
      </template>
    </AppModal>

    <!-- Détail bulletin -->
    <AppModal v-model="modalDetailOpen" title="Bulletin de paie" size="md">
      <BulletinDetail v-if="selected" :paie="selected" />
    </AppModal>

    <!-- Marquer payé -->
    <AppModal v-model="modalPayeOpen" title="Confirmer le paiement" size="sm">
      <div class="space-y-3">
        <p class="text-sm text-gray-600">
          Confirmer le paiement de <strong>{{ fmt(selected?.net_a_payer) }}</strong>
          à <strong>{{ selected?.employe?.user?.name }}</strong> ?
        </p>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date de paiement *</label>
          <input v-model="datePaiement" type="date" class="input-field" />
        </div>
      </div>
      <template #footer>
        <button @click="modalPayeOpen = false" class="btn-secondary">Annuler</button>
        <button @click="handleMarquerPaye" :disabled="submitting || !datePaiement" class="btn-primary">
          Confirmer le paiement
        </button>
      </template>
    </AppModal>

    <!-- Supprimer -->
    <AppModal v-model="modalDeleteOpen" title="Supprimer le bulletin" size="sm">
      <p class="text-sm text-gray-600">
        Supprimer définitivement le bulletin <strong>{{ selected?.reference }}</strong> ?
      </p>
      <template #footer>
        <button @click="modalDeleteOpen = false" class="btn-secondary">Non</button>
        <button @click="handleDelete" :disabled="submitting" class="btn-danger">Oui, supprimer</button>
      </template>
    </AppModal>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useToast }      from 'vue-toastification'
import { usePaiesStore } from '@/stores/paies'
import { useAuthStore }  from '@/stores/auth'
import { employesApi }   from '@/api/employes'

import AppTable          from '@/components/common/AppTable.vue'
import AppPagination     from '@/components/common/AppPagination.vue'
import AppModal          from '@/components/common/AppModal.vue'
import StatutPaieBadge   from './StatutPaieBadge.vue'
import GenererBulletinForm from './GenererBulletinForm.vue'
import RecapBulletin     from './RecapBulletin.vue'
import BulletinDetail    from './BulletinDetail.vue'

import {
  PlusIcon, EyeIcon, CheckCircleIcon, BanknotesIcon, TrashIcon
} from '@heroicons/vue/24/outline'

const store     = usePaiesStore()
const authStore = useAuthStore()
const toast     = useToast()

// ── Données ───────────────────────────────────────────────────────────────────
const employes      = ref([])
const apercuElements = ref([])
const datePaiement  = ref('')

const anneeActuelle = new Date().getFullYear()
const annees        = Array.from({ length: 5 }, (_, i) => anneeActuelle - i + 1)

const moisOptions = {
  1: 'Janvier', 2: 'Février', 3: 'Mars', 4: 'Avril',
  5: 'Mai', 6: 'Juin', 7: 'Juillet', 8: 'Août',
  9: 'Septembre', 10: 'Octobre', 11: 'Novembre', 12: 'Décembre',
}

// ── Computed ──────────────────────────────────────────────────────────────────
const periodeLabel = computed(() => {
  const m = store.filters.mois ? moisOptions[store.filters.mois] : 'Tous les mois'
  return `${m} ${store.filters.annee}`
})

// ── Colonnes table ────────────────────────────────────────────────────────────
const columns = [
  { key: 'employe',     label: 'Employé' },
  { key: 'reference',   label: 'Référence' },
  { key: 'periode',     label: 'Période' },
  { key: 'net_a_payer', label: 'Net à payer' },
  { key: 'statut',      label: 'Statut' },
]

// ── Modals ────────────────────────────────────────────────────────────────────
const modalGenererOpen = ref(false)
const modalDetailOpen  = ref(false)
const modalPayeOpen    = ref(false)
const modalDeleteOpen  = ref(false)
const selected         = ref(null)
const genererFormRef   = ref(null)
const formErrors       = ref({})
const submitting       = ref(false)

// ── Helpers ───────────────────────────────────────────────────────────────────
function fmt(v) {
  return Number(v ?? 0).toLocaleString('fr-FR') + ' FCFA'
}

function resetFiltres() {
  store.filters = {
    employe_id: '', mois: new Date().getMonth() + 1,
    annee: anneeActuelle, statut: '',
  }
  fetchPage(1)
}

// ── Fetch ─────────────────────────────────────────────────────────────────────
async function fetchPage(page = 1) {
  await store.fetchPaies(page)
  await store.fetchStats(store.filters.mois || new Date().getMonth() + 1, store.filters.annee)
}

// ── CRUD ──────────────────────────────────────────────────────────────────────
function openGenerer() {
  store.apercu   = null
  formErrors.value = {}
  apercuElements.value = []
  modalGenererOpen.value = true
}

async function handleApercu(payload) {
  if (!payload.employe_id) return
  try {
    await store.fetchApercu(payload)
    apercuElements.value = store.apercu?.avantages_detail?.map(a => ({
      libelle: a.libelle, type: 'avantage', montant: a.montant,
    })) ?? []
  } catch { /* silencieux */ }
}

function handleElementChange(elements) {
  // Relancer l'aperçu si on a un employé sélectionné
  const form = genererFormRef.value?.form
  if (form?.employe_id) {
    handleApercu({ employe_id: form.employe_id, elements })
  }
}

function submitGenerer() {
  genererFormRef.value?.$el?.dispatchEvent(new Event('submit', { bubbles: true }))
}

async function handleGenerer(data) {
  submitting.value = true
  formErrors.value = {}
  try {
    await store.createPaie(data)
    toast.success('Bulletin généré avec succès !')
    modalGenererOpen.value = false
    await fetchPage()
  } catch (err) {
    const errors = err.response?.data?.errors
    if (errors) formErrors.value = Object.fromEntries(
      Object.entries(errors).map(([k, v]) => [k, v[0]])
    )
    toast.error(err.response?.data?.message || 'Erreur lors de la génération.')
  } finally {
    submitting.value = false
  }
}

function openDetail(row) {
  selected.value       = row
  modalDetailOpen.value = true
}

async function handleValider(row) {
  try {
    await store.validerPaie(row.id)
    toast.success('Bulletin validé !')
    await fetchPage()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Erreur.')
  }
}

function openMarquerPaye(row) {
  selected.value    = row
  datePaiement.value = new Date().toISOString().split('T')[0]
  modalPayeOpen.value = true
}

async function handleMarquerPaye() {
  submitting.value = true
  try {
    await store.marquerPaye(selected.value.id, { date_paiement: datePaiement.value })
    toast.success('Bulletin marqué comme payé !')
    modalPayeOpen.value = false
    await fetchPage()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Erreur.')
  } finally {
    submitting.value = false
  }
}

function confirmDelete(row) {
  selected.value       = row
  modalDeleteOpen.value = true
}

async function handleDelete() {
  submitting.value = true
  try {
    await store.deletePaie(selected.value.id)
    toast.success('Bulletin supprimé.')
    modalDeleteOpen.value = false
  } catch (err) {
    toast.error(err.response?.data?.message || 'Erreur.')
  } finally {
    submitting.value = false
  }
}

// ── Init ──────────────────────────────────────────────────────────────────────
onMounted(async () => {
  store.filters.mois  = new Date().getMonth() + 1
  store.filters.annee = anneeActuelle
  await fetchPage()
  const { data } = await employesApi.getAll({ per_page: 100, statut: 'actif' })
  employes.value = data.data
})
</script>