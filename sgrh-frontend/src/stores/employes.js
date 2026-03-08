import { defineStore } from 'pinia'
import { ref } from 'vue'
import { employesApi, departementsApi, postesApi } from '@/api/employes'

export const useEmployesStore = defineStore('employes', () => {

  // ── State ──────────────────────────────────────────────────────────────────
  const employes     = ref([])
  const employe      = ref(null)       // détail
  const departements = ref([])
  const postes       = ref([])
  const loading      = ref(false)
  const meta         = ref({           // pagination
    total: 0, per_page: 15,
    current_page: 1, last_page: 1,
  })
  const filters = ref({
    search: '', departement_id: '',
    poste_id: '', statut: '', type_contrat: '',
    per_page: 15,
  })

  // ── Actions ────────────────────────────────────────────────────────────────
  async function fetchEmployes(page = 1) {
    loading.value = true
    try {
      const { data } = await employesApi.getAll({ ...filters.value, page })
      employes.value = data.data
      meta.value     = data.meta
    } finally {
      loading.value = false
    }
  }

  async function fetchOne(id) {
    loading.value = true
    try {
      const { data } = await employesApi.getOne(id)
      employe.value  = data.data
    } finally {
      loading.value = false
    }
  }

  async function createEmploye(payload) {
    const { data } = await employesApi.create(payload)
    await fetchEmployes()
    return data
  }

  async function updateEmploye(id, payload) {
    const { data } = await employesApi.update(id, payload)
    await fetchEmployes()
    return data
  }

  async function deleteEmploye(id) {
    await employesApi.delete(id)
    await fetchEmployes()
  }

  async function fetchDepartements() {
    const { data } = await departementsApi.getAll()
    departements.value = data.data
  }

  async function fetchPostes(departement_id = '') {
    const { data } = await postesApi.getAll({ departement_id })
    postes.value = data.data
  }

  function resetFilters() {
    filters.value = {
      search: '', departement_id: '',
      poste_id: '', statut: '', type_contrat: '',
      per_page: 15,
    }
  }

  return {
    employes, employe, departements, postes,
    loading, meta, filters,
    fetchEmployes, fetchOne,
    createEmploye, updateEmploye, deleteEmploye,
    fetchDepartements, fetchPostes, resetFilters,
  }
})