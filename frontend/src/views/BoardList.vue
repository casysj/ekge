<template>
  <div class="bg-gray-50 min-h-screen py-12">
    <div class="container mx-auto px-4 max-w-6xl">
      <!-- 페이지 헤더 (Clean Style) -->
      <div class="mb-10 text-center md:text-left">
        <h1 class="text-4xl font-bold text-gray-900 mb-2 tracking-tight">{{ boardTitle }}</h1>
        <p class="text-lg text-gray-500 font-light">{{ boardDescription }}</p>
      </div>

      <!-- 로딩 상태 -->
      <div v-if="isLoading" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-20 text-center">
        <div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-church-green-500 border-t-transparent"></div>
        <p class="mt-4 text-gray-500 font-medium">로딩 중...</p>
      </div>

      <!-- 에러 상태 -->
      <div v-else-if="error" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-20 text-center">
        <p class="text-red-500 mb-4">{{ error }}</p>
        <button @click="loadPosts" class="btn-primary">다시 시도</button>
      </div>

      <!-- 게시글 목록 -->
      <div v-else>
        <div v-if="posts.length === 0 && notices.length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-20 text-center">
          <p class="text-gray-400">게시글이 없습니다.</p>
        </div>

        <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <!-- 테이블 헤더 -->
          <div class="hidden md:grid grid-cols-12 gap-x-4 px-8 py-5 bg-gray-50/50 border-b border-gray-100 text-sm font-semibold text-gray-500 uppercase tracking-wider">
            <div class="col-span-1 text-center">No.</div>
            <div class="col-span-7">제목</div>
            <div class="col-span-2 text-center">작성자</div>
            <div class="col-span-2 text-center">날짜</div>
          </div>

          <!-- 공지사항 리스트 -->
          <div v-if="notices.length > 0">
            <router-link
              v-for="notice in notices"
              :key="'notice-' + notice.id"
              :to="`/board/${boardCode}/${notice.id}`"
              class="grid grid-cols-12 gap-x-4 px-6 md:px-8 py-4 border-b border-gray-100 bg-red-50/30 hover:bg-red-50/50 transition-colors items-center group relative overflow-hidden"
            >
              <div class="absolute left-0 top-0 bottom-0 w-1 bg-red-400"></div>
              <!-- 번호 -->
              <div class="col-span-12 md:col-span-1 text-center mb-2 md:mb-0">
                <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-600">공지</span>
              </div>

              <!-- 제목 -->
              <div class="col-span-12 md:col-span-7 mb-2 md:mb-0">
                <div class="flex items-center gap-2">
                  <span class="font-bold text-gray-900 group-hover:text-red-600 transition-colors text-base">
                    {{ decodeHtmlEntities(notice.title) }}
                  </span>
                  <span
                    v-if="isNew(notice.publishedAt)"
                    class="flex-shrink-0 w-1.5 h-1.5 rounded-full bg-red-500"
                  ></span>
                </div>
              </div>

              <!-- 작성자 -->
              <div class="col-span-6 md:col-span-2 text-left md:text-center">
                <span class="text-sm font-medium text-gray-600">{{ notice.authorName }}</span>
              </div>

              <!-- 날짜 -->
              <div class="col-span-6 md:col-span-2 text-right md:text-center">
                <span class="text-sm text-gray-400 font-light">{{ formatDate(notice.publishedAt) }}</span>
              </div>
            </router-link>
          </div>

          <!-- 일반 게시글 리스트 -->
          <div>
            <router-link
              v-for="(post, index) in posts"
              :key="post.id"
              :to="`/board/${boardCode}/${post.id}`"
              class="grid grid-cols-12 gap-x-4 px-6 md:px-8 py-5 border-b border-gray-100 hover:bg-gray-50/80 transition-all items-center group"
            >
              <!-- 번호 -->
              <div class="col-span-12 md:col-span-1 text-center mb-1 md:mb-0">
                <span class="text-sm text-gray-400 font-mono">{{ totalPosts - (currentPage - 1) * postsPerPage - index }}</span>
              </div>

              <!-- 제목 -->
              <div class="col-span-12 md:col-span-7 mb-1 md:mb-0">
                <div class="flex items-center gap-3">
                  <span class="font-medium text-gray-800 text-base group-hover:text-church-green-600 transition-colors">
                    {{ decodeHtmlEntities(post.title) }}
                  </span>
                  <span
                    v-if="isNew(post.publishedAt)"
                    class="flex-shrink-0 w-1.5 h-1.5 rounded-full bg-red-500"
                  ></span>
                  <span
                    v-if="post.attachmentCount > 0"
                    class="inline-flex items-center text-xs text-gray-400"
                  >
                   <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                   {{ post.attachmentCount }}
                  </span>
                </div>
              </div>

              <!-- 작성자 -->
              <div class="col-span-6 md:col-span-2 text-left md:text-center">
                <span class="text-sm text-gray-600">{{ post.authorName }}</span>
              </div>

              <!-- 날짜 -->
              <div class="col-span-6 md:col-span-2 text-right md:text-center">
                <span class="text-sm text-gray-400 font-light">{{ formatDate(post.publishedAt) }}</span>
              </div>
            </router-link>
          </div>

          <!-- 페이지네이션 (Modern) -->
          <div v-if="totalPages > 1" class="px-6 py-6 border-t border-gray-50 bg-gray-50/30">
            <div class="flex justify-center items-center space-x-2">
              <button
                @click="goToPage(currentPage - 1)"
                :disabled="currentPage === 1"
                class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 disabled:opacity-30 disabled:hover:bg-transparent transition-colors text-gray-600"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
              </button>

              <div class="flex space-x-1">
                <button
                  v-for="page in displayedPages"
                  :key="page"
                  @click="goToPage(page)"
                  class="w-8 h-8 flex items-center justify-center rounded-full text-sm font-medium transition-all"
                  :class="[
                    page === currentPage
                      ? 'bg-church-green-500 text-white shadow-md shadow-church-green-200'
                      : 'text-gray-600 hover:bg-gray-100'
                  ]"
                >
                  {{ page }}
                </button>
              </div>

              <button
                @click="goToPage(currentPage + 1)"
                :disabled="currentPage === totalPages"
                class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 disabled:opacity-30 disabled:hover:bg-transparent transition-colors text-gray-600"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
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
const notices = ref([])
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
    notices.value = response.data.notices || []
    totalPosts.value = response.data.pagination?.total || 0
    totalPages.value = response.data.pagination?.pages || 1
    postsPerPage.value = response.data.pagination?.perPage || 20
    currentPage.value = response.data.pagination?.currentPage || 1
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

// HTML Entity 디코딩
const decodeHtmlEntities = (text) => {
  if (!text) return ''
  const textarea = document.createElement('textarea')
  textarea.innerHTML = text
  return textarea.value
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
