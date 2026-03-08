import api from './axios'

export const congesApi = {
  getAll:        (params = {}) => api.get('/conges', { params }),
  getOne:        (id)          => api.get(`/conges/${id}`),
  create:        (data)        => api.post('/conges', data),
  traiter:       (id, data)    => api.post(`/conges/${id}/traiter`, data),
  annuler:       (id)          => api.post(`/conges/${id}/annuler`),
  mesSoldes:     ()            => api.get('/conges/mes-soldes'),
  typeConges:    ()            => api.get('/conges/types'),
  calendrier:    (params = {}) => api.get('/conges/calendrier', { params }),
}