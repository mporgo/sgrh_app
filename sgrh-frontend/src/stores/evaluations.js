import { defineStore } from 'pinia'
import { ref } from 'vue'
import { evaluationsApi } from '@/api/evaluations'

export const useEvaluationsStore = defineStore('evaluations', () => {

  const evaluations = ref([])
  const evaluation  = ref(null)
  const stats       = ref({})
  const loading     = ref(false)
  const meta        = ref({ total: 0, per_page: 15, current_page: 1, last_page: 1 })
  const filters     = ref({ statut: '', type: '', annee: new Date().getFullYear() })

  async function fetchEvaluations(page = 1) {
    loading.value = true
    try {
      const { data } = await evaluationsApi.getAll({ ...filters.value, page })
      evaluations.value = data.data
      meta.value        = data.meta
    } finally {
      loading.value = false
    }
  }

  async function fetchOne(id) {
    loading.value = true
    try {
      const { data } = await evaluationsApi.getOne(id)
      evaluation.value  = data.data
    } finally {
      loading.value = false
    }
  }

  async function fetchStats(annee) {
    const { data } = await evaluationsApi.stats({ annee })
    stats.value = data.data
  }

  async function createEvaluation(payload) {
    const { data } = await evaluationsApi.create(payload)
    await fetchEvaluations()
    return data
  }

  async function updateEvaluation(id, payload) {
    const { data } = await evaluationsApi.update(id, payload)
    // Rafraîchir le détail si on est sur la page détail
    if (evaluation.value?.id === id) {
      evaluation.value = data.data
    }
    await fetchEvaluations()
    return data
  }

  async function deleteEvaluation(id) {
    await evaluationsApi.delete(id)
    await fetchEvaluations()
  }

  async function commenterEmploye(id, payload) {
    const { data } = await evaluationsApi.commenterEmploye(id, payload)
    evaluation.value = data.data
    return data
  }

  return {
    evaluations, evaluation, stats, loading, meta, filters,
    fetchEvaluations, fetchOne, fetchStats,
    createEvaluation, updateEvaluation, deleteEvaluation, commenterEmploye,
  }
})