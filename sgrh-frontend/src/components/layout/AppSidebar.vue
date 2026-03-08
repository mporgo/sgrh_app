<template>
  <aside
    :class="[
      'bg-primary-700 text-white flex flex-col transition-all duration-300 shrink-0',
      collapsed ? 'w-16' : 'w-64'
    ]"
  >
    <!-- Logo -->
    <div class="flex items-center gap-3 px-4 py-5 border-b border-primary-600">
      <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center shrink-0">
        <UsersIcon class="w-5 h-5 text-white" />
      </div>
      <span v-if="!collapsed" class="font-bold text-lg tracking-wide">SGRH</span>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 py-4 space-y-1 overflow-y-auto">
      <template v-for="item in visibleMenuItems" :key="item.name">

        <!-- Séparateur de groupe -->
        <div v-if="!collapsed" class="px-4 pt-4 pb-1">
          <span class="text-xs font-semibold text-primary-300 uppercase tracking-wider">
            {{ item.group }}
          </span>
        </div>

        <!-- Liens -->
        <router-link
          v-for="link in item.links"
          :key="link.to"
          :to="link.to"
          :title="collapsed ? link.label : ''"
          class="flex items-center gap-3 px-4 py-2.5 mx-2 rounded-lg transition-colors duration-150 text-primary-100 hover:bg-primary-600 hover:text-white group"
          :class="{ 'bg-primary-600 text-white': isActive(link.to) }"
        >
          <component :is="link.icon" class="w-5 h-5 shrink-0" />
          <span v-if="!collapsed" class="text-sm font-medium truncate">{{ link.label }}</span>
        </router-link>

      </template>
    </nav>

    <!-- Footer sidebar : infos user -->
    <div class="border-t border-primary-600 p-4">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-primary-500 flex items-center justify-center shrink-0">
          <span class="text-xs font-bold text-white">{{ initials }}</span>
        </div>
        <div v-if="!collapsed" class="overflow-hidden">
          <p class="text-sm font-medium text-white truncate">{{ authStore.userName }}</p>
          <p class="text-xs text-primary-300 truncate">{{ primaryRole }}</p>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

import {
  HomeIcon, UsersIcon, CalendarDaysIcon, StarIcon,
  AcademicCapIcon, BanknotesIcon, BellIcon,
  ChartBarIcon, Cog6ToothIcon,
} from '@heroicons/vue/24/outline'

defineProps({ collapsed: Boolean })

const route     = useRoute()
const authStore = useAuthStore()

const isActive = (to) => route.path.startsWith(to)

const initials = computed(() => {
  return authStore.userName
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const primaryRole = computed(() => {
  const map = { admin: 'Administrateur', rh: 'Responsable RH', manager: 'Manager', employe: 'Employé' }
  return map[authStore.userRoles[0]] || authStore.userRoles[0] || ''
})

// Définition des menus avec contrôle par rôle
const allMenuGroups = [
  {
    group: 'Principal',
    links: [
      { to: '/dashboard', label: 'Tableau de bord', icon: HomeIcon, roles: [] },
    ]
  },
  {
    group: 'RH',
    links: [
      { to: '/employes',    label: 'Employés',    icon: UsersIcon,         roles: ['admin', 'rh', 'manager'] },
      { to: '/conges',      label: 'Congés',      icon: CalendarDaysIcon,  roles: [] },
      { to: '/evaluations', label: 'Évaluations', icon: StarIcon,          roles: [] },
      { to: '/formations',  label: 'Formations',  icon: AcademicCapIcon,   roles: [] },
      { to: '/paie',        label: 'Paie',        icon: BanknotesIcon,     roles: ['admin', 'rh'] },
    ]
  },
  {
    group: 'Analyse',
    links: [
      { to: '/rapports',      label: 'Rapports',      icon: ChartBarIcon, roles: ['admin', 'rh', 'manager'] },
      { to: '/notifications', label: 'Notifications', icon: BellIcon,     roles: [] },
    ]
  },
  {
    group: 'Système',
    links: [
      { to: '/admin', label: 'Administration', icon: Cog6ToothIcon, roles: ['admin'] },
    ]
  },
]

// Filtrer selon les rôles de l'utilisateur connecté
const visibleMenuItems = computed(() => {
  return allMenuGroups.map(group => ({
    ...group,
    links: group.links.filter(link =>
      link.roles.length === 0 || link.roles.some(r => authStore.hasRole(r))
    )
  })).filter(group => group.links.length > 0)
})
</script>