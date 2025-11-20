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
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-church-green-500"
        >
          <option value="">게시판을 선택하세요</option>
          <option v-for="board in boards" :key="board.id" :value="board.id">
            {{ board.name }}
          </option>
        </select>
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
        <textarea
          v-model="formData.content"
          required
          rows="15"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-church-green-500"
          placeholder="내용을 입력하세요"
        ></textarea>
      </div>

      <!-- 작성자 -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          작성자
        </label>
        <input
          v-model="formData.authorName"
          type="text"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-church-green-500"
          placeholder="작성자 이름"
        />
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
import adminService from '../../services/adminService'
import boardService from '../../services/boardService'

const route = useRoute()
const router = useRouter()

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
    }
  } catch (error) {
    console.error('Failed to load post:', error)
    errorMessage.value = '게시글을 불러오는데 실패했습니다.'
  }
}

// 폼 제출
const handleSubmit = async () => {
  isSaving.value = true
  errorMessage.value = ''

  try {
    let response

    if (isEditMode.value) {
      response = await adminService.updatePost(postId.value, formData.value)
    } else {
      response = await adminService.createPost(formData.value)
    }

    if (response.data.success) {
      alert(isEditMode.value ? '게시글이 수정되었습니다.' : '게시글이 작성되었습니다.')
      router.push('/admin/posts')
    } else {
      errorMessage.value = response.data.error || '저장에 실패했습니다.'
    }
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
  isLoading.value = false
})
</script>
