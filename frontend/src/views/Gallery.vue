<template>
  <div class="bg-gray-50 min-h-screen py-12">
    <div class="container mx-auto px-4 max-w-7xl">
      <!-- 페이지 헤더 (Clean Style) -->
      <div class="mb-10 text-center md:text-left">
        <h1 class="text-4xl font-bold text-gray-900 mb-2 tracking-tight">교회 앨범</h1>
        <p class="text-lg text-gray-500 font-light">교회 활동 사진을 공유합니다</p>
      </div>

      <!-- 로딩 상태 -->
      <div v-if="isLoading" class="text-center py-20">
        <div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-church-green-500 border-t-transparent"></div>
        <p class="mt-4 text-gray-500 font-medium">로딩 중...</p>
      </div>

      <!-- 에러 상태 -->
      <div v-else-if="error" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-20 text-center">
        <p class="text-red-500 mb-4">{{ error }}</p>
        <button @click="loadGallery" class="btn-primary">다시 시도</button>
      </div>

      <!-- 갤러리 그리드 -->
      <div v-else>
        <div v-if="posts.length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-20 text-center">
          <p class="text-gray-400">사진이 없습니다.</p>
        </div>

        <div v-else>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <router-link
              v-for="post in posts"
              :key="post.id"
              :to="`/board/gallery/${post.id}`"
              class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-300"
            >
              <!-- 썸네일 이미지 -->
              <div class="aspect-w-16 aspect-h-12 bg-gray-100 overflow-hidden relative">
                <img
                  v-if="post.thumbnail"
                  :src="post.thumbnail"
                  :alt="post.title"
                  class="w-full h-56 object-cover transform group-hover:scale-110 transition-transform duration-700 ease-in-out"
                />
                <div v-else class="w-full h-56 flex items-center justify-center bg-gray-100 text-gray-300">
                  <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
                <!-- Overlay Gradient -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              </div>

              <!-- 제목 및 날짜 -->
              <div class="p-5">
                <h3 class="font-bold text-gray-900 truncate group-hover:text-church-green-600 transition-colors text-lg mb-2">
                  {{ post.title }}
                </h3>
                <div class="flex justify-between items-center text-sm text-gray-500">
                  <span>{{ formatDate(post.publishedAt) }}</span>
                  <div class="flex items-center space-x-3 text-xs">
                     <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        {{ post.viewCount }}
                     </span>
                  </div>
                </div>
              </div>
            </router-link>
          </div>

          <!-- 페이지네이션 (Modern) -->
          <div v-if="totalPages > 1" class="mt-12 flex justify-center items-center space-x-2">
            <button
              @click="goToPage(currentPage - 1)"
              :disabled="currentPage === 1"
              class="w-10 h-10 flex items-center justify-center rounded-full bg-white border border-gray-200 hover:bg-gray-50 disabled:opacity-50 disabled:hover:bg-white text-gray-600 transition-colors shadow-sm"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <div class="flex space-x-1">
              <button
                v-for="page in displayedPages"
                :key="page"
                @click="goToPage(page)"
                class="w-10 h-10 flex items-center justify-center rounded-full text-sm font-bold transition-all"
                :class="[
                  page === currentPage
                    ? 'bg-church-green-600 text-white shadow-md shadow-church-green-200 transform scale-105'
                    : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50 hover:border-church-green-200'
                ]"
              >
                {{ page }}
              </button>
            </div>

            <button
              @click="goToPage(currentPage + 1)"
              :disabled="currentPage === totalPages"
              class="w-10 h-10 flex items-center justify-center rounded-full bg-white border border-gray-200 hover:bg-gray-50 disabled:opacity-50 disabled:hover:bg-white text-gray-600 transition-colors shadow-sm"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import boardService from '../services/boardService'

const isLoading = ref(true)
const error = ref(null)
const posts = ref([])
const currentPage = ref(1)
const totalPages = ref(1)
const postsPerPage = 12

// 갤러리 로드
const loadGallery = async () => {
  isLoading.value = true
  error.value = null

  try {
    const response = await boardService.getBoardPosts('gallery', {
      page: currentPage.value,
      limit: postsPerPage
    })

    posts.value = response.data.posts || []
    totalPages.value = response.data.pagination?.pages || 1
    currentPage.value = response.data.pagination?.currentPage || 1
  } catch (err) {
    console.error('Error loading gallery:', err)
    error.value = '갤러리를 불러오는 중 오류가 발생했습니다.'
  } finally {
    isLoading.value = false
  }
}

// 페이지 이동
const goToPage = (page) => {
  if (page < 1 || page > totalPages.value) return
  currentPage.value = page
  loadGallery()
  window.scrollTo({ top: 0, behavior: 'smooth' })
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

onMounted(() => {
  loadGallery()
})
</script>
