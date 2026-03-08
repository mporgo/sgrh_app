import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api/axios'

export const useAuthStore = defineStore('auth', () => {
  // ── State ──
  const user  = ref(null)
  const token = ref(localStorage.getItem('token') || null)

  // ── Getters ──
  const isAuthenticated = computed(() => !!token.value)
  const userRoles       = computed(() => user.value?.roles || [])
  const userName        = computed(() => user.value?.name || '')

  // ── Helpers rôles ──
  const hasRole = (role) => userRoles.value.includes(role)
  const isAdmin    = computed(() => hasRole('admin'))
  const isRH       = computed(() => hasRole('rh'))
  const isManager  = computed(() => hasRole('manager'))
  const isEmploye  = computed(() => hasRole('employe'))

  // ── Actions ──
  async function login(credentials) {
    const { data } = await api.post('/login', credentials)
    token.value = data.token
    user.value  = data.user
    localStorage.setItem('token', data.token)
    return data
  }

  async function fetchMe() {
    try {
      const { data } = await api.get('/me')
      user.value = data
    } catch {
      logout()
    }
  }

  async function logout() {
    try {
      await api.post('/logout')
    } catch { /* silencieux */ }
    finally {
      user.value  = null
      token.value = null
      localStorage.removeItem('token')
    }
  }

  return {
    user, token,
    isAuthenticated, userRoles, userName,
    hasRole, isAdmin, isRH, isManager, isEmploye,
    login, logout, fetchMe,
  }
})