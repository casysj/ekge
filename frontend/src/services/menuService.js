import apiClient from './api'

/**
 * 메뉴 API 서비스
 */
const menuService = {
  /**
   * 모든 메뉴 조회 (트리 구조)
   * @returns {Promise}
   */
  getAllMenus() {
    return apiClient.get('/menus')
  },

  /**
   * 특정 메뉴 상세 조회
   * @param {number} menuId - 메뉴 ID
   * @returns {Promise}
   */
  getMenu(menuId) {
    return apiClient.get(`/menus/${menuId}`)
  }
}

export default menuService
