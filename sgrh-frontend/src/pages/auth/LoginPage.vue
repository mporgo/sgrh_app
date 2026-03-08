<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-700 to-primary-900 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8">

      <!-- Logo / Titre -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-primary-50 rounded-2xl mb-4">
          <svg class="w-8 h-8 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857
                 M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857
                 m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-800">SGRH</h1>
        <p class="text-sm text-gray-500 mt-1">Système de Gestion des Ressources Humaines</p>
      </div>

      <!-- Formulaire -->
      <form @submit.prevent="handleLogin" class="space-y-5">

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Adresse email
          </label>
          <input
            v-model="form.email"
            type="email"
            autocomplete="email"
            placeholder="votre@email.com"
            class="input-field"
            :class="{ 'border-red-400 focus:ring-red-400': errors.email }"
          />
          <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Mot de passe
          </label>
          <div class="relative">
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              autocomplete="current-password"
              placeholder="••••••••"
              class="input-field pr-10"
              :class="{ 'border-red-400 focus:ring-red-400': errors.password }"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
              <EyeIcon v-if="!showPassword" class="w-5 h-5" />
              <EyeSlashIcon v-else class="w-5 h-5" />
            </button>
          </div>
          <p v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password }}</p>
        </div>

        <!-- Message d'erreur API -->
        <div v-if="apiError" class="bg-red-50 border border-red-200 rounded-lg p-3">
          <p class="text-red-600 text-sm text-center">{{ apiError }}</p>
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="btn-primary w-full flex items-center justify-center gap-2 py-2.5"
        >
          <span v-if="loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
          <span>{{ loading ? 'Connexion...' : 'Se connecter' }}</span>
        </button>

      </form>

      <p class="text-center text-xs text-gray-400 mt-6">
        ESI – Université Nazi Boni &copy; {{ new Date().getFullYear() }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'

const router    = useRouter()
const toast     = useToast()
const authStore = useAuthStore()

const form = reactive({ email: '', password: '' })
const errors = reactive({ email: '', password: '' })
const loading      = ref(false)
const showPassword = ref(false)
const apiError     = ref('')

function validate() {
  errors.email    = ''
  errors.password = ''
  let valid = true

  if (!form.email) {
    errors.email = 'L\'email est obligatoire.'; valid = false
  } else if (!/\S+@\S+\.\S+/.test(form.email)) {
    errors.email = 'Format d\'email invalide.'; valid = false
  }
  if (!form.password) {
    errors.password = 'Le mot de passe est obligatoire.'; valid = false
  }
  return valid
}

async function handleLogin() {
  if (!validate()) return
  loading.value  = true
  apiError.value = ''

  try {
    await authStore.login(form)
    toast.success(`Bienvenue, ${authStore.userName} !`)
    router.push('/dashboard')
  } catch (err) {
    apiError.value = err.response?.data?.message || 'Erreur de connexion.'
  } finally {
    loading.value = false
  }
}
</script>