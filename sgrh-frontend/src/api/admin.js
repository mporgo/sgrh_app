import api from './axios'

export const adminApi = {
  // Utilisateurs
  getUsers:       (params = {}) => api.get('/admin/users', { params }),
  getUser:        (id)          => api.get(`/admin/users/${id}`),
  createUser:     (data)        => api.post('/admin/users', data),
  updateUser:     (id, data)    => api.put(`/admin/users/${id}`, data),
  toggleUser:     (id)          => api.post(`/admin/users/${id}/toggle`),
  resetPassword:  (id, data)    => api.post(`/admin/users/${id}/reset-password`, data),
  assignerRole:   (id, data)    => api.post(`/admin/users/${id}/assigner-role`, data),

  // Rôles
  getRoles:       ()            => api.get('/admin/roles'),

  // Logs
  getLogs:        (params = {}) => api.get('/admin/logs', { params }),

  // Système
  infosSysteme:   ()            => api.get('/admin/infos-systeme'),
}