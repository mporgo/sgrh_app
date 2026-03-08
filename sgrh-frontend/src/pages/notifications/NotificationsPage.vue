<template>
  <div class="space-y-5 max-w-3xl mx-auto">

    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-xl font-bold text-gray-800">Notifications</h2>
        <p class="text-sm text-gray-500">
          <span v-if="store.nonLues > 0" class="text-primary-600 font-medium">
            {{ store.nonLues }} non lue(s)
          </span>
          <span v-else>Tout est à jour ✅</span>
        </p>
      </div>
      <button
        v-if="store.nonLues > 0"
        @click="handleToutMarquerLu"
        class="btn-secondary text-sm"
      >
        Tout marquer comme lu
      </button>
    </div>

    <!-- Filtres -->
    <div class="flex gap-3 flex-wrap">
      <button
        v-for="f in filtres"
        :key="f.value"
        @click="filtre = f.value; store.fetchNotifications(1)"
        :class="[
          'px-3 py-1.5 rounded-full text-xs font-medium transition',
          filtre === f.value
            ? 'bg-primary-600 text-white'
            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
        ]"
      >
        {{ f.label }}
      </button>
    </div>

    <!-- Liste -->
    <div v-if="store.loading" class="flex justify-center py-12">
      <div class="w-6 h-6 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" />
    </div>

    <div v-else-if="!store.notifications.length" class="text-center py-16 text-gray-400">
      <p class="text-4xl mb-3">🔔</p>
      <p>Aucune notification</p>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="notif in notifsFiltrees"
        :key="notif.id"
        :class="[
          'card flex items-start gap-4 cursor-pointer transition',
          !notif.lu ? 'border-l-4 border-primary-400 bg-primary-50/30' : ''
        ]"
        @click="handleClick(notif)"
      >
        <!-- Icône type -->
        <div :class="['w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-lg', typeConfig(notif.type).bg]">
          {{ typeConfig(notif.type).icon }}
        </div>

        <!-- Contenu -->
        <div class="flex-1 min-w-0">
          <div class="flex items-start justify-between gap-2">
            <p :class="['text-sm font-semibold', !notif.lu ? 'text-gray-900' : 'text-gray-700']">
              {{ notif.titre }}
            </p>
            <div class="flex items-center gap-2 shrink-0">
              <span v-if="!notif.lu" class="w-2 h-2 rounded-full bg-primary-500 shrink-0" />
              <button
                @click.stop="store.deleteNotification(notif.id)"
                class="p-1 text-gray-300 hover:text-red-400 transition"
              >
                <XMarkIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
          <p class="text-sm text-gray-600 mt-0.5">{{ notif.message }}</p>
          <p class="text-xs text-gray-400 mt-1.5">{{ notif.created_at }}</p>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <AppPagination :meta="store.meta" @change="store.fetchNotifications" />

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useNotificationsStore } from '@/stores/notifications'
import AppPagination from '@/components/common/AppPagination.vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

const store  = useNotificationsStore()
const router = useRouter()

const filtre = ref('')

const filtres = [
  { label: 'Toutes',      value: '' },
  { label: '📅 Congés',   value: 'conge' },
  { label: '💰 Paie',     value: 'paie' },
  { label: '⭐ Éval.',    value: 'evaluation' },
  { label: '🎓 Formation',value: 'formation' },
  { label: '⚠️ Alertes',  value: 'alerte' },
  { label: '⚙️ Système',  value: 'systeme' },
]

function typeConfig(type) {
  return {
    conge:      { icon: '📅', bg: 'bg-blue-100'   },
    paie:       { icon: '💰', bg: 'bg-green-100'  },
    evaluation: { icon: '⭐', bg: 'bg-yellow-100' },
    formation:  { icon: '🎓', bg: 'bg-purple-100' },
    alerte:     { icon: '⚠️', bg: 'bg-red-100'    },
    systeme:    { icon: '⚙️', bg: 'bg-gray-100'   },
  }[type] ?? { icon: '🔔', bg: 'bg-gray-100' }
}

const notifsFiltrees = computed(() => {
  if (!filtre.value) return store.notifications
  return store.notifications.filter(n => n.type === filtre.value)
})

async function handleClick(notif) {
  if (!notif.lu) await store.marquerLue(notif.id)
  if (notif.lien) router.push(notif.lien)
}

async function handleToutMarquerLu() {
  await store.toutMarquerLu()
}

onMounted(() => store.fetchNotifications())
</script>