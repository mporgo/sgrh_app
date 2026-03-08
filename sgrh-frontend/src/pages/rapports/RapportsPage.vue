<template>
  <div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h2 class="text-xl font-bold text-gray-800">Rapports analytiques</h2>
        <p class="text-sm text-gray-500">Tableau de bord RH — {{ store.annee }}</p>
      </div>
      <div class="flex items-center gap-3">
        <select v-model.number="store.annee" class="input-field w-28" @change="recharger">
          <option v-for="y in annees" :key="y" :value="y">{{ y }}</option>
        </select>
      </div>
    </div>

    <!-- Onglets -->
    <div class="border-b border-gray-200">
      <nav class="flex gap-6 overflow-x-auto">
        <button
          v-for="tab in tabs"
          :key="tab.value"
          @click="activeTab = tab.value; chargerOnglet(tab.value)"
          :class="[
            'pb-3 text-sm font-medium border-b-2 whitespace-nowrap transition',
            activeTab === tab.value
              ? 'border-primary-500 text-primary-600'
              : 'border-transparent text-gray-500 hover:text-gray-700'
          ]"
        >
          {{ tab.label }}
        </button>
      </nav>
    </div>

    <!-- Chargement -->
    <div v-if="store.loading" class="flex justify-center py-16">
      <div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" />
    </div>

    <!-- ── Onglet Vue globale ──────────────────────────────────────────────── -->
    <div v-else-if="activeTab === 'global' && store.rapportGlobal" class="space-y-6">

      <!-- KPIs globaux -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <KpiRapport label="Employés actifs"   :value="g.employes.actifs"          icon="👥" color="blue"   />
        <KpiRapport label="Jours d'absence"   :value="g.conges.jours_total"       icon="📅" color="yellow" />
        <KpiRapport label="Évaluations"       :value="g.evaluations.terminees"    icon="⭐" color="green"  />
        <KpiRapport label="Formations"        :value="g.formations.total_inscrits" icon="🎓" color="purple" />
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Répartition par contrat -->
        <div class="card">
          <h3 class="font-semibold text-gray-700 mb-4">Répartition par type de contrat</h3>
          <BarChart :items="contratsItems" />
        </div>

        <!-- Employés par département -->
        <div class="card">
          <h3 class="font-semibold text-gray-700 mb-4">Effectifs par département</h3>
          <BarChart :items="deptItems" default-color="#27ae60" />
        </div>

        <!-- Absences par mois -->
        <div class="card">
          <h3 class="font-semibold text-gray-700 mb-4">Jours d'absence par mois</h3>
          <LineChart :data="absencesMoisData" :labels="moisLabels" />
        </div>

        <!-- Masse salariale par mois -->
        <div class="card">
          <h3 class="font-semibold text-gray-700 mb-4">Masse salariale payée (FCFA)</h3>
          <LineChart :data="masseMoisData" :labels="moisLabels" />
        </div>

      </div>

      <!-- Notes évaluations -->
      <div class="card">
        <h3 class="font-semibold text-gray-700 mb-4">Répartition des notes d'évaluation</h3>
        <div class="flex flex-wrap gap-4">
          <div
            v-for="(count, note) in g.evaluations.par_note"
            :key="note"
            class="flex flex-col items-center gap-1"
          >
            <span class="text-2xl font-bold text-gray-800">{{ count }}</span>
            <span class="text-xs text-gray-500">{{ noteLabel(note) }}</span>
          </div>
        </div>
        <p v-if="g.evaluations.score_moyen" class="text-sm text-gray-600 mt-3">
          Score moyen : <strong class="text-primary-600">{{ g.evaluations.score_moyen }}/100</strong>
        </p>
      </div>

    </div>

    <!-- ── Onglet Absentéisme ─────────────────────────────────────────────── -->
    <div v-else-if="activeTab === 'absenteisme' && store.absenteisme" class="space-y-6">

      <!-- Sélecteur mois -->
      <div class="flex items-center gap-3">
        <select v-model="moisAbsenteisme" class="input-field w-40" @change="chargerAbsenteisme">
          <option value="">Toute l'année</option>
          <option v-for="(label, val) in moisOptions" :key="val" :value="parseInt(val)">{{ label }}</option>
        </select>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <KpiRapport label="Jours d'absence totaux" :value="store.absenteisme.total_jours"  icon="📅" color="red"    />
        <KpiRapport label="Employés concernés"     :value="store.absenteisme.nb_employes"  icon="👤" color="yellow" />
        <KpiRapport label="Moy. jours/employé"     :value="moyJoursAbsence"                icon="📊" color="blue"   />
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="card">
          <h3 class="font-semibold text-gray-700 mb-4">Absences par département</h3>
          <BarChart
            :items="store.absenteisme.par_departement.map(d => ({
              label: d.departement, value: d.jours_total,
            }))"
            default-color="#e74c3c"
          />
        </div>

        <div class="card">
          <h3 class="font-semibold text-gray-700 mb-4">Top 10 employés les plus absents</h3>
          <div class="space-y-2">
            <div
              v-for="(emp, i) in store.absenteisme.top_absents"
              :key="emp.employe"
              class="flex items-center justify-between text-sm p-2 rounded-lg hover:bg-gray-50"
            >
              <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-700 text-xs font-bold flex items-center justify-center">
                  {{ i + 1 }}
                </span>
                <div>
                  <p class="font-medium text-gray-800">{{ emp.employe }}</p>
                  <p class="text-xs text-gray-500">{{ emp.departement }}</p>
                </div>
              </div>
              <span class="font-bold text-red-600">{{ emp.jours_total }}j</span>
            </div>
          </div>
        </div>

      </div>

    </div>

    <!-- ── Onglet Masse salariale ─────────────────────────────────────────── -->
    <div v-else-if="activeTab === 'masse' && store.masseSalariale" class="space-y-6">

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <KpiRapport
          label="Masse nette annuelle"
          :value="fmt(store.masseSalariale.total_net_annee)"
          icon="💰" color="green"
        />
        <KpiRapport
          label="Total CNSS versé"
          :value="fmt(store.masseSalariale.total_cnss_annee)"
          icon="🏦" color="blue"
        />
        <KpiRapport
          label="Total IUTS versé"
          :value="fmt(store.masseSalariale.total_iuts_annee)"
          icon="📋" color="yellow"
        />
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Masse par mois -->
        <div class="card">
          <h3 class="font-semibold text-gray-700 mb-4">Évolution masse salariale</h3>
          <LineChart :data="masseNetParMois" :labels="moisLabels" />
        </div>

        <!-- Par département -->
        <div class="card">
          <h3 class="font-semibold text-gray-700 mb-4">Masse salariale par département</h3>
          <BarChart
            :items="store.masseSalariale.par_departement.map(d => ({
              label: d.departement,
              value: Math.round(d.masse_nette),
            }))"
          />
        </div>

      </div>

      <!-- Tableau mensuel -->
      <div class="card p-0 overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-primary-700 text-white">
            <tr>
              <th class="px-4 py-3 text-left">Mois</th>
              <th class="px-4 py-3 text-right">Base</th>
              <th class="px-4 py-3 text-right">Primes</th>
              <th class="px-4 py-3 text-right">CNSS</th>
              <th class="px-4 py-3 text-right">IUTS</th>
              <th class="px-4 py-3 text-right font-bold">Net</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(row, i) in store.masseSalariale.par_mois"
              :key="row.mois"
              :class="i % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
              class="border-t border-gray-100"
            >
              <td class="px-4 py-2.5 font-medium text-gray-700">{{ moisOptions[row.mois] }}</td>
              <td class="px-4 py-2.5 text-right text-gray-600">{{ fmt(row.base) }}</td>
              <td class="px-4 py-2.5 text-right text-green-600">{{ fmt(row.primes) }}</td>
              <td class="px-4 py-2.5 text-right text-orange-600">{{ fmt(row.cnss) }}</td>
              <td class="px-4 py-2.5 text-right text-orange-600">{{ fmt(row.iuts) }}</td>
              <td class="px-4 py-2.5 text-right font-bold text-gray-800">{{ fmt(row.net) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>

    <!-- ── Onglet Effectifs ───────────────────────────────────────────────── -->
    <div v-else-if="activeTab === 'effectifs' && store.effectifs" class="space-y-6">

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <KpiRapport label="Employés actifs"        :value="store.effectifs.total_actifs"        icon="👥" color="blue"  />
        <KpiRapport label="Ancienneté moyenne"     :value="store.effectifs.anciennete_moyenne + ' ans'" icon="📅" color="green" />
        <KpiRapport label="Contrats expirant"      :value="store.effectifs.contrats_expirants?.length" icon="⚠️" color="red" />
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Pyramide des âges -->
        <div class="card">
          <h3 class="font-semibold text-gray-700 mb-4">Pyramide des âges</h3>
          <BarChart
            :items="store.effectifs.pyramide_ages?.map(p => ({
              label: p.tranche, value: p.total,
            })) ?? []"
            default-color="#9b59b6"
          />
        </div>

        <!-- Répartition genre -->
        <div class="card">
          <h3 class="font-semibold text-gray-700 mb-4">Répartition par genre</h3>
          <div class="flex items-center justify-center gap-8 py-4">
            <div class="text-center">
              <p class="text-4xl font-bold text-blue-600">{{ store.effectifs.repartition_genre?.M ?? 0 }}</p>
              <p class="text-sm text-gray-500 mt-1">👨 Hommes</p>
            </div>
            <div class="text-center">
              <p class="text-4xl font-bold text-pink-500">{{ store.effectifs.repartition_genre?.F ?? 0 }}</p>
              <p class="text-sm text-gray-500 mt-1">👩 Femmes</p>
            </div>
          </div>
        </div>

        <!-- Effectifs par département -->
        <div class="card">
          <h3 class="font-semibold text-gray-700 mb-4">Effectifs par département</h3>
          <BarChart
            :items="store.effectifs.par_departement?.map(d => ({
              label: d.departement, value: d.total,
            })) ?? []"
            default-color="#27ae60"
          />
        </div>

        <!-- Contrats expirants -->
        <div class="card">
          <h3 class="font-semibold text-gray-700 mb-4 text-red-600">⚠️ Contrats expirant dans 30 jours</h3>
          <div v-if="store.effectifs.contrats_expirants?.length" class="space-y-2">
            <div
              v-for="c in store.effectifs.contrats_expirants"
              :key="c.nom"
              class="flex items-center justify-between p-2 rounded-lg bg-red-50 text-sm"
            >
              <div>
                <p class="font-medium text-gray-800">{{ c.nom }}</p>
                <p class="text-xs text-gray-500">{{ c.departement }} · {{ c.type_contrat }}</p>
              </div>
              <div class="text-right">
                <p class="font-bold text-red-600">{{ c.jours_restants }}j</p>
                <p class="text-xs text-gray-400">{{ formatDate(c.fin_contrat) }}</p>
              </div>
            </div>
          </div>
          <p v-else class="text-sm text-gray-400 italic">Aucun contrat expirant prochainement.</p>
        </div>

      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRapportsStore } from '@/stores/rapports'
import BarChart  from '@/components/common/BarChart.vue'
import LineChart from '@/components/common/LineChart.vue'

const store = useRapportsStore()

// ── Options ───────────────────────────────────────────────────────────────────
const anneeActuelle    = new Date().getFullYear()
const annees           = Array.from({ length: 5 }, (_, i) => anneeActuelle - i + 1)
const moisAbsenteisme  = ref('')

const moisOptions = {
  1: 'Jan', 2: 'Fév', 3: 'Mar', 4: 'Avr', 5: 'Mai', 6: 'Juin',
  7: 'Juil', 8: 'Aoû', 9: 'Sep', 10: 'Oct', 11: 'Nov', 12: 'Déc',
}
const moisLabels = Object.values(moisOptions)

// ── Onglets ───────────────────────────────────────────────────────────────────
const activeTab = ref('global')
const tabs = [
  { label: '📊 Vue globale',      value: 'global'    },
  { label: '📅 Absentéisme',      value: 'absenteisme'},
  { label: '💰 Masse salariale',  value: 'masse'     },
  { label: '👥 Effectifs',        value: 'effectifs' },
]

// ── Computed ──────────────────────────────────────────────────────────────────
const g = computed(() => store.rapportGlobal ?? {})

const contratsItems = computed(() =>
  Object.entries(g.value?.employes?.par_contrat ?? {}).map(([label, value]) => ({ label, value }))
)

const deptItems = computed(() =>
  (g.value?.employes?.par_departement ?? []).map(d => ({
    label: d.departement, value: d.total,
  }))
)

const absencesMoisData = computed(() => {
  const data = g.value?.conges?.par_mois ?? {}
  return Array.from({ length: 12 }, (_, i) => Number(data[i + 1] ?? 0))
})

const masseMoisData = computed(() => {
  const data = g.value?.paie?.par_mois ?? {}
  return Array.from({ length: 12 }, (_, i) => Number(data[i + 1] ?? 0))
})

const masseNetParMois = computed(() => {
  return (store.masseSalariale?.par_mois ?? []).map(m => Number(m.net ?? 0))
})

const moyJoursAbsence = computed(() => {
  const a = store.absenteisme
  if (!a || !a.nb_employes) return 0
  return Math.round(a.total_jours / a.nb_employes * 10) / 10
})

// ── Helpers ───────────────────────────────────────────────────────────────────
function fmt(v) {
  return Number(v ?? 0).toLocaleString('fr-FR') + ' FCFA'
}

function formatDate(d) {
  return d ? new Date(d).toLocaleDateString('fr-FR') : '—'
}

function noteLabel(note) {
  return {
    insuffisant: 'Insuffisant', passable: 'Passable',
    bien: 'Bien', tres_bien: 'Très bien', excellent: 'Excellent',
  }[note] ?? note
}

// ── Fetch ─────────────────────────────────────────────────────────────────────
async function chargerOnglet(tab) {
  if (tab === 'global')       await store.fetchGlobal()
  if (tab === 'absenteisme')  await store.fetchAbsenteisme(moisAbsenteisme.value)
  if (tab === 'masse')        await store.fetchMasseSalariale()
  if (tab === 'effectifs')    await store.fetchEffectifs()
}

async function chargerAbsenteisme() {
  await store.fetchAbsenteisme(moisAbsenteisme.value)
}

async function recharger() {
  await chargerOnglet(activeTab.value)
}

onMounted(() => chargerOnglet('global'))
</script>

<!-- Composant KPI inline -->
<script>
export default {
  components: {
    KpiRapport: {
      props: ['label', 'value', 'icon', 'color'],
      template: `
        <div class="card text-center">
          <div class="text-2xl mb-1">{{ icon }}</div>
          <p class="text-xl font-bold text-gray-800">{{ value }}</p>
          <p class="text-xs text-gray-500 mt-1">{{ label }}</p>
        </div>
      `,
    },
  },
}
</script>