import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import adminService from '../services/adminService'

// 전역 상태 (단일 인스턴스)
const user = ref(null)
const isAuthenticated = computed(() => !!user.value)

export function useAuth() {
  const router = useRouter()

  /**
   * 로그인
   */
  const login = async (credentials) => {
    try {
      const response = await adminService.login(credentials)

      if (response.data.success) {
        // 세션 토큰 저장 (있는 경우)
        if (response.data.token) {
          sessionStorage.setItem('auth_token', response.data.token)
        }

        // 사용자 정보 저장
        user.value = response.data.user

        return { success: true }
      } else {
        return { success: false, error: response.data.error || '로그인 실패' }
      }
    } catch (error) {
      console.error('Login error:', error)
      return {
        success: false,
        error: error.response?.data?.error || '로그인 중 오류가 발생했습니다.'
      }
    }
  }

  /**
   * 로그아웃
   */
  const logout = async () => {
    try {
      await adminService.logout()
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      // 로컬 상태 초기화
      user.value = null
      sessionStorage.removeItem('auth_token')
      router.push('/admin/login')
    }
  }

  /**
   * 현재 사용자 정보 가져오기
   */
  const fetchUser = async () => {
    try {
      const response = await adminService.me()

      if (response.data.success) {
        user.value = response.data.user
        return true
      } else {
        user.value = null
        return false
      }
    } catch (error) {
      console.error('Fetch user error:', error)
      user.value = null
      return false
    }
  }

  /**
   * 인증 확인 (라우터 가드용)
   */
  const checkAuth = async () => {
    // 이미 로그인된 상태면 통과
    if (user.value) {
      return true
    }

    // 세션 토큰이 있으면 사용자 정보 가져오기
    if (sessionStorage.getItem('auth_token')) {
      return await fetchUser()
    }

    return false
  }

  return {
    user,
    isAuthenticated,
    login,
    logout,
    fetchUser,
    checkAuth
  }
}
