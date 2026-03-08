import { defineStore } from 'pinia'
import { ref } from 'vue'
import { formationsApi } from '@/api/formations'

export const useFormationsStore = defineStore('formations', () => {

  const formations    = ref([])
  const formation     = ref(null)
  const mesFormations = ref([])
  const inscrits      = ref([])
  const loading       = ref(false)
  const meta          = ref({ total: 0, per_page: 12, current_page: 1, last_page: 1 })
  const filters       = ref({ statut: '', type: '', search: '' })

  async function fetchFormations(page = 1) {
    loading.value = true
    try {
      const { data } = await formationsApi.getAll({ ...filters.value, page })
      formations.value = data.data
      meta.value       = data.meta
    } finally {
      loading.value = false
    }
  }

  async function fetchOne(id) {
    loading.value = true
    try {
      const { data } = await formationsApi.getOne(id)
      formation.value  = data.data
    } finally {
      loading.value = false
    }
  }

  async function fetchMesFormations() {
    const { data }   = await formationsApi.mesFormations()
    mesFormations.value = data.data
  }

  async function fetchInscrits(id) {
    const { data } = await formationsApi.inscrits(id)
    inscrits.value = data.data
  }

  async function createFormation(payload) {
    const { data } = await formationsApi.create(payload)
    await fetchFormations()
    return data
  }

  async function updateFormation(id, payload) {
    const { data } = await formationsApi.update(id, payload)
    await fetchFormations()
    return data
  }

  async function deleteFormation(id) {
    await formationsApi.delete(id)
    await fetchFormations()
  }

  async function inscrire(id) {
    const { data } = await formationsApi.inscrire(id)
    await fetchFormations()
    return data
  }

  async function desinscrire(id) {
    await formationsApi.desinscrire(id)
    await fetchFormations()
  }

  async function validerInscription(id, payload) {
    const { data } = await formationsApi.validerInscription(id, payload)
    if (formation.value) await fetchInscrits(formation.value.id)
    return data
  }

  async function resultatsInscription(id, payload) {
    const { data } = await formationsApi.resultatsInscription(id, payload)
    if (formation.value) await fetchInscrits(formation.value.id)
    return data
  }

  return {
    formations, formation, mesFormations, inscrits,
    loading, meta, filters,
    fetchFormations, fetchOne, fetchMesFormations, fetchInscrits,
    createFormation, updateFormation, deleteFormation,
    inscrire, desinscrire, validerInscription, resultatsInscription,
  }
})