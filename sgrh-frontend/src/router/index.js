import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  // ── Auth ──
  {
    path: '/login',
    name: 'login',
    component: () => import('@/pages/auth/LoginPage.vue'),
    meta: { guest: true },
  },

  // ── App (protégées) ──
  {
    path: '/',
    component: () => import('@/components/layout/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: '/dashboard',
      },
      {
        path: 'dashboard',
        name: 'dashboard',
        component: () => import('@/pages/dashboard/DashboardPage.vue'),
      },
      // ── Employés ──
      {
        path: 'employes',
        name: 'employes',
        component: () => import('@/pages/employes/EmployesPage.vue'),
        meta: { roles: ['admin', 'rh', 'manager'] },
      },
      {
        path: 'employes/:id',
        name: 'employe-detail',
        component: () => import('@/pages/employes/EmployeDetailPage.vue'),
        meta: { roles: ['admin', 'rh', 'manager'] },
      },
      // ── Congés ──
      {
        path: 'conges',
        name: 'conges',
        component: () => import('@/pages/conges/CongesPage.vue'),
      },
      // ── Évaluations ──
      {
        path: 'evaluations',
        name: 'evaluations',
        component: () => import('@/pages/evaluations/EvaluationsPage.vue'),
      },
      // ── Formations ──
      {
        path: 'formations',
        name: 'formations',
        component: () => import('@/pages/formations/FormationsPage.vue'),
      },
      // ── Paie ──
      {
        path: 'paie',
        name: 'paie',
        component: () => import('@/pages/paie/PaiePage.vue'),
        meta: { roles: ['admin', 'rh'] },
      },
      // ── Rapports ──
      {
        path: 'rapports',
        name: 'rapports',
        component: () => import('@/pages/rapports/RapportsPage.vue'),
        meta: { roles: ['admin', 'rh', 'manager'] },
      },
      // ── Administration ──
      {
        path: 'admin',
        name: 'admin',
        component: () => import('@/pages/admin/AdminPage.vue'),
        meta: { roles: ['admin'] },
      },
      // ── Notifications ──
      {
        path: 'notifications',
        name: 'notifications',
        component: () => import('@/pages/notifications/NotificationsPage.vue'),
        meta: { roles: ['admin', 'rh', 'manager'] },
      },
    ],
  },

  // ── Erreurs ──
  { path: '/403', component: () => import('@/pages/errors/Page403.vue') },
  { path: '/:pathMatch(.*)*', component: () => import('@/pages/errors/Page404.vue') },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// ── Navigation Guard ──────────────────────────────────────────────────────────
router.beforeEach(async (to) => {
  const auth = useAuthStore()

  // Si connecté mais user pas encore chargé → fetchMe
  if (auth.isAuthenticated && !auth.user) {
    await auth.fetchMe()
  }

  // Route protégée et non connecté → /login
  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login' }
  }

  // Route guest (ex: /login) mais déjà connecté → /dashboard
  if (to.meta.guest && auth.isAuthenticated) {
    return { name: 'dashboard' }
  }

  // Vérification des rôles
  if (to.meta.roles && !to.meta.roles.some(r => auth.hasRole(r))) {
    return { path: '/403' }
  }
})

export default router