import { defineStore } from 'pinia'
import { ref } from 'vue'
import { adminApi } from '@/api/admin'

export const useAdminStore = defineStore('admin', () => {

  const users         = ref([])
  const roles         = ref([])
  const logs          = ref([])
  const infosSysteme  = ref(null)
  const loading       = ref(false)
  const metaUsers     = ref({ total: 0, per_page: 15, current_page: 1, last_page: 1 })
  const metaLogs      = ref({ total: 0, per_page: 20, current_page: 1, last_page: 1 })
  const filtersUsers  = ref({ search: '', role: '', is_active: '' })
  const filtersLogs   = ref({ module: '', user_id: '', search: '', date_debut: '', date_fin: '' })

  async function fetchUsers(page = 1) {
    loading.value = true
    try {
      const { data } = await adminApi.getUsers({ ...filtersUsers.value, page })
      users.value    = data.data
      metaUsers.value = data.meta
    } finally {
      loading.value = false
    }
  }

  async function fetchRoles() {
    try {
      const { data } = await adminApi.getRoles()
      roles.value = data.data
      console.log('Rôles chargés :', roles.value) // debug temporaire
    } catch (err) {
      console.error('Erreur fetchRoles :', err.response?.data ?? err.message)
    }
  }

  async function fetchLogs(page = 1) {
    loading.value = true
    try {
      const { data } = await adminApi.getLogs({ ...filtersLogs.value, page })
      logs.value     = data.data
      metaLogs.value = data.meta
    } finally {
      loading.value = false
    }
  }

  async function fetchInfosSysteme() {
    const { data } = await adminApi.infosSysteme()
    infosSysteme.value = data.data
  }

  async function createUser(payload) {
    const { data } = await adminApi.createUser(payload)
    await fetchUsers()
    return data
  }

  async function updateUser(id, payload) {
    const { data } = await adminApi.updateUser(id, payload)
    await fetchUsers()
    return data
  }

  async function toggleUser(id) {
    const { data } = await adminApi.toggleUser(id)
    const idx = users.value.findIndex(u => u.id === id)
    if (idx !== -1) users.value[idx] = data.data
    return data
  }

  async function resetPassword(id, payload) {
    const { data } = await adminApi.resetPassword(id, payload)
    return data
  }

  return {
    users, roles, logs, infosSysteme, loading,
    metaUsers, metaLogs, filtersUsers, filtersLogs,
    fetchUsers, fetchRoles, fetchLogs, fetchInfosSysteme,
    createUser, updateUser, toggleUser, resetPassword,
  }
})