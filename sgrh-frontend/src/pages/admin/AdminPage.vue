<template>
  <div class="space-y-6">

    <!-- Header -->
    <div>
      <h2 class="text-xl font-bold text-gray-800">Administration</h2>
      <p class="text-sm text-gray-500">Gestion des accès, rôles et journaux système</p>
    </div>

    <!-- Onglets -->
    <div class="border-b border-gray-200">
      <nav class="flex gap-6">
        <button
          v-for="tab in tabs"
          :key="tab.value"
          @click="activeTab = tab.value; chargerOnglet(tab.value)"
          :class="[
            'pb-3 text-sm font-medium border-b-2 transition whitespace-nowrap',
            activeTab === tab.value
              ? 'border-primary-500 text-primary-600'
              : 'border-transparent text-gray-500 hover:text-gray-700'
          ]"
        >
          {{ tab.label }}
        </button>
      </nav>
    </div>

    <!-- ── Onglet Utilisateurs ──────────────────────────────────────────────── -->
    <div v-if="activeTab === 'users'" class="space-y-5">

      <!-- Actions -->
      <div class="flex flex-col sm:flex-row gap-3 justify-between">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 flex-1">
          <input
            v-model="store.filtersUsers.search"
            type="text"
            class="input-field"
            placeholder="Rechercher un utilisateur..."
            @input="debouncedUsers"
          />
          <select v-model="store.filtersUsers.role" class="input-field" @change="store.fetchUsers(1)">
            <option value="">Tous les rôles</option>
            <option v-for="r in store.roles" :key="r.name" :value="r.name">{{ r.name }}</option>
          </select>
          <select v-model="store.filtersUsers.is_active" class="input-field" @change="store.fetchUsers(1)">
            <option value="">Tous les statuts</option>
            <option value="1">Actifs</option>
            <option value="0">Inactifs</option>
          </select>
        </div>
        <button @click="openCreer" class="btn-primary flex items-center gap-2 whitespace-nowrap">
          <PlusIcon class="w-4 h-4" /> Nouvel utilisateur
        </button>
      </div>

      <!-- Tableau utilisateurs -->
      <div class="card p-0 overflow-hidden">
        <AppTable :columns="columnsUsers" :data="store.users" :loading="store.loading">

          <!-- Nom + Email -->
          <template #cell-name="{ row }">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 text-sm font-bold
                          flex items-center justify-center shrink-0">
                {{ row.name?.charAt(0)?.toUpperCase() }}
              </div>
              <div>
                <p class="font-medium text-gray-800">{{ row.name }}</p>
                <p class="text-xs text-gray-500">{{ row.email }}</p>
              </div>
            </div>
          </template>

          <!-- Rôles -->
          <template #cell-roles="{ row }">
            <span
              v-for="role in row.roles"
              :key="role.name"
              :class="['inline-flex px-2 py-0.5 rounded text-xs font-medium mr-1', roleColor(role.name)]"
            >
              {{ role.name }}
            </span>
          </template>

          <!-- Statut -->
          <template #cell-is_active="{ value }">
            <span :class="['inline-flex px-2 py-0.5 rounded-full text-xs font-medium',
              value ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600']"
            >
              {{ value ? '✅ Actif' : '🔴 Inactif' }}
            </span>
          </template>

          <!-- Actions -->
          <template #actions="{ row }">
            <div class="flex justify-end gap-1.5">
              <button
                @click="openModifier(row)"
                class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg transition"
                title="Modifier"
              >
                <PencilIcon class="w-4 h-4" />
              </button>
              <button
                @click="openResetPassword(row)"
                class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition"
                title="Réinitialiser le mot de passe"
              >
                <KeyIcon class="w-4 h-4" />
              </button>
              <button
                v-if="row.id !== authStore.user?.id"
                @click="handleToggle(row)"
                :class="['p-1.5 rounded-lg transition',
                  row.is_active ? 'text-red-500 hover:bg-red-50' : 'text-green-600 hover:bg-green-50']"
                :title="row.is_active ? 'Désactiver' : 'Activer'"
              >
                <component :is="row.is_active ? XCircleIcon : CheckCircleIcon" class="w-4 h-4" />
              </button>
            </div>
          </template>

        </AppTable>
        <div class="px-4 pb-4">
          <AppPagination :meta="store.metaUsers" @change="store.fetchUsers" />
        </div>
      </div>

    </div>

    <!-- ── Onglet Logs ─────────────────────────────────────────────────────── -->
    <div v-else-if="activeTab === 'logs'" class="space-y-5">

      <!-- Filtres logs -->
      <div class="card">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <input
            v-model="store.filtersLogs.search"
            type="text"
            class="input-field"
            placeholder="Rechercher dans les logs..."
            @input="debouncedLogs"
          />
          <select v-model="store.filtersLogs.module" class="input-field" @change="store.fetchLogs(1)">
            <option value="">Tous les modules</option>
            <option value="administration">Administration</option>
            <option value="employes">Employés</option>
            <option value="conges">Congés</option>
            <option value="evaluations">Évaluations</option>
            <option value="formations">Formations</option>
            <option value="paie">Paie</option>
          </select>
          <input
            v-model="store.filtersLogs.date_debut"
            type="date"
            class="input-field"
            @change="store.fetchLogs(1)"
          />
          <input
            v-model="store.filtersLogs.date_fin"
            type="date"
            class="input-field"
            @change="store.fetchLogs(1)"
          />
        </div>
      </div>

      <!-- Liste logs -->
      <div class="space-y-2">
        <div v-if="store.loading" class="flex justify-center py-12">
          <div class="w-6 h-6 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" />
        </div>

        <div
          v-else
          v-for="log in store.logs"
          :key="log.id"
          class="card flex items-start gap-4"
        >
          <!-- Icône module -->
          <div :class="['w-9 h-9 rounded-lg flex items-center justify-center text-sm shrink-0', moduleColor(log.module)]">
            {{ moduleIcon(log.module) }}
          </div>

          <!-- Contenu -->
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-0.5">
              <span class="text-xs font-mono bg-gray-100 text-gray-600 px-2 py-0.5 rounded">
                {{ log.action }}
              </span>
              <span class="text-xs text-gray-400">{{ log.date_relative }}</span>
              <span class="text-xs text-gray-400">📡 {{ log.ip_address }}</span>
            </div>
            <p class="text-sm text-gray-700">{{ log.description }}</p>
            <p v-if="log.user" class="text-xs text-gray-500 mt-0.5">
              Par : <strong>{{ log.user.name }}</strong>
            </p>
          </div>

          <!-- Voir données -->
          <button
            v-if="log.donnees_avant || log.donnees_apres"
            @click="openDiff(log)"
            class="p-1.5 text-blue-500 hover:bg-blue-50 rounded-lg transition shrink-0"
            title="Voir les modifications"
          >
            <EyeIcon class="w-4 h-4" />
          </button>
        </div>
      </div>

      <AppPagination :meta="store.metaLogs" @change="store.fetchLogs" />
    </div>

    <!-- ── Onglet Système ─────────────────────────────────────────────────── -->
    <div v-else-if="activeTab === 'systeme' && store.infosSysteme" class="space-y-5">

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card text-center">
          <p class="text-2xl font-bold text-primary-600">{{ store.infosSysteme.nb_users }}</p>
          <p class="text-xs text-gray-500 mt-1">Utilisateurs total</p>
        </div>
        <div class="card text-center">
          <p class="text-2xl font-bold text-green-600">{{ store.infosSysteme.nb_actifs }}</p>
          <p class="text-xs text-gray-500 mt-1">Comptes actifs</p>
        </div>
        <div class="card text-center">
          <p class="text-sm font-bold text-gray-700">{{ store.infosSysteme.laravel }}</p>
          <p class="text-xs text-gray-500 mt-1">Laravel</p>
        </div>
        <div class="card text-center">
          <p class="text-sm font-bold text-gray-700">{{ store.infosSysteme.php_version }}</p>
          <p class="text-xs text-gray-500 mt-1">PHP</p>
        </div>
      </div>

      <!-- Infos détaillées -->
      <div class="card space-y-3">
        <h3 class="font-semibold text-gray-700">Informations système</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
          <InfoRow label="Application"     :value="store.infosSysteme.app_name" />
          <InfoRow label="Environnement"   :value="store.infosSysteme.app_env" />
          <InfoRow label="Base de données" :value="store.infosSysteme.db_name" />
          <InfoRow label="Cache driver"    :value="store.infosSysteme.cache_driver" />
          <InfoRow label="Heure serveur"   :value="store.infosSysteme.server_time" />
        </div>
      </div>

      <!-- Gestion rôles -->
      <div class="card space-y-3">
        <h3 class="font-semibold text-gray-700">Rôles disponibles</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <div
            v-for="role in store.roles"
            :key="role.name"
            class="text-center p-4 rounded-xl bg-gray-50"
          >
            <p class="text-2xl font-bold text-gray-800">{{ role.nb_users }}</p>
            <span :class="['inline-flex px-2 py-0.5 rounded text-xs font-medium mt-2', roleColor(role.name)]">
              {{ role.name }}
            </span>
          </div>
        </div>
      </div>

    </div>

    <!-- ════════════════════════════════════════════════════════════════════ -->
    <!-- MODALS                                                              -->
    <!-- ════════════════════════════════════════════════════════════════════ -->

    <!-- Créer / Modifier utilisateur -->
    <AppModal
      v-model="modalUserOpen"
      :title="isEdit ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur'"
      size="md"
    >
      <UserForm
        ref="userFormRef"
        :roles="store.roles"
        :errors="formErrors"
        :initial="formInitial"
        :is-edit="isEdit"
        @submit="handleSubmitUser"
      />
      <template #footer>
        <button @click="modalUserOpen = false" class="btn-secondary">Annuler</button>
        <button @click="submitUser" :disabled="submitting" class="btn-primary flex items-center gap-2">
          <span v-if="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
          {{ isEdit ? 'Enregistrer' : 'Créer le compte' }}
        </button>
      </template>
    </AppModal>

    <!-- Reset password -->
    <AppModal v-model="modalResetOpen" title="Réinitialiser le mot de passe" size="sm">
      <div class="space-y-3">
        <p class="text-sm text-gray-600">
          Définir un nouveau mot de passe pour <strong>{{ selected?.name }}</strong>
        </p>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe *</label>
          <input v-model="newPassword" type="password" class="input-field" placeholder="Min. 8 caractères" />
        </div>
      </div>
      <template #footer>
        <button @click="modalResetOpen = false" class="btn-secondary">Annuler</button>
        <button
          @click="handleResetPassword"
          :disabled="submitting || newPassword.length < 8"
          class="btn-primary"
        >
          Réinitialiser
        </button>
      </template>
    </AppModal>

    <!-- Diff données log -->
    <AppModal v-model="modalDiffOpen" title="Détail de la modification" size="md">
      <div v-if="selectedLog" class="space-y-4 text-xs font-mono">
        <div v-if="selectedLog.donnees_avant">
          <p class="text-sm font-sans font-semibold text-gray-700 mb-2">Avant :</p>
          <pre class="bg-red-50 text-red-800 rounded-lg p-3 overflow-x-auto">{{ JSON.stringify(selectedLog.donnees_avant, null, 2) }}</pre>
        </div>
        <div v-if="selectedLog.donnees_apres">
          <p class="text-sm font-sans font-semibold text-gray-700 mb-2">Après :</p>
          <pre class="bg-green-50 text-green-800 rounded-lg p-3 overflow-x-auto">{{ JSON.stringify(selectedLog.donnees_apres, null, 2) }}</pre>
        </div>
      </div>
    </AppModal>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast }   from 'vue-toastification'
import { useAdminStore } from '@/stores/admin'
import { useAuthStore }  from '@/stores/auth'
import { useDebounceFn } from '@/composables/useDebounceFn'

import AppTable      from '@/components/common/AppTable.vue'
import AppPagination from '@/components/common/AppPagination.vue'
import AppModal      from '@/components/common/AppModal.vue'
import InfoRow       from '@/components/common/InfoRow.vue'
import UserForm      from './UserForm.vue'

import {
  PlusIcon, PencilIcon, KeyIcon,
  XCircleIcon, CheckCircleIcon, EyeIcon,
} from '@heroicons/vue/24/outline'

const store     = useAdminStore()
const authStore = useAuthStore()
const toast     = useToast()

// ── Onglets ───────────────────────────────────────────────────────────────────
const activeTab = ref('users')
const tabs = [
  { label: '👤 Utilisateurs', value: 'users'   },
  { label: '📋 Journaux',     value: 'logs'    },
  { label: '⚙️ Système',      value: 'systeme' },
]

// ── Colonnes ──────────────────────────────────────────────────────────────────
const columnsUsers = [
  { key: 'name',      label: 'Utilisateur' },
  { key: 'roles',     label: 'Rôle' },
  { key: 'phone',     label: 'Téléphone' },
  { key: 'is_active', label: 'Statut' },
]

// ── Modals ────────────────────────────────────────────────────────────────────
const modalUserOpen  = ref(false)
const modalResetOpen = ref(false)
const modalDiffOpen  = ref(false)
const isEdit         = ref(false)
const selected       = ref(null)
const selectedLog    = ref(null)
const userFormRef    = ref(null)
const formInitial    = ref({})
const formErrors     = ref({})
const submitting     = ref(false)
const newPassword    = ref('')

// ── Helpers ───────────────────────────────────────────────────────────────────
function roleColor(role) {
  return {
    admin:   'bg-red-100 text-red-700',
    rh:      'bg-purple-100 text-purple-700',
    manager: 'bg-blue-100 text-blue-700',
    employe: 'bg-gray-100 text-gray-600',
  }[role] ?? 'bg-gray-100 text-gray-600'
}

function moduleIcon(module) {
  return {
    administration: '⚙️', employes: '👤',
    conges: '📅', evaluations: '⭐',
    formations: '🎓', paie: '💰',
  }[module] ?? '📋'
}

function moduleColor(module) {
  return {
    administration: 'bg-red-50',
    employes:       'bg-blue-50',
    conges:         'bg-yellow-50',
    evaluations:    'bg-purple-50',
    formations:     'bg-teal-50',
    paie:           'bg-green-50',
  }[module] ?? 'bg-gray-50'
}

// ── Debounce ──────────────────────────────────────────────────────────────────
const debouncedUsers = useDebounceFn(() => store.fetchUsers(1), 400)
const debouncedLogs  = useDebounceFn(() => store.fetchLogs(1), 400)

// ── Chargement onglet ─────────────────────────────────────────────────────────
async function chargerOnglet(tab) {
  if (tab === 'users')   await Promise.all([store.fetchUsers(), store.fetchRoles()])
  if (tab === 'logs')    await store.fetchLogs()
  if (tab === 'systeme') await Promise.all([store.fetchInfosSysteme(), store.fetchRoles()])
}

// ── CRUD utilisateurs ─────────────────────────────────────────────────────────
function openCreer() {
  isEdit.value       = false
  formInitial.value  = {}
  formErrors.value   = {}
  modalUserOpen.value = true
}

function openModifier(user) {
  isEdit.value      = true
  selected.value    = user
  formInitial.value = {
    name:      user.name,
    email:     user.email,
    phone:     user.phone,
    role:      user.roles?.[0]?.name ?? '',
    is_active: user.is_active,
  }
  formErrors.value   = {}
  modalUserOpen.value = true
}

function submitUser() {
  userFormRef.value?.$el?.dispatchEvent(new Event('submit', { bubbles: true }))
}

async function handleSubmitUser(data) {
  submitting.value = true
  formErrors.value = {}
  try {
    if (isEdit.value) {
      await store.updateUser(selected.value.id, data)
      toast.success('Utilisateur mis à jour !')
    } else {
      await store.createUser(data)
      toast.success('Utilisateur créé avec succès !')
    }
    modalUserOpen.value = false
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

async function handleToggle(user) {
  try {
    const { message } = await store.toggleUser(user.id)
    toast.success(message)
  } catch (err) {
    toast.error(err.response?.data?.message || 'Erreur.')
  }
}

function openResetPassword(user) {
  selected.value    = user
  newPassword.value = ''
  modalResetOpen.value = true
}

async function handleResetPassword() {
  submitting.value = true
  try {
    await store.resetPassword(selected.value.id, { password: newPassword.value })
    toast.success('Mot de passe réinitialisé !')
    modalResetOpen.value = false
  } catch (err) {
    toast.error(err.response?.data?.message || 'Erreur.')
  } finally {
    submitting.value = false
  }
}

// ── Logs ──────────────────────────────────────────────────────────────────────
function openDiff(log) {
  selectedLog.value  = log
  modalDiffOpen.value = true
}

// ── Init ──────────────────────────────────────────────────────────────────────
onMounted(async () => {
  await store.fetchRoles()     
  await chargerOnglet('users')
})

</script>