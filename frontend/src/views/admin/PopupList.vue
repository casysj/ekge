<template>
  <div>
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-3xl font-bold text-gray-800">팝업 관리</h1>
      <router-link to="/admin/popups/create" class="btn-primary">
        + 새 팝업 작성
      </router-link>
    </div>

    <!-- 설명 -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
      <p class="text-sm text-blue-700">
        <strong>안내:</strong> 활성화된 팝업은 동시에 1개만 가능합니다.
        새로운 팝업을 활성화하면 기존 활성 팝업은 자동으로 비활성화됩니다.
      </p>
    </div>

    <!-- 로딩 상태 -->
    <div v-if="isLoading" class="bg-white rounded-lg shadow p-12 text-center">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-church-green-500 border-t-transparent"></div>
      <p class="mt-4 text-gray-600">로딩 중...</p>
    </div>

    <!-- 팝업 목록 -->
    <div v-else class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">상태</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">제목</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">표시 기간</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">작성일</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">관리</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="popups.length === 0">
            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
              팝업이 없습니다. 새 팝업을 작성해보세요.
            </td>
          </tr>
          <tr v-for="popup in popups" :key="popup.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 text-sm text-gray-900">{{ popup.id }}</td>
            <td class="px-6 py-4">
              <span
                :class="[
                  'px-2 py-1 text-xs rounded-full font-medium',
                  popup.isActive
                    ? 'bg-green-100 text-green-700'
                    : 'bg-gray-100 text-gray-600'
                ]"
              >
                {{ popup.isActive ? '활성' : '비활성' }}
              </span>
              <span
                v-if="popup.isActive && !isInDateRange(popup)"
                class="ml-2 px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700"
              >
                기간외
              </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900">
              {{ popup.title }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-600">
              <div v-if="popup.startDate || popup.endDate">
                <div v-if="popup.startDate">시작: {{ formatDate(popup.startDate) }}</div>
                <div v-if="popup.endDate">종료: {{ formatDate(popup.endDate) }}</div>
              </div>
              <span v-else class="text-gray-400">기간 제한 없음</span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-600">
              {{ formatDate(popup.createdAt) }}
            </td>
            <td class="px-6 py-4 text-sm space-x-2">
              <button
                @click="togglePopup(popup)"
                :class="[
                  'px-3 py-1 text-xs rounded',
                  popup.isActive
                    ? 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                    : 'bg-green-100 text-green-700 hover:bg-green-200'
                ]"
              >
                {{ popup.isActive ? '비활성화' : '활성화' }}
              </button>
              <router-link
                :to="`/admin/popups/${popup.id}/edit`"
                class="text-blue-600 hover:text-blue-700"
              >
                수정
              </router-link>
              <button
                @click="deletePopup(popup.id)"
                class="text-red-600 hover:text-red-700"
              >
                삭제
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import popupService from '../../services/popupService'

const isLoading = ref(true)
const popups = ref([])

// 팝업 목록 로드
const loadPopups = async () => {
  isLoading.value = true

  try {
    const response = await popupService.getAllPopups()
    if (response.data.success) {
      popups.value = response.data.popups
    }
  } catch (error) {
    console.error('Failed to load popups:', error)
  } finally {
    isLoading.value = false
  }
}

// 팝업 활성화/비활성화 토글
const togglePopup = async (popup) => {
  const action = popup.isActive ? '비활성화' : '활성화'
  if (!confirm(`이 팝업을 ${action}하시겠습니까?`)) return

  try {
    const response = await popupService.togglePopup(popup.id)

    if (response.data.success) {
      alert(response.data.message)
      loadPopups()
    } else {
      alert(response.data.message || `${action}에 실패했습니다.`)
    }
  } catch (error) {
    console.error('Toggle error:', error)
    alert(`${action} 중 오류가 발생했습니다.`)
  }
}

// 팝업 삭제
const deletePopup = async (popupId) => {
  if (!confirm('정말 삭제하시겠습니까?')) return

  try {
    const response = await popupService.deletePopup(popupId)

    if (response.data.success) {
      alert('삭제되었습니다.')
      loadPopups()
    } else {
      alert(response.data.message || '삭제에 실패했습니다.')
    }
  } catch (error) {
    console.error('Delete error:', error)
    alert('삭제 중 오류가 발생했습니다.')
  }
}

// 날짜 범위 내인지 확인
const isInDateRange = (popup) => {
  const now = new Date()

  if (popup.startDate && new Date(popup.startDate) > now) {
    return false
  }

  if (popup.endDate && new Date(popup.endDate) < now) {
    return false
  }

  return true
}

// 날짜 포맷팅
const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('ko-KR', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(() => {
  loadPopups()
})
</script>
