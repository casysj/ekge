import apiClient from './api'

/**
 * 관리자 API 서비스
 */
const adminService = {
  /**
   * 로그인
   * @param {object} credentials - { username, password }
   * @returns {Promise}
   */
  login(credentials) {
    return apiClient.post('/admin/login', credentials)
  },

  /**
   * 로그아웃
   * @returns {Promise}
   */
  logout() {
    return apiClient.post('/admin/logout')
  },

  /**
   * 현재 로그인한 사용자 정보
   * @returns {Promise}
   */
  me() {
    return apiClient.get('/admin/me')
  },

  /**
   * 통계 조회
   * @returns {Promise}
   */
  stats() {
    return apiClient.get('/admin/stats')
  },

  /**
   * 게시글 작성
   * @param {object} postData - 게시글 데이터
   * @returns {Promise}
   */
  createPost(postData) {
    return apiClient.post('/admin/posts', postData)
  },

  /**
   * 게시글 수정
   * @param {number} postId - 게시글 ID
   * @param {object} postData - 게시글 데이터
   * @returns {Promise}
   */
  updatePost(postId, postData) {
    return apiClient.put(`/admin/posts/${postId}`, postData)
  },

  /**
   * 게시글 삭제
   * @param {number} postId - 게시글 ID
   * @returns {Promise}
   */
  deletePost(postId) {
    return apiClient.delete(`/admin/posts/${postId}`)
  },

  /**
   * 파일 업로드
   * @param {FormData} formData - 파일 데이터
   * @returns {Promise}
   */
  uploadFile(formData) {
    return apiClient.post('/admin/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  }
}

export default adminService
