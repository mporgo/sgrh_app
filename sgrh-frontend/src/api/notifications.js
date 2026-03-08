import api from './axios'

export const notificationsApi = {
  getAll:        (params = {}) => api.get('/notifications', { params }),
  nonLues:       ()            => api.get('/notifications/non-lues'),
  marquerLue:    (id)          => api.post(`/notifications/${id}/marquer-lue`),
  toutMarquerLu: ()            => api.post('/notifications/tout-marquer-lu'),
  delete:        (id)          => api.delete(`/notifications/${id}`),
}