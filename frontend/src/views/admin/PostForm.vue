<template>
  <div>
    <h1 class="text-3xl font-bold text-gray-800 mb-8">
      {{ isEditMode ? '게시글 수정' : '새 게시글 작성' }}
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

      <!-- 게시판 선택 -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          게시판 <span class="text-red-500">*</span>
        </label>
        <select
          v-model="formData.board_id"
          required
          :disabled="isEditMode"
          :class="[
            'w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-church-green-500',
            isEditMode ? 'bg-gray-100 cursor-not-allowed' : ''
          ]"
        >
          <option value="">게시판을 선택하세요</option>
          <option v-for="board in boards" :key="board.id" :value="board.id">
            {{ board.name }}
          </option>
        </select>
        <p v-if="isEditMode" class="mt-1 text-xs text-gray-500">
          게시판은 수정할 수 없습니다.
        </p>
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
          placeholder="제목을 입력하세요"
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
            placeholder="내용을 입력하세요"
            style="min-height: 300px;"
          />
        </div>
      </div>

      <!-- 작성자 -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          작성자
        </label>
        <input
          v-model="formData.authorName"
          type="text"
          readonly
          class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed"
          placeholder="작성자 이름"
        />
        <p class="mt-1 text-xs text-gray-500">
          로그인한 사용자 이름이 자동으로 설정됩니다.
        </p>
      </div>

      <!-- 공지사항 설정 -->
      <div class="flex items-center">
        <input
          id="isNotice"
          v-model="formData.isNotice"
          type="checkbox"
          class="w-4 h-4 text-church-green-500 border-gray-300 rounded focus:ring-church-green-500"
        />
        <label for="isNotice" class="ml-2 text-sm text-gray-700">
          공지사항으로 등록
        </label>
      </div>

      <!-- 게시 설정 -->
      <div class="flex items-center">
        <input
          id="isPublished"
          v-model="formData.isPublished"
          type="checkbox"
          class="w-4 h-4 text-church-green-500 border-gray-300 rounded focus:ring-church-green-500"
        />
        <label for="isPublished" class="ml-2 text-sm text-gray-700">
          즉시 게시
        </label>
      </div>

      <!-- 첨부파일 (수정 모드) -->
      <div v-if="isEditMode && existingAttachments.length > 0" class="space-y-2">
        <label class="block text-sm font-medium text-gray-700">
          기존 첨부파일
        </label>
        <div class="bg-gray-50 rounded-lg p-4 space-y-2">
          <div
            v-for="attachment in existingAttachments"
            :key="attachment.id"
            class="flex items-center justify-between py-2 px-3 bg-white rounded border"
          >
            <div class="flex items-center space-x-3">
              <span class="text-gray-500">📎</span>
              <div>
                <p class="text-sm font-medium text-gray-900">{{ attachment.originalName }}</p>
                <p class="text-xs text-gray-500">
                  {{ formatFileSize(attachment.fileSize) }}
                  <span v-if="attachment.downloadCount > 0">
                    · {{ attachment.downloadCount }}회 다운로드
                  </span>
                </p>
              </div>
            </div>
            <button
              type="button"
              @click="deleteAttachment(attachment.id)"
              class="text-red-600 hover:text-red-700 text-sm"
            >
              삭제
            </button>
          </div>
        </div>
      </div>

      <!-- 파일 업로드 -->
      <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700">
          파일 첨부 {{ isEditMode ? '(추가)' : '' }}
        </label>
        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
          <input
            ref="fileInput"
            type="file"
            multiple
            @change="handleFileSelect"
            class="hidden"
            accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.txt"
          />

          <button
            type="button"
            @click="$refs.fileInput.click()"
            class="text-church-green-500 hover:text-church-green-600 font-medium"
          >
            📎 파일 선택
          </button>
          <p class="text-xs text-gray-500 mt-2">
            이미지, 문서, 압축 파일 등 (최대 10MB)
          </p>

          <!-- 선택된 파일 목록 -->
          <div v-if="selectedFiles.length > 0" class="mt-4 space-y-2">
            <div
              v-for="(file, index) in selectedFiles"
              :key="index"
              class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded text-left"
            >
              <div class="flex items-center space-x-2">
                <span class="text-gray-500">📄</span>
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ file.name }}</p>
                  <p class="text-xs text-gray-500">{{ formatFileSize(file.size) }}</p>
                </div>
              </div>
              <button
                type="button"
                @click="removeSelectedFile(index)"
                class="text-red-600 hover:text-red-700 text-sm"
              >
                제거
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- 버튼 -->
      <div class="flex justify-between items-center pt-6 border-t">
        <router-link
          to="/admin/posts"
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
import adminService from '../../services/adminService'
import boardService from '../../services/boardService'
import { useAuth } from '../../composables/useAuth'

const route = useRoute()
const router = useRouter()
const { user } = useAuth()

const isEditMode = computed(() => !!route.params.id)
const postId = computed(() => route.params.id)

const isLoading = ref(true)
const isSaving = ref(false)
const errorMessage = ref('')

const boards = ref([])
const formData = ref({
  board_id: '',
  title: '',
  content: '',
  authorName: '',
  isNotice: false,
  isPublished: true
})

// 파일 관련
const selectedFiles = ref([])
const existingAttachments = ref([])
const fileInput = ref(null)

// Quill 에디터 옵션
const editorOptions = {
  modules: {
    toolbar: [
      [{ 'header': [1, 2, 3, false] }],
      ['bold', 'italic', 'underline', 'strike'],
      [{ 'color': [] }, { 'background': [] }],
      [{ 'list': 'ordered' }, { 'list': 'bullet' }],
      [{ 'align': [] }],
      ['link', 'image'],
      ['clean']
    ]
  },
  placeholder: '내용을 입력하세요'
}

// 게시판 목록 로드
const loadBoards = async () => {
  try {
    const response = await boardService.getAllBoards()
    if (response.data.success) {
      boards.value = response.data.boards
    }
  } catch (error) {
    console.error('Failed to load boards:', error)
  }
}

// 게시글 로드 (수정 모드)
const loadPost = async () => {
  if (!isEditMode.value) return

  try {
    const response = await boardService.getPost(postId.value)
    if (response.data.success) {
      const post = response.data.post
      formData.value = {
        board_id: post.board.id,
        title: post.title,
        content: post.content,
        authorName: post.authorName,
        isNotice: post.isNotice,
        isPublished: true
      }

      // 기존 첨부파일 로드
      existingAttachments.value = post.attachments || []
    }
  } catch (error) {
    console.error('Failed to load post:', error)
    errorMessage.value = '게시글을 불러오는데 실패했습니다.'
  }
}

// 파일 선택 핸들러
const handleFileSelect = (event) => {
  const files = Array.from(event.target.files)

  // 파일 크기 검증 (10MB)
  const maxSize = 10 * 1024 * 1024
  const validFiles = files.filter(file => {
    if (file.size > maxSize) {
      alert(`${file.name}은(는) 10MB를 초과합니다.`)
      return false
    }
    return true
  })

  selectedFiles.value = [...selectedFiles.value, ...validFiles]

  // 파일 입력 초기화 (같은 파일 재선택 가능)
  event.target.value = ''
}

// 선택된 파일 제거
const removeSelectedFile = (index) => {
  selectedFiles.value.splice(index, 1)
}

// 기존 첨부파일 삭제
const deleteAttachment = async (attachmentId) => {
  if (!confirm('첨부파일을 삭제하시겠습니까?')) return

  try {
    const response = await adminService.deleteAttachment(attachmentId)

    if (response.data.success) {
      existingAttachments.value = existingAttachments.value.filter(
        att => att.id !== attachmentId
      )
      alert('첨부파일이 삭제되었습니다.')
    } else {
      alert(response.data.error || '삭제에 실패했습니다.')
    }
  } catch (error) {
    console.error('Delete attachment error:', error)
    alert('첨부파일 삭제 중 오류가 발생했습니다.')
  }
}

// 파일 크기 포맷팅
const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

// 폼 제출
const handleSubmit = async () => {
  isSaving.value = true
  errorMessage.value = ''

  try {
    let response
    let createdPostId = null

    // 1. 게시글 저장
    if (isEditMode.value) {
      response = await adminService.updatePost(postId.value, formData.value)
      createdPostId = postId.value
    } else {
      response = await adminService.createPost(formData.value)
      createdPostId = response.data.post?.id
    }

    if (!response.data.success) {
      errorMessage.value = response.data.error || '저장에 실패했습니다.'
      return
    }

    // 2. 파일 업로드 (선택된 파일이 있는 경우)
    if (selectedFiles.value.length > 0 && createdPostId) {
      const formData = new FormData()
      formData.append('postId', createdPostId)

      selectedFiles.value.forEach((file) => {
        formData.append('files[]', file)
      })

      try {
        const uploadResponse = await adminService.uploadFile(formData)

        if (!uploadResponse.data.success) {
          console.error('File upload failed:', uploadResponse.data.error)
          alert(`게시글은 저장되었으나 파일 업로드에 실패했습니다: ${uploadResponse.data.error}`)
        }
      } catch (uploadError) {
        console.error('Upload error:', uploadError)
        alert('게시글은 저장되었으나 파일 업로드 중 오류가 발생했습니다.')
      }
    }

    // 3. 완료
    alert(isEditMode.value ? '게시글이 수정되었습니다.' : '게시글이 작성되었습니다.')
    router.push('/admin/posts')

  } catch (error) {
    console.error('Save error:', error)
    errorMessage.value = error.response?.data?.error || '저장 중 오류가 발생했습니다.'
  } finally {
    isSaving.value = false
  }
}

onMounted(async () => {
  await loadBoards()
  await loadPost()

  // 새 글 작성 시 로그인 사용자의 이름을 작성자로 자동 설정
  if (!isEditMode.value && user.value) {
    formData.value.authorName = user.value.displayName || user.value.username
  }

  isLoading.value = false
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
  min-height: 300px;
}

.quill-editor-container :deep(.ql-editor) {
  min-height: 300px;
  padding: 1rem;
}

.quill-editor-container :deep(.ql-editor.ql-blank::before) {
  color: #9ca3af;
  font-style: normal;
}
</style>
