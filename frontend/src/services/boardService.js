import apiClient from './api'

/**
 * 게시판 API 서비스
 */
const boardService = {
  /**
   * 모든 게시판 목록 조회
   * @returns {Promise}
   */
  getAllBoards() {
    return apiClient.get('/boards')
  },

  /**
   * 특정 게시판의 게시글 목록 조회
   * @param {string} boardCode - 게시판 코드 (sermon, weekly, notice, gallery, free)
   * @param {object} params - 쿼리 파라미터 (page, limit, search 등)
   * @returns {Promise}
   */
  getBoardPosts(boardCode, params = {}) {
    return apiClient.get(`/boards/${boardCode}/posts`, { params })
  },

  /**
   * 게시글 상세 조회
   * @param {number} postId - 게시글 ID
   * @returns {Promise}
   */
  getPost(postId) {
    return apiClient.get(`/posts/${postId}`)
  }
}

export default boardService
