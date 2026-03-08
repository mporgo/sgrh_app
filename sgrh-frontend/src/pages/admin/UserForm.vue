<template>
  <form @submit.prevent="$emit('submit', form)" class="space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

      <!-- Nom -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet *</label>
        <input v-model="form.name" type="text" class="input-field" placeholder="Prénom Nom" />
        <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</p>
      </div>

      <!-- Email -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
        <input v-model="form.email" type="email" class="input-field" placeholder="email@sgrh.com" />
        <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</p>
      </div>

      <!-- Mot de passe -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Mot de passe {{ isEdit ? '(laisser vide = inchangé)' : '*' }}
        </label>
        <input v-model="form.password" type="password" class="input-field" placeholder="••••••••" />
        <p v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password }}</p>
      </div>

      <!-- Téléphone -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
        <input v-model="form.phone" type="tel" class="input-field" placeholder="+226 XX XX XX XX" />
      </div>

      <!-- Rôle -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Rôle *</label>
        <select v-model="form.role" class="input-field">
          <option value="">— Sélectionner un rôle —</option>
          <option v-for="r in roles" :key="r.name" :value="r.name">
            {{ r.name }}
          </option>
        </select>
        <p v-if="errors.role" class="text-red-500 text-xs mt-1">{{ errors.role }}</p>
      </div>

      <!-- Statut (édition uniquement) -->
      <div v-if="isEdit" class="flex items-center gap-3 pt-6">
        <input
          v-model="form.is_active"
          type="checkbox"
          id="is_active"
          class="w-4 h-4 accent-primary-500"
        />
        <label for="is_active" class="text-sm text-gray-700">Compte actif</label>
      </div>

    </div>
  </form>
</template>

<script setup>
import { reactive } from 'vue'

const props = defineProps({
  roles:   { type: Array,   default: () => [] },
  errors:  { type: Object,  default: () => ({}) },
  initial: { type: Object,  default: () => ({}) },
  isEdit:  { type: Boolean, default: false },
})

defineEmits(['submit'])

const form = reactive({
  name: '', email: '', password: '',
  phone: '', role: '', is_active: true,
  ...props.initial,
})

defineExpose({ form })
</script>