import api from './axios'

export const rapportsApi = {
  global:        (params = {}) => api.get('/rapports/global', { params }),
  absenteisme:   (params = {}) => api.get('/rapports/absenteisme', { params }),
  masseSalariale:(params = {}) => api.get('/rapports/masse-salariale', { params }),
  effectifs:     (params = {}) => api.get('/rapports/effectifs', { params }),
}