<template>
  <div class="bg-gray-50 min-h-screen py-8">
    <div class="container mx-auto px-4">
      <!-- 페이지 헤더 -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-800">{{ boardTitle }}</h1>
        <p class="text-gray-600 mt-2">{{ boardDescription }}</p>
      </div>

      <!-- 로딩 상태 -->
      <div v-if="isLoading" class="bg-white rounded-lg shadow-md p-12 text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-church-green-500 border-t-transparent"></div>
        <p class="mt-4 text-gray-600">로딩 중...</p>
      </div>

      <!-- 에러 상태 -->
      <div v-else-if="error" class="bg-white rounded-lg shadow-md p-12 text-center">
        <p class="text-red-600">{{ error }}</p>
        <button @click="loadPosts" class="mt-4 btn-primary">다시 시도</button>
      </div>

      <!-- 게시글 목록 -->
      <div v-else>
        <div v-if="posts.length === 0" class="bg-white rounded-lg shadow-md p-12 text-center">
          <p class="text-gray-500">게시글이 없습니다.</p>
        </div>

        <div v-else class="bg-white rounded-lg shadow-md overflow-hidden">
          <!-- 테이블 헤더 -->
          <div class="hidden md:grid grid-cols-12 gap-4 px-6 py-4 bg-gray-50 border-b font-semibold text-gray-700">
            <div class="col-span-1 text-center">번호</div>
            <div class="col-span-7">제목</div>
            <div class="col-span-2 text-center">작성자</div>
            <div class="col-span-2 text-center">날짜</div>
          </div>

          <!-- 게시글 리스트 -->
          <div>
            <router-link
              v-for="(post, index) in posts"
              :key="post.id"
              :to="`/board/${boardCode}/${post.id}`"
              class="grid grid-cols-12 gap-4 px-6 py-4 border-b hover:bg-gray-50 transition-colors"
            >
              <!-- 번호 -->
              <div class="col-span-12 md:col-span-1 text-center">
                <span class="text-gray-600">{{ totalPosts - (currentPage - 1) * postsPerPage - index }}</span>
              </div>

              <!-- 제목 -->
              <div class="col-span-12 md:col-span-7">
                <div class="flex items-center space-x-2">
                  <span class="font-medium text-gray-800 hover:text-church-green-500">
                    {{ post.title }}
                  </span>
                  <span
                    v-if="isNew(post.publishedAt)"
                    class="text-xs bg-red-500 text-white px-2 py-0.5 rounded"
                  >
                    NEW
                  </span>
                  <span
                    v-if="post.attachmentCount > 0"
                    class="text-xs text-gray-500"
                  >
                    📎 {{ post.attachmentCount }}
                  </span>
                </div>
              </div>

              <!-- 작성자 -->
              <div class="col-span-6 md:col-span-2 text-left md:text-center">
                <span class="text-sm text-gray-600">{{ post.authorName }}</span>
              </div>

              <!-- 날짜 -->
              <div class="col-span-6 md:col-span-2 text-right md:text-center">
                <span class="text-sm text-gray-600">{{ formatDate(post.publishedAt) }}</span>
              </div>
            </router-link>
          </div>

          <!-- 페이지네이션 -->
          <div v-if="totalPages > 1" class="px-6 py-4 border-t bg-gray-50">
            <div class="flex justify-center space-x-2">
              <button
                @click="goToPage(currentPage - 1)"
                :disabled="currentPage === 1"
                class="px-3 py-1 rounded border bg-white disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-100"
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
                    : 'bg-white hover:bg-gray-100'
                ]"
              >
                {{ page }}
              </button>

              <button
                @click="goToPage(currentPage + 1)"
                :disabled="currentPage === totalPages"
                class="px-3 py-1 rounded border bg-white disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-100"
              >
                다음
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import boardService from '../services/boardService'

const route = useRoute()
const boardCode = computed(() => route.params.code)

const isLoading = ref(true)
const error = ref(null)
const posts = ref([])
const currentPage = ref(1)
const totalPosts = ref(0)
const totalPages = ref(1)
const postsPerPage = ref(20)

// 게시판 제목 매핑
const boardTitles = {
  sermon: '설교말씀 및 듣기',
  weekly: '주보',
  notice: '교회소식',
  gallery: '교회앨범',
  free: '자유게시판'
}

const boardDescriptions = {
  sermon: '주일 설교 말씀을 나눕니다',
  weekly: '매주 발행되는 주보입니다',
  notice: '교회의 소식을 전합니다',
  gallery: '교회 활동 사진을 공유합니다',
  free: '자유롭게 소통하는 공간입니다'
}

const boardTitle = computed(() => boardTitles[boardCode.value] || '게시판')
const boardDescription = computed(() => boardDescriptions[boardCode.value] || '')

// 게시글 로드
const loadPosts = async () => {
  isLoading.value = true
  error.value = null

  try {
    const response = await boardService.getBoardPosts(boardCode.value, {
      page: currentPage.value,
      limit: postsPerPage.value
    })

    posts.value = response.data.posts || []
    totalPosts.value = response.data.total || 0
    totalPages.value = response.data.totalPages || 1
    postsPerPage.value = response.data.perPage || 20
  } catch (err) {
    console.error('Error loading posts:', err)
    error.value = '게시글을 불러오는 중 오류가 발생했습니다.'
  } finally {
    isLoading.value = false
  }
}

// 페이지 이동
const goToPage = (page) => {
  if (page < 1 || page > totalPages.value) return
  currentPage.value = page
  loadPosts()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// 표시할 페이지 번호들
const displayedPages = computed(() => {
  const pages = []
  const maxPages = 10
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

// 신규 게시글 확인 (3일 이내)
const isNew = (dateString) => {
  if (!dateString) return false
  const postDate = new Date(dateString)
  const now = new Date()
  const diffDays = (now - postDate) / (1000 * 60 * 60 * 24)
  return diffDays <= 3
}

// 게시판 코드 변경 시 데이터 리로드
watch(boardCode, () => {
  currentPage.value = 1
  loadPosts()
})

onMounted(() => {
  loadPosts()
})
</script>
