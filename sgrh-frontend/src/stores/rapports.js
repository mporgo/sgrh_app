import { defineStore } from 'pinia'
import { ref } from 'vue'
import { rapportsApi } from '@/api/rapports'

export const useRapportsStore = defineStore('rapports', () => {

  const rapportGlobal  = ref(null)
  const absenteisme    = ref(null)
  const masseSalariale = ref(null)
  const effectifs      = ref(null)
  const loading        = ref(false)
  const annee          = ref(new Date().getFullYear())

  async function fetchGlobal() {
    loading.value = true
    try {
      const { data } = await rapportsApi.global({ annee: annee.value })
      rapportGlobal.value = data.data
    } finally {
      loading.value = false
    }
  }

  async function fetchAbsenteisme(mois = '') {
    const { data } = await rapportsApi.absenteisme({ annee: annee.value, mois })
    absenteisme.value = data.data
  }

  async function fetchMasseSalariale() {
    const { data } = await rapportsApi.masseSalariale({ annee: annee.value })
    masseSalariale.value = data.data
  }

  async function fetchEffectifs() {
    const { data } = await rapportsApi.effectifs()
    effectifs.value = data.data
  }

  return {
    rapportGlobal, absenteisme, masseSalariale, effectifs,
    loading, annee,
    fetchGlobal, fetchAbsenteisme, fetchMasseSalariale, fetchEffectifs,
  }
})