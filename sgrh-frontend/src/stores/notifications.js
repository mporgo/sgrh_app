import { defineStore } from 'pinia'
import { ref } from 'vue'
import { notificationsApi } from '@/api/notifications'

export const useNotificationsStore = defineStore('notifications', () => {

  const notifications = ref([])
  const nonLues       = ref(0)
  const loading       = ref(false)
  const meta          = ref({ total: 0, current_page: 1, last_page: 1 })

  async function fetchNotifications(page = 1) {
    loading.value = true
    try {
      const { data } = await notificationsApi.getAll({ page, per_page: 20 })
      notifications.value = data.data
      meta.value          = data.meta
      nonLues.value       = data.meta.non_lues
    } finally {
      loading.value = false
    }
  }

  async function fetchNonLues() {
    const { data } = await notificationsApi.nonLues()
    nonLues.value = data.count
  }

  async function marquerLue(id) {
    await notificationsApi.marquerLue(id)
    const n = notifications.value.find(n => n.id === id)
    if (n) { n.lu = true; nonLues.value = Math.max(0, nonLues.value - 1) }
  }

  async function toutMarquerLu() {
    await notificationsApi.toutMarquerLu()
    notifications.value.forEach(n => n.lu = true)
    nonLues.value = 0
  }

  async function deleteNotification(id) {
    await notificationsApi.delete(id)
    notifications.value = notifications.value.filter(n => n.id !== id)
  }

  return {
    notifications, nonLues, loading, meta,
    fetchNotifications, fetchNonLues,
    marquerLue, toutMarquerLu, deleteNotification,
  }
})