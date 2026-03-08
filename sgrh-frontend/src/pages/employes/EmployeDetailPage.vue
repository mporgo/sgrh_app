<template>
  <div v-if="store.loading" class="flex justify-center py-20">
    <div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" />
  </div>

  <div v-else-if="store.employe" class="space-y-6 max-w-4xl mx-auto">

    <!-- Header fiche -->
    <div class="card flex flex-col sm:flex-row items-start sm:items-center gap-5">
      <div class="w-20 h-20 rounded-2xl bg-primary-100 flex items-center justify-center shrink-0">
        <span class="text-2xl font-bold text-primary-700">{{ initials }}</span>
      </div>
      <div class="flex-1">
        <h2 class="text-2xl font-bold text-gray-800">{{ e.user?.name }}</h2>
        <p class="text-gray-500 text-sm">{{ e.poste?.titre ?? 'Poste non défini' }} · {{ e.departement?.nom ?? '—' }}</p>
        <div class="flex flex-wrap gap-2 mt-2">
          <AppBadge :value="e.statut"        type="statut" />
          <AppBadge :value="e.type_contrat"  type="contrat" />
          <span v-if="e.contrat_expirant" class="badge-danger">⚠️ Contrat expirant</span>
        </div>
      </div>
      <div class="flex gap-2">
        <button @click="$router.back()" class="btn-secondary text-sm">← Retour</button>
        <button
          v-if="authStore.isAdmin || authStore.isRH"
          @click="goEdit"
          class="btn-primary text-sm"
        >
          Modifier
        </button>
      </div>
    </div>

    <!-- Grille infos -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

      <!-- Infos personnelles -->
      <div class="card space-y-3">
        <h3 class="font-semibold text-gray-700 border-b border-gray-100 pb-2">Informations personnelles</h3>
        <InfoRow label="Email"        :value="e.user?.email" />
        <InfoRow label="Téléphone"    :value="e.user?.phone" />
        <InfoRow label="Naissance"    :value="formatDate(e.user?.date_naissance)" />
        <InfoRow label="Genre"        :value="genreLabel(e.user?.genre)" />
        <InfoRow label="Adresse"      :value="e.user?.adresse" />
      </div>

      <!-- Infos professionnelles -->
      <div class="card space-y-3">
        <h3 class="font-semibold text-gray-700 border-b border-gray-100 pb-2">Informations professionnelles</h3>
        <InfoRow label="Matricule"    :value="e.matricule" />
        <InfoRow label="Embauche"     :value="formatDate(e.date_embauche)" />
        <InfoRow label="Fin contrat"  :value="formatDate(e.fin_contrat)" />
        <InfoRow label="Salaire"      :value="formatSalaire(e.salaire_base)" />
        <InfoRow label="Solde congés" :value="`${e.conge_solde} jours`" />
        <InfoRow label="Manager"      :value="e.manager?.name" />
      </div>

    </div>

    <!-- Notes -->
    <div v-if="e.notes" class="card">
      <h3 class="font-semibold text-gray-700 mb-2">Notes internes</h3>
      <p class="text-sm text-gray-600 whitespace-pre-line">{{ e.notes }}</p>
    </div>

  </div>

  <div v-else class="text-center py-20 text-gray-400">
    Employé introuvable.
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useEmployesStore } from '@/stores/employes'
import { useAuthStore }     from '@/stores/auth'
import AppBadge             from '@/components/common/AppBadge.vue'
import InfoRow from '@/components/common/InfoRow.vue'

const route     = useRoute()
const router    = useRouter()
const store     = useEmployesStore()
const authStore = useAuthStore()

const e = computed(() => store.employe ?? {})

const initials = computed(() =>
  (e.value.user?.name ?? '').split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
)

function formatDate(d) {
  return d ? new Date(d).toLocaleDateString('fr-FR') : '—'
}

function formatSalaire(v) {
  return v ? Number(v).toLocaleString('fr-FR') + ' FCFA' : '—'
}

function genreLabel(g) {
  return { M: 'Masculin', F: 'Féminin', autre: 'Autre' }[g] ?? '—'
}

function goEdit() {
  router.push({ name: 'employes' }) // pour l'instant on passe par la liste
}

onMounted(() => store.fetchOne(route.params.id))
</script>