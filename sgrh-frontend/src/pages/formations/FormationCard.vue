<template>
  <div class="card hover:shadow-md transition-shadow duration-200 flex flex-col gap-4">

    <!-- Header -->
    <div class="flex items-start justify-between gap-3">
      <div class="flex-1">
        <h3 class="font-semibold text-gray-800 text-base leading-snug">{{ f.titre }}</h3>
        <p class="text-xs text-gray-500 mt-1">{{ f.formateur ?? 'Formateur non précisé' }}</p>
      </div>
      <TypeBadge :type="f.type" />
    </div>

    <!-- Description -->
    <p v-if="f.description" class="text-sm text-gray-600 line-clamp-2">{{ f.description }}</p>

    <!-- Infos clés -->
    <div class="grid grid-cols-2 gap-2 text-xs text-gray-500">
      <div class="flex items-center gap-1.5">
        <CalendarDaysIcon class="w-3.5 h-3.5" />
        <span>{{ formatDate(f.date_debut) }} → {{ formatDate(f.date_fin) }}</span>
      </div>
      <div class="flex items-center gap-1.5">
        <ClockIcon class="w-3.5 h-3.5" />
        <span>{{ f.duree_heures }}h</span>
      </div>
      <div class="flex items-center gap-1.5">
        <MapPinIcon class="w-3.5 h-3.5" />
        <span>{{ f.lieu ?? f.lien_elearning ? 'En ligne' : '—' }}</span>
      </div>
      <div class="flex items-center gap-1.5">
        <UsersIcon class="w-3.5 h-3.5" />
        <span>
          {{ f.nb_inscrits ?? 0 }} inscrit(s)
          <template v-if="f.places_max">
            / {{ f.places_max }} places
          </template>
          <template v-else>· Places illimitées</template>
        </span>
      </div>
    </div>

    <!-- Coût -->
    <div v-if="f.cout > 0" class="text-xs font-medium text-primary-600">
      💰 {{ formatCout(f.cout) }} FCFA
    </div>
    <div v-else class="text-xs font-medium text-green-600">✅ Gratuite</div>

    <!-- Barre de places -->
    <div v-if="f.places_max" class="space-y-1">
      <div class="flex justify-between text-xs">
        <span :class="f.complet ? 'text-red-600 font-semibold' : 'text-gray-500'">
          {{ f.complet ? '🔴 Complet' : `🟢 ${f.places_disponibles} place(s) disponible(s)` }}
        </span>
      </div>
      <div class="w-full bg-gray-100 rounded-full h-1.5">
        <div
          class="h-1.5 rounded-full transition-all"
          :class="f.complet ? 'bg-red-400' : 'bg-green-400'"
          :style="{ width: Math.min(100, ((f.nb_inscrits ?? 0) / f.places_max) * 100) + '%' }"
        />
      </div>
    </div>

    <!-- Footer : statut + actions -->
    <div class="flex items-center justify-between pt-2 border-t border-gray-100">
      <StatutFormationBadge :statut="f.statut" />

      <div class="flex gap-2">
        <!-- Bouton inscription (employé non encore inscrit) -->
        <button
          v-if="!monInscription && !f.complet && !['terminee','annulee'].includes(f.statut)"
          @click="$emit('inscrire', f)"
          class="btn-primary text-xs px-3 py-1.5"
        >
          S'inscrire
        </button>

        <!-- Statut de mon inscription -->
        <StatutFormationBadge
          v-if="monInscription"
          :statut="monInscription.statut"
        />

        <!-- Se désinscrire si en attente ou validée -->
        <button
          v-if="monInscription && ['en_attente','validee'].includes(monInscription.statut)"
          @click="$emit('desinscrire', f)"
          class="btn-secondary text-xs px-3 py-1.5"
        >
          Se désinscrire
        </button>

        <!-- Voir détails -->
        <button
          @click="$emit('voir', f)"
          class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition"
          title="Voir le détail"
        >
          <EyeIcon class="w-4 h-4" />
        </button>

        <!-- Modifier (RH/Admin) -->
        <button
          v-if="canManage"
          @click="$emit('modifier', f)"
          class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg transition"
          title="Modifier"
        >
          <PencilIcon class="w-4 h-4" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import TypeBadge            from './TypeBadge.vue'
import StatutFormationBadge from './StatutFormationBadge.vue'
import {
  CalendarDaysIcon, ClockIcon, MapPinIcon, UsersIcon, EyeIcon, PencilIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  formation: { type: Object, required: true },
})
defineEmits(['inscrire', 'desinscrire', 'voir', 'modifier'])

const authStore = useAuthStore()
const f = computed(() => props.formation)

const canManage = computed(() =>
  authStore.isAdmin || authStore.isRH
)

const monInscription = computed(() => f.value.mon_inscription ?? null)

function formatDate(d) {
  return d ? new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' }) : '—'
}

function formatCout(v) {
  return Number(v).toLocaleString('fr-FR')
}
</script>