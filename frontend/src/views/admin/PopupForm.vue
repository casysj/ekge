<template>
  <div>
    <h1 class="text-3xl font-bold text-gray-800 mb-8">
      {{ isEditMode ? '팝업 수정' : '새 팝업 작성' }}
    </h1>

    <!-- 로딩 상태 -->
    <div v-if="isLoading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-church-green-500 border-t-transparent"></div>
      <p class="mt-4 text-gray-600">로딩 중...</p>
    </div>

    <!-- 폼 -->
    <form v-else @submit.prevent="handleSubmit" class="bg-white rounded-lg shadow p-6 space-y-6">
      <!-- 에러 메시지 -->
      <div v-if="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
        {{ errorMessage }}
      </div>

      <!-- 제목 -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          제목 <span class="text-red-500">*</span>
        </label>
        <input
          v-model="formData.title"
          type="text"
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-church-green-500"
          placeholder="팝업 제목을 입력하세요"
        />
      </div>

      <!-- 내용 -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          내용 <span class="text-red-500">*</span>
        </label>
        <div class="quill-editor-container">
          <QuillEditor
            v-model:content="formData.content"
            contentType="html"
            :options="editorOptions"
            theme="snow"
            placeholder="팝업 내용을 입력하세요"
            style="min-height: 200px;"
          />
        </div>
      </div>

      <!-- 표시 기간 -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            시작 일시
          </label>
          <input
            v-model="formData.startDate"
            type="datetime-local"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-church-green-500"
          />
          <p class="mt-1 text-xs text-gray-500">
            비워두면 즉시 시작
          </p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            종료 일시
          </label>
          <input
            v-model="formData.endDate"
            type="datetime-local"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-church-green-500"
          />
          <p class="mt-1 text-xs text-gray-500">
            비워두면 무기한
          </p>
        </div>
      </div>

      <!-- 활성화 설정 -->
      <div class="flex items-center">
        <input
          id="isActive"
          v-model="formData.isActive"
          type="checkbox"
          class="w-4 h-4 text-church-green-500 border-gray-300 rounded focus:ring-church-green-500"
        />
        <label for="isActive" class="ml-2 text-sm text-gray-700">
          즉시 활성화
        </label>
      </div>
      <p class="text-xs text-gray-500 -mt-4">
        활성화하면 기존 활성 팝업은 자동으로 비활성화됩니다.
      </p>

      <!-- 미리보기 -->
      <div v-if="formData.content" class="border-t pt-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          미리보기
        </label>
        <div class="border rounded-lg p-4 bg-gray-50">
          <h3 class="font-bold text-lg mb-2">{{ formData.title || '(제목 없음)' }}</h3>
          <div class="prose prose-sm max-w-none" v-html="formData.content"></div>
        </div>
      </div>

      <!-- 버튼 -->
      <div class="flex justify-between items-center pt-6 border-t">
        <router-link
          to="/admin/popups"
          class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
        >
          취소
        </router-link>

        <button
          type="submit"
          :disabled="isSaving"
          class="btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="isSaving">저장 중...</span>
          <span v-else>{{ isEditMode ? '수정' : '작성' }} 완료</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'
import popupService from '../../services/popupService'

const route = useRoute()
const router = useRouter()

const isEditMode = computed(() => !!route.params.id)
const popupId = computed(() => route.params.id)

const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')

const formData = ref({
  title: '',
  content: '',
  startDate: '',
  endDate: '',
  isActive: false
})

// Quill 에디터 옵션
const editorOptions = {
  modules: {
    toolbar: [
      [{ 'header': [1, 2, 3, false] }],
      ['bold', 'italic', 'underline'],
      [{ 'color': [] }, { 'background': [] }],
      [{ 'list': 'ordered' }, { 'list': 'bullet' }],
      [{ 'align': [] }],
      ['link'],
      ['clean']
    ]
  },
  placeholder: '팝업 내용을 입력하세요'
}

// 팝업 로드 (수정 모드)
const loadPopup = async () => {
  if (!isEditMode.value) return

  isLoading.value = true

  try {
    const response = await popupService.getPopup(popupId.value)
    if (response.data.success) {
      const popup = response.data.popup
      formData.value = {
        title: popup.title,
        content: popup.content,
        startDate: popup.startDate ? formatDateTimeLocal(popup.startDate) : '',
        endDate: popup.endDate ? formatDateTimeLocal(popup.endDate) : '',
        isActive: popup.isActive
      }
    }
  } catch (error) {
    console.error('Failed to load popup:', error)
    errorMessage.value = '팝업을 불러오는데 실패했습니다.'
  } finally {
    isLoading.value = false
  }
}

// datetime-local 형식으로 변환
const formatDateTimeLocal = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  const hours = String(date.getHours()).padStart(2, '0')
  const minutes = String(date.getMinutes()).padStart(2, '0')
  return `${year}-${month}-${day}T${hours}:${minutes}`
}

// 폼 제출
const handleSubmit = async () => {
  if (!formData.value.title || !formData.value.content) {
    errorMessage.value = '제목과 내용은 필수입니다.'
    return
  }

  isSaving.value = true
  errorMessage.value = ''

  try {
    let response

    const data = {
      title: formData.value.title,
      content: formData.value.content,
      startDate: formData.value.startDate || null,
      endDate: formData.value.endDate || null,
      isActive: formData.value.isActive
    }

    if (isEditMode.value) {
      response = await popupService.updatePopup(popupId.value, data)
    } else {
      response = await popupService.createPopup(data)
    }

    if (response.data.success) {
      alert(isEditMode.value ? '팝업이 수정되었습니다.' : '팝업이 작성되었습니다.')
      router.push('/admin/popups')
    } else {
      errorMessage.value = response.data.message || '저장에 실패했습니다.'
    }
  } catch (error) {
    console.error('Save error:', error)
    errorMessage.value = error.response?.data?.message || '저장 중 오류가 발생했습니다.'
  } finally {
    isSaving.value = false
  }
}

onMounted(() => {
  loadPopup()
})
</script>

<style scoped>
.quill-editor-container {
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  overflow: hidden;
}

.quill-editor-container :deep(.ql-toolbar) {
  border: none;
  border-bottom: 1px solid #d1d5db;
  background-color: #f9fafb;
}

.quill-editor-container :deep(.ql-container) {
  border: none;
  font-size: 1rem;
  min-height: 200px;
}

.quill-editor-container :deep(.ql-editor) {
  min-height: 200px;
  padding: 1rem;
}

.quill-editor-container :deep(.ql-editor.ql-blank::before) {
  color: #9ca3af;
  font-style: normal;
}
</style>
