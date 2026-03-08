import { defineStore } from 'pinia'
import { ref } from 'vue'
import { congesApi } from '@/api/conges'

export const useCongesStore = defineStore('conges', () => {

  const conges      = ref([])
  const soldes      = ref([])
  const typeConges  = ref([])
  const calendrier  = ref([])
  const loading     = ref(false)
  const meta        = ref({ total: 0, per_page: 15, current_page: 1, last_page: 1 })
  const filters     = ref({ statut: '', type_conge_id: '', annee: new Date().getFullYear() })

  async function fetchConges(page = 1) {
    loading.value = true
    try {
      const { data } = await congesApi.getAll({ ...filters.value, page })
      conges.value = data.data
      meta.value   = data.meta
    } finally {
      loading.value = false
    }
  }

  async function fetchSoldes() {
    const { data } = await congesApi.mesSoldes()
    soldes.value = data.data
  }

  async function fetchTypeConges() {
    const { data } = await congesApi.typeConges()
    typeConges.value = data.data
  }

  async function fetchCalendrier(annee, mois) {
    const { data } = await congesApi.calendrier({ annee, mois })
    calendrier.value = data.data
  }

  async function createConge(payload) {
    const { data } = await congesApi.create(payload)
    await fetchConges()
    await fetchSoldes()
    return data
  }

  async function traiterConge(id, payload) {
    const { data } = await congesApi.traiter(id, payload)
    await fetchConges()
    return data
  }

  async function annulerConge(id) {
    await congesApi.annuler(id)
    await fetchConges()
    await fetchSoldes()
  }

  return {
    conges, soldes, typeConges, calendrier,
    loading, meta, filters,
    fetchConges, fetchSoldes, fetchTypeConges,
    fetchCalendrier, createConge, traiterConge, annulerConge,
  }
})