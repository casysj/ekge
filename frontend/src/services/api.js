import axios from 'axios'

// API Base URL 설정
const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8080/api'

// Axios 인스턴스 생성
const apiClient = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  withCredentials: true, // 세션 쿠키 전송 활성화
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// 요청 인터셉터 (인증 토큰 추가 등)
apiClient.interceptors.request.use(
  (config) => {
    // 세션 스토리지에서 토큰 가져오기
    const token = sessionStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// 응답 인터셉터 (에러 처리)
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response) {
      // 서버 응답 에러
      console.error('API Error:', error.response.status, error.response.data)

      // 401 Unauthorized - 로그인 페이지로 리다이렉트
      if (error.response.status === 401) {
        sessionStorage.removeItem('auth_token')
        window.location.href = '/admin/login'
      }
    } else if (error.request) {
      // 요청은 보냈지만 응답 없음
      console.error('No response from server')
    } else {
      // 요청 설정 중 에러
      console.error('Request setup error:', error.message)
    }
    return Promise.reject(error)
  }
)

export default apiClient
