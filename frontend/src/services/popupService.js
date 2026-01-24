import apiClient from './api'

/**
 * 팝업 API 서비스
 */
const popupService = {
  /**
   * 현재 활성 팝업 조회 (공개)
   * @returns {Promise}
   */
  getActivePopup() {
    return apiClient.get('/popup/active')
  },

  /**
   * 모든 팝업 목록 조회 (관리자)
   * @returns {Promise}
   */
  getAllPopups() {
    return apiClient.get('/admin/popups')
  },

  /**
   * 팝업 상세 조회 (관리자)
   * @param {number} popupId - 팝업 ID
   * @returns {Promise}
   */
  getPopup(popupId) {
    return apiClient.get(`/admin/popups/${popupId}`)
  },

  /**
   * 팝업 생성 (관리자)
   * @param {object} popupData - 팝업 데이터
   * @returns {Promise}
   */
  createPopup(popupData) {
    return apiClient.post('/admin/popups', popupData)
  },

  /**
   * 팝업 수정 (관리자)
   * @param {number} popupId - 팝업 ID
   * @param {object} popupData - 팝업 데이터
   * @returns {Promise}
   */
  updatePopup(popupId, popupData) {
    return apiClient.put(`/admin/popups/${popupId}`, popupData)
  },

  /**
   * 팝업 삭제 (관리자)
   * @param {number} popupId - 팝업 ID
   * @returns {Promise}
   */
  deletePopup(popupId) {
    return apiClient.delete(`/admin/popups/${popupId}`)
  },

  /**
   * 팝업 활성화/비활성화 토글 (관리자)
   * @param {number} popupId - 팝업 ID
   * @returns {Promise}
   */
  togglePopup(popupId) {
    return apiClient.post(`/admin/popups/${popupId}/toggle`)
  }
}

export default popupService
