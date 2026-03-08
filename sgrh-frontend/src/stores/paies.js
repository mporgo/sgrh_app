import { defineStore } from 'pinia'
import { ref } from 'vue'
import { paiesApi } from '@/api/paies'

export const usePaiesStore = defineStore('paies', () => {

  const paies   = ref([])
  const paie    = ref(null)
  const stats   = ref({})
  const apercu  = ref(null)
  const loading = ref(false)
  const meta    = ref({ total: 0, per_page: 15, current_page: 1, last_page: 1 })
  const filters = ref({
    employe_id: '', mois: '', annee: new Date().getFullYear(), statut: '',
  })

  async function fetchPaies(page = 1) {
    loading.value = true
    try {
      const { data } = await paiesApi.getAll({ ...filters.value, page })
      paies.value = data.data
      meta.value  = data.meta
    } finally {
      loading.value = false
    }
  }

  async function fetchOne(id) {
    loading.value = true
    try {
      const { data } = await paiesApi.getOne(id)
      paie.value  = data.data
    } finally {
      loading.value = false
    }
  }

  async function fetchStats(mois, annee) {
    const { data } = await paiesApi.stats({ mois, annee })
    stats.value = data.data
  }

  async function fetchApercu(payload) {
    const { data } = await paiesApi.apercu(payload)
    apercu.value = data.data
    return data.data
  }

  async function createPaie(payload) {
    const { data } = await paiesApi.create(payload)
    await fetchPaies()
    return data
  }

  async function updatePaie(id, payload) {
    const { data } = await paiesApi.update(id, payload)
    await fetchPaies()
    return data
  }

  async function deletePaie(id) {
    await paiesApi.delete(id)
    await fetchPaies()
  }

  async function validerPaie(id) {
    const { data } = await paiesApi.valider(id)
    await fetchPaies()
    return data
  }

  async function marquerPaye(id, payload) {
    const { data } = await paiesApi.marquerPaye(id, payload)
    await fetchPaies()
    return data
  }

  return {
    paies, paie, stats, apercu, loading, meta, filters,
    fetchPaies, fetchOne, fetchStats, fetchApercu,
    createPaie, updatePaie, deletePaie, validerPaie, marquerPaye,
  }
})