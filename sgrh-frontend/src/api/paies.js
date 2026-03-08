import api from './axios'

export const paiesApi = {
  getAll:       (params = {}) => api.get('/paies', { params }),
  getOne:       (id)          => api.get(`/paies/${id}`),
  create:       (data)        => api.post('/paies', data),
  update:       (id, data)    => api.put(`/paies/${id}`, data),
  delete:       (id)          => api.delete(`/paies/${id}`),
  valider:      (id)          => api.post(`/paies/${id}/valider`),
  marquerPaye:  (id, data)    => api.post(`/paies/${id}/marquer-paye`, data),
  apercu:       (data)        => api.post('/paies/apercu', data),
  stats:        (params = {}) => api.get('/paies/stats', { params }),
}