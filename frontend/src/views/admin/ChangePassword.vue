<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-800 mb-6">비밀번호 변경</h1>

    <div class="max-w-md">
      <div class="bg-white rounded-lg shadow-md p-6">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- 성공 메시지 -->
          <div v-if="successMessage" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
            {{ successMessage }}
          </div>

          <!-- 에러 메시지 -->
          <div v-if="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            {{ errorMessage }}
          </div>

          <!-- 현재 비밀번호 -->
          <div>
            <label for="currentPassword" class="block text-sm font-medium text-gray-700 mb-2">
              현재 비밀번호
            </label>
            <input
              id="currentPassword"
              v-model="form.currentPassword"
              type="password"
              required
              autocomplete="current-password"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-church-green-500 focus:border-transparent"
              placeholder="현재 비밀번호를 입력하세요"
            />
          </div>

          <!-- 새 비밀번호 -->
          <div>
            <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-2">
              새 비밀번호
            </label>
            <input
              id="newPassword"
              v-model="form.newPassword"
              type="password"
              required
              autocomplete="new-password"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-church-green-500 focus:border-transparent"
              placeholder="새 비밀번호를 입력하세요 (최소 6자)"
            />
          </div>

          <!-- 새 비밀번호 확인 -->
          <div>
            <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-2">
              새 비밀번호 확인
            </label>
            <input
              id="confirmPassword"
              v-model="form.confirmPassword"
              type="password"
              required
              autocomplete="new-password"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-church-green-500 focus:border-transparent"
              placeholder="새 비밀번호를 다시 입력하세요"
            />
          </div>

          <!-- 변경 버튼 -->
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full btn-primary py-3 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="isLoading">변경 중...</span>
            <span v-else>비밀번호 변경</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import adminService from '../../services/adminService'

const form = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const handleSubmit = async () => {
  errorMessage.value = ''
  successMessage.value = ''

  if (form.value.newPassword.length < 6) {
    errorMessage.value = '새 비밀번호는 최소 6자 이상이어야 합니다.'
    return
  }

  if (form.value.newPassword !== form.value.confirmPassword) {
    errorMessage.value = '새 비밀번호가 일치하지 않습니다.'
    return
  }

  isLoading.value = true

  try {
    const response = await adminService.changePassword(form.value)

    if (response.data.success) {
      successMessage.value = response.data.message
      form.value = { currentPassword: '', newPassword: '', confirmPassword: '' }
    } else {
      errorMessage.value = response.data.message
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || '비밀번호 변경 중 오류가 발생했습니다.'
  }

  isLoading.value = false
}
</script>
