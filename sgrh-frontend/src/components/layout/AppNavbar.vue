<template>
  <header class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between shrink-0">

    <!-- Bouton toggle sidebar -->
    <button
      @click="$emit('toggle-sidebar')"
      class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition"
    >
      <Bars3Icon class="w-5 h-5" />
    </button>

    <!-- Titre page courante -->
    <h1 class="text-base font-semibold text-gray-700 hidden sm:block">
      {{ pageTitle }}
    </h1>

    <!-- Actions droite -->
    <div class="flex items-center gap-3">

      <!-- Notifications -->
      <router-link to="/notifications" class="relative p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition">
        <BellIcon class="w-5 h-5" />
        <span
          v-if="notifStore.nonLues > 0"
          class="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center"
        >
          {{ notifStore.nonLues > 9 ? '9+' : notifStore.nonLues }}
        </span>
      </router-link>

      <!-- Menu utilisateur -->
      <div class="relative" ref="userMenuRef">
        <button
          @click="userMenuOpen = !userMenuOpen"
          class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100 transition"
        >
          <div class="w-8 h-8 rounded-full bg-primary-500 flex items-center justify-center">
            <span class="text-xs font-bold text-white">{{ initials }}</span>
          </div>
          <span class="text-sm font-medium text-gray-700 hidden md:block">{{ authStore.userName }}</span>
          <ChevronDownIcon class="w-4 h-4 text-gray-400 hidden md:block" />
        </button>

        <!-- Dropdown -->
        <div
          v-if="userMenuOpen"
          class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50"
        >
          <div class="px-4 py-2 border-b border-gray-100">
            <p class="text-sm font-medium text-gray-800">{{ authStore.userName }}</p>
            <p class="text-xs text-gray-500">{{ authStore.user?.email }}</p>
          </div>
          <router-link
            to="/profil"
            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
            @click="userMenuOpen = false"
          >
            <UserCircleIcon class="w-4 h-4" /> Mon profil
          </router-link>
          <button
            @click="handleLogout"
            class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50"
          >
            <ArrowRightOnRectangleIcon class="w-4 h-4" /> Déconnexion
          </button>
        </div>
      </div>

    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import { useAuthStore } from '@/stores/auth'

import { useNotificationsStore } from '@/stores/notifications'

import {
  Bars3Icon, BellIcon, ChevronDownIcon,
  UserCircleIcon, ArrowRightOnRectangleIcon,
} from '@heroicons/vue/24/outline'

defineEmits(['toggle-sidebar'])

const route     = useRoute()
const router    = useRouter()
const toast     = useToast()
const authStore = useAuthStore()

const notifStore = useNotificationsStore()

const userMenuOpen = ref(false)
const userMenuRef  = ref(null)

const initials = computed(() =>
  authStore.userName.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
)

const pageTitles = {
  '/dashboard':    'Tableau de bord',
  '/employes':     'Gestion des employés',
  '/conges':       'Congés & Absences',
  '/evaluations':  'Évaluations',
  '/formations':   'Formations',
  '/paie':         'Paie & Avantages',
  '/rapports':     'Rapports analytiques',
  '/notifications':'Notifications',
  '/admin':        'Administration',
}

const pageTitle = computed(() => {
  const path = '/' + route.path.split('/')[1]
  return pageTitles[path] || 'SGRH'
})

async function handleLogout() {
  await authStore.logout()
  toast.info('Vous êtes déconnecté.')
  router.push('/login')
}

// Fermer le menu si clic en dehors
function handleClickOutside(e) {
  if (userMenuRef.value && !userMenuRef.value.contains(e.target)) {
    userMenuOpen.value = false
  }
}
onMounted(async () => {
  document.addEventListener('click', handleClickOutside)
  await notifStore.fetchNonLues()
})
onUnmounted(() => document.removeEventListener('click', handleClickOutside))
</script>