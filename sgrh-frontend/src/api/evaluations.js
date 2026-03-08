import api from './axios'

export const evaluationsApi = {
  getAll:          (params = {}) => api.get('/evaluations', { params }),
  getOne:          (id)          => api.get(`/evaluations/${id}`),
  create:          (data)        => api.post('/evaluations', data),
  update:          (id, data)    => api.put(`/evaluations/${id}`, data),
  delete:          (id)          => api.delete(`/evaluations/${id}`),
  commenterEmploye:(id, data)    => api.post(`/evaluations/${id}/commenter-employe`, data),
  stats:           (params = {}) => api.get('/evaluations/stats', { params }),
}