<template>
  <Teleport to="body">
    <Transition name="fade">
      <div
        v-if="isVisible"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
        @click.self="closePopup"
      >
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full max-h-[80vh] overflow-hidden">
          <!-- 헤더 -->
          <div class="flex justify-between items-center px-6 py-4 border-b bg-church-green-500 text-white">
            <h2 class="text-lg font-bold">{{ popup?.title }}</h2>
            <button
              @click="closePopup"
              class="text-white hover:text-gray-200 text-xl font-bold"
            >
              &times;
            </button>
          </div>

          <!-- 내용 -->
          <div class="px-6 py-4 overflow-y-auto max-h-[50vh]">
            <div class="prose prose-sm max-w-none" v-html="popup?.content"></div>
          </div>

          <!-- 푸터 -->
          <div class="flex justify-between items-center px-6 py-4 border-t bg-gray-50">
            <label class="flex items-center text-sm text-gray-600 cursor-pointer">
              <input
                type="checkbox"
                v-model="dontShowToday"
                class="w-4 h-4 mr-2 text-church-green-500 border-gray-300 rounded focus:ring-church-green-500"
              />
              오늘 하루 보지 않기
            </label>
            <button
              @click="closePopup"
              class="px-4 py-2 bg-church-green-500 text-white rounded hover:bg-church-green-600"
            >
              닫기
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import popupService from '../services/popupService'

const isVisible = ref(false)
const popup = ref(null)
const dontShowToday = ref(false)

const STORAGE_KEY = 'popup_hidden_until'

// 오늘 날짜 끝 시간 (23:59:59) 반환
const getEndOfToday = () => {
  const now = new Date()
  return new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59, 999).getTime()
}

// 숨김 설정 확인
const shouldShowPopup = () => {
  const hiddenUntil = localStorage.getItem(STORAGE_KEY)
  if (!hiddenUntil) return true

  const hiddenUntilTime = parseInt(hiddenUntil, 10)
  const now = Date.now()

  // 저장된 시간이 지났으면 표시
  if (now > hiddenUntilTime) {
    localStorage.removeItem(STORAGE_KEY)
    return true
  }

  return false
}

// 팝업 닫기
const closePopup = () => {
  if (dontShowToday.value && popup.value) {
    // 오늘 하루 보지 않기 설정
    localStorage.setItem(STORAGE_KEY, getEndOfToday().toString())
  }
  isVisible.value = false
}

// 활성 팝업 불러오기
const loadActivePopup = async () => {
  // 숨김 설정 확인
  if (!shouldShowPopup()) return

  try {
    const response = await popupService.getActivePopup()

    if (response.data.success && response.data.popup) {
      popup.value = response.data.popup
      isVisible.value = true
    }
  } catch (error) {
    console.error('Failed to load active popup:', error)
  }
}

onMounted(() => {
  loadActivePopup()
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
