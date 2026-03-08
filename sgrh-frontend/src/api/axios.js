import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  withCredentials: true, // pour Sanctum SPA
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// ── Intercepteur requête : injecter le token ──────────────────────────────────
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// ── Intercepteur réponse : gérer les erreurs globalement ─────────────────────
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status

    if (status === 401) {
      // Token expiré ou invalide → déconnexion automatique
      localStorage.removeItem('token')
      window.location.href = '/login'
    }

    if (status === 403) {
      window.location.href = '/403'
    }

    return Promise.reject(error)
  }
)

export default api