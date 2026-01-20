<template>
  <div class="bg-gray-50">
    <!-- 환영 섹션 -->
    <section class="bg-gradient-to-br from-church-green-500 to-church-green-700 text-white py-16">
      <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">에센 한인교회</h1>
        <p class="text-xl md:text-2xl mb-2">Ex.Koreanische Gemeinde in Essen e.V.</p>
        <p class="text-lg opacity-90">예세한인교회가 함께하는 방문을 진심으로 환영합니다.</p>
      </div>
    </section>

    <!-- 예배 시간 안내 -->
    <section class="bg-white py-8 border-b">
      <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-3xl mx-auto">
          <div class="flex items-center justify-center space-x-4 p-4 bg-church-light-50 rounded-lg">
            <svg class="w-12 h-12 text-church-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <p class="text-sm text-gray-600">주일 대예배</p>
              <p class="text-lg font-bold text-gray-800">매주 일요일 오후 3시</p>
            </div>
          </div>
          <div class="flex items-center justify-center space-x-4 p-4 bg-church-light-50 rounded-lg">
            <svg class="w-12 h-12 text-church-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <p class="text-sm text-gray-600">금요 기도회</p>
              <p class="text-lg font-bold text-gray-800">매주 금요일 저녁 7시</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 메인 콘텐츠 그리드 -->
    <section class="container mx-auto px-4 py-12">
      <!-- 로딩 상태 -->
      <div v-if="isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-church-green-500 border-t-transparent"></div>
        <p class="mt-4 text-gray-600">로딩 중...</p>
      </div>

      <!-- 에러 상태 -->
      <div v-else-if="error" class="text-center py-12">
        <p class="text-red-600">{{ error }}</p>
        <button @click="loadData" class="mt-4 btn-primary">다시 시도</button>
      </div>

      <!-- 콘텐츠 그리드 -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- 교회앨범 -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden card-hover">
          <div class="bg-church-green-500 text-white px-6 py-4 flex justify-between items-center">
            <h2 class="text-xl font-bold">교회앨범</h2>
            <router-link to="/gallery" class="text-sm hover:underline">더보기 +</router-link>
          </div>
          <div class="p-6">
            <div v-if="galleryPosts.length === 0" class="text-center text-gray-500 py-8">
              게시글이 없습니다
            </div>
            <div v-else class="space-y-4">
              <router-link
                v-for="post in galleryPosts"
                :key="post.id"
                :to="`/board/gallery/${post.id}`"
                class="block hover:bg-gray-50 p-3 rounded transition-colors"
              >
                <div class="flex items-start space-x-3">
                  <div
                    v-if="post.thumbnail"
                    class="flex-shrink-0 w-16 h-16 bg-gray-200 rounded overflow-hidden"
                  >
                    <img :src="post.thumbnail" :alt="post.title" class="w-full h-full object-cover" />
                  </div>
                  <div class="flex-grow min-w-0">
                    <p class="font-medium text-gray-800 truncate">{{ decodeHtmlEntities(post.title) }}</p>
                    <p class="text-sm text-gray-500">{{ formatDate(post.publishedAt) }}</p>
                  </div>
                </div>
              </router-link>
            </div>
          </div>
        </div>

        <!-- 교회소식 -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden card-hover">
          <div class="bg-church-light-500 text-white px-6 py-4 flex justify-between items-center">
            <h2 class="text-xl font-bold">교회소식</h2>
            <router-link to="/board/notice" class="text-sm hover:underline">더보기 +</router-link>
          </div>
          <div class="p-6">
            <div v-if="noticePosts.length === 0" class="text-center text-gray-500 py-8">
              게시글이 없습니다
            </div>
            <ul v-else class="space-y-3">
              <li v-for="post in noticePosts" :key="post.id">
                <router-link
                  :to="`/board/notice/${post.id}`"
                  class="block hover:bg-gray-50 p-3 rounded transition-colors"
                >
                  <div class="flex justify-between items-start">
                    <p class="font-medium text-gray-800 truncate flex-grow mr-2">{{ decodeHtmlEntities(post.title) }}</p>
                    <span
                      v-if="isNew(post.publishedAt)"
                      class="flex-shrink-0 text-xs bg-red-500 text-white px-2 py-1 rounded"
                    >
                      NEW
                    </span>
                  </div>
                  <p class="text-sm text-gray-500 mt-1">{{ formatDate(post.publishedAt) }}</p>
                </router-link>
              </li>
            </ul>
          </div>
        </div>

        <!-- 자유게시판 -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden card-hover">
          <div class="bg-gray-600 text-white px-6 py-4 flex justify-between items-center">
            <h2 class="text-xl font-bold">자유게시판</h2>
            <router-link to="/board/free" class="text-sm hover:underline">더보기 +</router-link>
          </div>
          <div class="p-6">
            <div v-if="freePosts.length === 0" class="text-center text-gray-500 py-8">
              게시글이 없습니다
            </div>
            <ul v-else class="space-y-3">
              <li v-for="post in freePosts" :key="post.id">
                <router-link
                  :to="`/board/free/${post.id}`"
                  class="block hover:bg-gray-50 p-3 rounded transition-colors"
                >
                  <div class="flex justify-between items-start">
                    <p class="font-medium text-gray-800 truncate flex-grow mr-2">{{ decodeHtmlEntities(post.title) }}</p>
                    <span
                      v-if="isNew(post.publishedAt)"
                      class="flex-shrink-0 text-xs bg-red-500 text-white px-2 py-1 rounded"
                    >
                      NEW
                    </span>
                  </div>
                  <p class="text-sm text-gray-500 mt-1">{{ formatDate(post.publishedAt) }}</p>
                </router-link>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import boardService from '../services/boardService'

const isLoading = ref(true)
const error = ref(null)
const galleryPosts = ref([])
const noticePosts = ref([])
const freePosts = ref([])

// 데이터 로드
const loadData = async () => {
  isLoading.value = true
  error.value = null

  try {
    // 각 게시판에서 최신 게시글 5개씩 가져오기
    const [galleryRes, noticeRes, freeRes] = await Promise.all([
      boardService.getBoardPosts('gallery', { limit: 5 }),
      boardService.getBoardPosts('notice', { limit: 5 }),
      boardService.getBoardPosts('free', { limit: 5 })
    ])

    galleryPosts.value = galleryRes.data.posts || []
    noticePosts.value = noticeRes.data.posts || []
    freePosts.value = freeRes.data.posts || []
  } catch (err) {
    console.error('Error loading data:', err)
    error.value = '데이터를 불러오는 중 오류가 발생했습니다.'
  } finally {
    isLoading.value = false
  }
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

// HTML Entity 디코딩
const decodeHtmlEntities = (text) => {
  const textarea = document.createElement('textarea')
  textarea.innerHTML = text
  return textarea.value
}

// 신규 게시글 확인 (3일 이내)
const isNew = (dateString) => {
  if (!dateString) return false
  const postDate = new Date(dateString)
  const now = new Date()
  const diffDays = (now - postDate) / (1000 * 60 * 60 * 24)
  return diffDays <= 3
}

onMounted(() => {
  loadData()
})
</script>
