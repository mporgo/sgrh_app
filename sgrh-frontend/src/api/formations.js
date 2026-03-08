import api from './axios'

export const formationsApi = {
  getAll:        (params = {}) => api.get('/formations', { params }),
  getOne:        (id)          => api.get(`/formations/${id}`),
  create:        (data)        => api.post('/formations', data),
  update:        (id, data)    => api.put(`/formations/${id}`, data),
  delete:        (id)          => api.delete(`/formations/${id}`),
  inscrits:      (id)          => api.get(`/formations/${id}/inscrits`),
  inscrire:      (id)          => api.post(`/formations/${id}/inscrire`),
  desinscrire:   (id)          => api.post(`/formations/${id}/desinscrire`),
  mesFormations: ()            => api.get('/inscriptions/mes-formations'),
  validerInscription:  (id, data) => api.post(`/inscriptions/${id}/valider`, data),
  resultatsInscription:(id, data) => api.post(`/inscriptions/${id}/resultats`, data),
}