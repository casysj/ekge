<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <!-- 로고 & 타이틀 -->
      <div class="text-center">
        <h2 class="text-4xl font-bold text-church-green-500 mb-2">
          에센 한인교회
        </h2>
        <p class="text-xl text-gray-600">관리자 로그인</p>
      </div>

      <!-- 로그인 폼 -->
      <div class="bg-white rounded-lg shadow-md p-8">
        <form @submit.prevent="handleLogin" class="space-y-6">
          <!-- 에러 메시지 -->
          <div v-if="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            {{ errorMessage }}
          </div>

          <!-- 아이디 -->
          <div>
            <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
              아이디
            </label>
            <input
              id="username"
              v-model="credentials.username"
              type="text"
              required
              autocomplete="username"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-church-green-500 focus:border-transparent"
              placeholder="아이디를 입력하세요"
            />
          </div>

          <!-- 비밀번호 -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
              비밀번호
            </label>
            <input
              id="password"
              v-model="credentials.password"
              type="password"
              required
              autocomplete="current-password"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-church-green-500 focus:border-transparent"
              placeholder="비밀번호를 입력하세요"
            />
          </div>

          <!-- 로그인 버튼 -->
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full btn-primary py-3 text-lg disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="isLoading">로그인 중...</span>
            <span v-else>로그인</span>
          </button>
        </form>

        <!-- 홈으로 돌아가기 -->
        <div class="mt-6 text-center">
          <router-link to="/" class="text-church-green-500 hover:text-church-green-600 text-sm">
            ← 홈으로 돌아가기
          </router-link>
        </div>
      </div>

      <!-- 안내 메시지 -->
      <div class="text-center text-sm text-gray-500">
        <p>관리자 계정이 필요합니다</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../../composables/useAuth'

const router = useRouter()
const { login } = useAuth()

const credentials = ref({
  username: '',
  password: ''
})

const isLoading = ref(false)
const errorMessage = ref('')

const handleLogin = async () => {
  isLoading.value = true
  errorMessage.value = ''

  const result = await login(credentials.value)

  if (result.success) {
    // 로그인 성공 - 대시보드로 이동
    router.push('/admin/dashboard')
  } else {
    // 로그인 실패
    errorMessage.value = result.error
  }

  isLoading.value = false
}
</script>
