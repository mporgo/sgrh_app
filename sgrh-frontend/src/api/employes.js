import api from './axios'

export const employesApi = {
  // Liste avec filtres
  getAll: (params = {}) => api.get('/employes', { params }),

  // Détail
  getOne: (id) => api.get(`/employes/${id}`),

  // Créer
  create: (data) => api.post('/employes', data),

  // Modifier
  update: (id, data) => api.put(`/employes/${id}`, data),

  // Désactiver
  delete: (id) => api.delete(`/employes/${id}`),
}

export const departementsApi = {
  getAll: () => api.get('/departements'),
  create: (data) => api.post('/departements', data),
  update: (id, data) => api.put(`/departements/${id}`, data),
}

export const postesApi = {
  getAll: (params = {}) => api.get('/postes', { params }),
  create: (data) => api.post('/postes', data),
  update: (id, data) => api.put(`/postes/${id}`, data),
}