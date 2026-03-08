<template>
  <div class="space-y-6">

    <!-- Titre + message accueil -->
    <div>
      <h2 class="text-2xl font-bold text-gray-800">
        Bonjour, {{ authStore.userName }} 👋
      </h2>
      <p class="text-gray-500 text-sm mt-1">
        {{ today }} · {{ primaryRole }}
      </p>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
      <KpiCard
        v-for="kpi in kpiCards"
        :key="kpi.label"
        v-bind="kpi"
      />
    </div>

    <!-- Ligne 2 : activités + congés en attente -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

      <!-- Activités récentes -->
      <div class="card">
        <h3 class="font-semibold text-gray-700 mb-4">Activités récentes</h3>
        <ul class="space-y-3">
          <li
            v-for="act in recentActivities"
            :key="act.id"
            class="flex items-start gap-3"
          >
            <span
              class="mt-1 w-2 h-2 rounded-full shrink-0"
              :class="act.color"
            />
            <div>
              <p class="text-sm text-gray-700">{{ act.message }}</p>
              <p class="text-xs text-gray-400">{{ act.time }}</p>
            </div>
          </li>
        </ul>
      </div>

      <!-- Demandes de congés en attente (manager / RH) -->
      <div v-if="authStore.isRH || authStore.isManager || authStore.isAdmin" class="card">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-semibold text-gray-700">Demandes en attente</h3>
          <router-link to="/conges" class="text-xs text-primary-500 hover:underline">Voir tout</router-link>
        </div>
        <div v-if="pendingLeaves.length === 0" class="text-sm text-gray-400 text-center py-4">
          Aucune demande en attente
        </div>
        <ul v-else class="space-y-3">
          <li
            v-for="leave in pendingLeaves"
            :key="leave.id"
            class="flex items-center justify-between p-3 rounded-lg bg-gray-50 hover:bg-gray-100 transition"
          >
            <div>
              <p class="text-sm font-medium text-gray-700">{{ leave.employe }}</p>
              <p class="text-xs text-gray-500">{{ leave.type }} · {{ leave.dates }}</p>
            </div>
            <span class="badge-warning">En attente</span>
          </li>
        </ul>
      </div>

      <!-- Pour un employé : son solde de congés -->
      <div v-else class="card">
        <h3 class="font-semibold text-gray-700 mb-4">Mon solde de congés</h3>
        <div class="space-y-3">
          <div v-for="solde in congesEmployee" :key="solde.type">
            <div class="flex justify-between text-sm mb-1">
              <span class="text-gray-600">{{ solde.type }}</span>
              <span class="font-medium text-gray-800">{{ solde.restants }}j restants</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div
                class="bg-primary-500 h-2 rounded-full transition-all"
                :style="{ width: (solde.restants / solde.total * 100) + '%' }"
              />
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import KpiCard from './KpiCard.vue'

const authStore = useAuthStore()

const today = new Date().toLocaleDateString('fr-FR', {
  weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
})

const primaryRole = computed(() => {
  const map = { admin: 'Administrateur', rh: 'Responsable RH', manager: 'Manager', employe: 'Employé' }
  return map[authStore.userRoles[0]] || ''
})

// KPI dynamiques selon rôle
const kpiCards = computed(() => {
  if (authStore.isRH || authStore.isAdmin) {
    return [
      { label: 'Employés actifs',     value: '—', icon: 'users',    color: 'blue'  },
      { label: 'Congés en attente',   value: '—', icon: 'calendar', color: 'yellow'},
      { label: 'Formations ce mois',  value: '—', icon: 'academic', color: 'green' },
      { label: 'Taux d\'absentéisme', value: '—', icon: 'chart',    color: 'red'   },
    ]
  }
  if (authStore.isManager) {
    return [
      { label: 'Mon équipe',        value: '—', icon: 'users',    color: 'blue'  },
      { label: 'Congés en attente', value: '—', icon: 'calendar', color: 'yellow'},
      { label: 'Évaluations',       value: '—', icon: 'star',     color: 'green' },
      { label: 'Absences du mois',  value: '—', icon: 'chart',    color: 'red'   },
    ]
  }
  return [
    { label: 'Jours de congés',  value: '—', icon: 'calendar', color: 'blue'  },
    { label: 'Mes formations',   value: '—', icon: 'academic', color: 'green' },
    { label: 'Évaluations',      value: '—', icon: 'star',     color: 'yellow'},
    { label: 'Notifications',    value: '—', icon: 'bell',     color: 'red'   },
  ]
})

// Données temporaires (seront remplacées par l'API)
const recentActivities = [
  { id: 1, message: 'Demande de congé soumise par Kader Traoré', time: 'il y a 2h',  color: 'bg-yellow-400' },
  { id: 2, message: 'Nouveau employé ajouté : Aminata Coulibaly', time: 'il y a 5h',  color: 'bg-green-400'  },
  { id: 3, message: 'Bulletin de paie généré pour mars 2025',     time: 'hier',       color: 'bg-blue-400'   },
  { id: 4, message: 'Formation "Excel Avancé" validée',           time: 'il y a 2j',  color: 'bg-purple-400' },
]

const pendingLeaves = [
  { id: 1, employe: 'Kader Traoré',   type: 'Congés payés', dates: '15 – 22 juil.' },
  { id: 2, employe: 'Fatoumata Diallo', type: 'RTT',         dates: '18 – 19 juil.' },
]

const congesEmployee = [
  { type: 'Congés payés', restants: 18, total: 25 },
  { type: 'RTT',          restants: 3,  total: 12  },
  { type: 'Maladie',      restants: 5,  total: 10  },
]
</script>