<template>
  <div>
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-3xl font-bold text-gray-800">게시글 관리</h1>
      <router-link to="/admin/posts/create" class="btn-primary">
        ✏️ 새 게시글 작성
      </router-link>
    </div>

    <!-- 게시판 필터 -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="flex items-center space-x-4">
        <label class="text-sm font-medium text-gray-700">게시판:</label>
        <select
          v-model="selectedBoard"
          @change="loadPosts"
          class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-church-green-500"
        >
          <option value="">전체</option>
          <option v-for="board in boards" :key="board.code" :value="board.code">
            {{ board.name }}
          </option>
        </select>
      </div>
    </div>

    <!-- 로딩 상태 -->
    <div v-if="isLoading" class="bg-white rounded-lg shadow p-12 text-center">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-church-green-500 border-t-transparent"></div>
      <p class="mt-4 text-gray-600">로딩 중...</p>
    </div>

    <!-- 게시글 목록 -->
    <div v-else class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">게시판</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">제목</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">작성자</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">조회수</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">작성일</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">관리</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="posts.length === 0">
            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
              게시글이 없습니다
            </td>
          </tr>
          <tr v-for="post in posts" :key="post.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 text-sm text-gray-900">{{ post.id }}</td>
            <td class="px-6 py-4 text-sm text-gray-600">
              <span class="px-2 py-1 bg-gray-100 rounded text-xs">
                {{ getBoardName(post.board?.code) }}
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center space-x-2">
                <span v-if="post.isNotice" class="px-2 py-1 bg-red-100 text-red-700 text-xs rounded">
                  공지
                </span>
                <router-link
                  :to="`/board/${post.board?.code}/${post.id}`"
                  target="_blank"
                  class="text-sm text-gray-900 hover:text-church-green-500"
                >
                  {{ post.title }}
                </router-link>
              </div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-600">{{ post.authorName }}</td>
            <td class="px-6 py-4 text-sm text-gray-600">{{ post.viewCount }}</td>
            <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(post.publishedAt) }}</td>
            <td class="px-6 py-4 text-sm space-x-2">
              <router-link
                :to="`/admin/posts/${post.id}/edit`"
                class="text-blue-600 hover:text-blue-700"
              >
                수정
              </router-link>
              <button
                @click="deletePost(post.id)"
                class="text-red-600 hover:text-red-700"
              >
                삭제
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- 페이지네이션 -->
      <div v-if="totalPages > 1" class="px-6 py-4 border-t">
        <div class="flex justify-center space-x-2">
          <button
            @click="goToPage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="px-3 py-1 rounded border disabled:opacity-50"
          >
            이전
          </button>

          <button
            v-for="page in displayedPages"
            :key="page"
            @click="goToPage(page)"
            :class="[
              'px-3 py-1 rounded border',
              page === currentPage
                ? 'bg-church-green-500 text-white'
                : 'hover:bg-gray-100'
            ]"
          >
            {{ page }}
          </button>

          <button
            @click="goToPage(currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="px-3 py-1 rounded border disabled:opacity-50"
          >
            다음
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import boardService from '../../services/boardService'
import adminService from '../../services/adminService'

const isLoading = ref(true)
const boards = ref([])
const posts = ref([])
const selectedBoard = ref('')

const currentPage = ref(1)
const totalPages = ref(1)
const postsPerPage = 20

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

// 게시글 목록 로드
const loadPosts = async () => {
  isLoading.value = true

  try {
    let response

    if (selectedBoard.value) {
      response = await boardService.getBoardPosts(selectedBoard.value, {
        page: currentPage.value,
        limit: postsPerPage
      })
    } else {
      // 전체 게시판의 게시글 로드 (백엔드 API에 구현 필요)
      // 임시로 첫번째 게시판 사용
      const firstBoard = boards.value[0]?.code || 'sermon'
      response = await boardService.getBoardPosts(firstBoard, {
        page: currentPage.value,
        limit: postsPerPage
      })
    }

    if (response.data.success) {
      posts.value = response.data.posts || []
      totalPages.value = response.data.pagination?.pages || 1
    }
  } catch (error) {
    console.error('Failed to load posts:', error)
  } finally {
    isLoading.value = false
  }
}

// 게시글 삭제
const deletePost = async (postId) => {
  if (!confirm('정말 삭제하시겠습니까?')) return

  try {
    const response = await adminService.deletePost(postId)

    if (response.data.success) {
      alert('삭제되었습니다.')
      loadPosts()
    } else {
      alert(response.data.error || '삭제에 실패했습니다.')
    }
  } catch (error) {
    console.error('Delete error:', error)
    alert('삭제 중 오류가 발생했습니다.')
  }
}

// 페이지 이동
const goToPage = (page) => {
  if (page < 1 || page > totalPages.value) return
  currentPage.value = page
  loadPosts()
}

// 표시할 페이지 번호들
const displayedPages = computed(() => {
  const pages = []
  const maxPages = 5
  let startPage = Math.max(1, currentPage.value - Math.floor(maxPages / 2))
  let endPage = Math.min(totalPages.value, startPage + maxPages - 1)

  if (endPage - startPage < maxPages - 1) {
    startPage = Math.max(1, endPage - maxPages + 1)
  }

  for (let i = startPage; i <= endPage; i++) {
    pages.push(i)
  }

  return pages
})

// 게시판 이름 가져오기
const getBoardName = (code) => {
  const board = boards.value.find(b => b.code === code)
  return board?.name || code
}

// 날짜 포맷팅
const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('ko-KR', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  })
}

onMounted(async () => {
  await loadBoards()
  await loadPosts()
})
</script>
