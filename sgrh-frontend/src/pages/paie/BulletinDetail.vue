<template>
  <div class="space-y-5" v-if="p">

    <!-- En-tête bulletin -->
    <div class="flex items-center justify-between p-4 bg-primary-700 text-white rounded-xl">
      <div>
        <p class="text-lg font-bold">BULLETIN DE PAIE</p>
        <p class="text-sm opacity-80">{{ p.periode }}</p>
        <p class="text-xs opacity-60 font-mono mt-1">{{ p.reference }}</p>
      </div>
      <StatutPaieBadge :statut="p.statut" />
    </div>

    <!-- Infos employé -->
    <div class="grid grid-cols-2 gap-3 text-sm">
      <InfoRow label="Employé"      :value="p.employe?.user?.name" />
      <InfoRow label="Matricule"    :value="p.employe?.matricule" />
      <InfoRow label="Poste"        :value="p.employe?.poste" />
      <InfoRow label="Département"  :value="p.employe?.departement" />
    </div>

    <!-- Récap calcul -->
    <RecapBulletin
      :calcul="p"
      :elements="p.elements ?? []"
    />

    <!-- Date de paiement -->
    <div v-if="p.date_paiement" class="text-sm text-green-700 flex items-center gap-2">
      <CheckCircleIcon class="w-4 h-4" />
      Payé le {{ formatDate(p.date_paiement) }}
    </div>

    <!-- Notes -->
    <div v-if="p.notes" class="text-sm text-gray-600 bg-gray-50 rounded-lg p-3">
      <span class="font-medium text-gray-700">Notes : </span>{{ p.notes }}
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue'
import InfoRow        from '@/components/common/InfoRow.vue'
import RecapBulletin  from './RecapBulletin.vue'
import StatutPaieBadge from './StatutPaieBadge.vue'
import { CheckCircleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({ paie: { type: Object, required: true } })

const p = computed(() => props.paie)

function formatDate(d) {
  return d ? new Date(d).toLocaleDateString('fr-FR') : '—'
}
</script>