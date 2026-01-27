<template>
  <div class="bg-gray-50 min-h-screen">
    <!-- 팝업 모달 -->
    <PopupModal />

    <!-- Hero Section -->
    <section class="relative bg-gradient-to-r from-emerald-900 to-church-green-800 text-white overflow-hidden">
      <!-- Decorative background pattern (optional) -->
      <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
      
      <div class="relative container mx-auto px-4 py-24 md:py-32 flex flex-col items-center text-center">
        <span class="inline-block py-1 px-3 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 text-sm font-light tracking-wider mb-6 animate-fade-in-up">
          Ev. Koreanische Gemeinde in Essen e.V.
        </span>
        <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight tracking-tight animate-fade-in-up delay-100">
          에센 한인교회에<br class="md:hidden" /> 오신 것을 환영합니다
        </h1>
        <p class="text-lg md:text-xl text-gray-200 max-w-2xl mx-auto mb-10 font-light leading-relaxed animate-fade-in-up delay-200">
          하나님의 사랑이 넘치는 공동체, 에센 한인교회입니다.<br />
          함께 예배하며 교제하는 기쁨을 누리시길 소망합니다.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 animate-fade-in-up delay-300">
          <router-link
            to="/menu/2"
            class="px-8 py-3 bg-white text-church-green-800 rounded-full font-semibold hover:bg-gray-100 transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
          >
            교회 소개
          </router-link>
          <router-link
            to="/board/sermon"
            class="px-8 py-3 bg-transparent border border-white/40 text-white rounded-full font-semibold hover:bg-white/10 transition duration-300 backdrop-blur-sm"
          >
            설교 말씀
          </router-link>
        </div>
      </div>
    </section>

    <!-- Worship Info Section -->
    <section class="py-16 bg-white relative -mt-8 z-10 rounded-t-3xl shadow-[0_-10px_40px_rgba(0,0,0,0.05)]">
      <div class="container mx-auto px-4">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-gray-800 mb-3">예배 안내</h2>
          <div class="w-12 h-1 bg-church-green-500 mx-auto rounded-full"></div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
          <!-- Sunday Service -->
          <div class="group p-8 rounded-2xl border border-gray-100 bg-white hover:border-church-green-200 hover:shadow-lg transition-all duration-300 flex items-start space-x-6">
            <div class="flex-shrink-0 w-14 h-14 rounded-full bg-church-green-50 flex items-center justify-center text-church-green-600 group-hover:bg-church-green-500 group-hover:text-white transition-colors duration-300">
              <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">주일 대예배</h3>
              <p class="text-gray-600 mb-1">매주 일요일 오후 3시</p>
              <p class="text-sm text-gray-400">본당 대예배실</p>
            </div>
          </div>

          <!-- Wednesday Service -->
          <div class="group p-8 rounded-2xl border border-gray-100 bg-white hover:border-church-green-200 hover:shadow-lg transition-all duration-300 flex items-start space-x-6">
            <div class="flex-shrink-0 w-14 h-14 rounded-full bg-church-green-50 flex items-center justify-center text-church-green-600 group-hover:bg-church-green-500 group-hover:text-white transition-colors duration-300">
              <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
              </svg>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">수요 예배</h3>
              <p class="text-gray-600 mb-1">매주 수요일 저녁 7시</p>
              <p class="text-sm text-gray-400">소예배실</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Latest Content Section -->
    <section class="py-16 bg-gray-50">
      <div class="container mx-auto px-4">
        <!-- Content Grids -->
        <div v-if="isLoading" class="text-center py-20">
          <div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-church-green-500 border-t-transparent"></div>
        </div>

        <div v-else-if="error" class="text-center py-20">
          <p class="text-red-500">{{ error }}</p>
          <button @click="loadData" class="mt-4 px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">다시 시도</button>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          
          <!-- Gallery Widget (Modern) -->
          <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-300">
            <div class="flex justify-between items-end mb-6">
              <div>
                <span class="text-xs font-bold text-church-green-600 uppercase tracking-wider mb-1 block">Gallery</span>
                <h2 class="text-2xl font-bold text-gray-900">교회 앨범</h2>
              </div>
              <router-link to="/gallery" class="text-sm text-gray-500 hover:text-church-green-600 transition-colors flex items-center gap-1">
                더보기 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
              </router-link>
            </div>
            
            <div class="space-y-4">
              <router-link 
                v-for="post in galleryPosts" 
                :key="post.id" 
                :to="`/board/gallery/${post.id}`"
                class="group block"
              >
                <div class="flex gap-4">
                  <div class="w-20 h-20 rounded-lg overflow-hidden bg-gray-200 flex-shrink-0 relative">
                    <img 
                      v-if="post.thumbnail" 
                      :src="post.thumbnail" 
                      :alt="post.title" 
                      class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" 
                    />
                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                  </div>
                  <div class="flex-1 min-w-0 py-1">
                    <h4 class="font-medium text-gray-900 truncate group-hover:text-church-green-600 transition-colors">
                      {{ decodeHtmlEntities(post.title) }}
                    </h4>
                    <p class="text-xs text-gray-500 mt-2">{{ formatDate(post.publishedAt) }}</p>
                  </div>
                </div>
              </router-link>
              <div v-if="galleryPosts.length === 0" class="text-center text-gray-400 py-8 text-sm">
                등록된 앨범이 없습니다.
              </div>
            </div>
          </div>

          <!-- Notice Widget (Modern) -->
          <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-300">
            <div class="flex justify-between items-end mb-6">
              <div>
                <span class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-1 block">Notice</span>
                <h2 class="text-2xl font-bold text-gray-900">교회 소식</h2>
              </div>
              <router-link to="/board/notice" class="text-sm text-gray-500 hover:text-blue-600 transition-colors flex items-center gap-1">
                더보기 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
              </router-link>
            </div>

            <ul class="divide-y divide-gray-100">
              <li v-for="post in noticePosts" :key="post.id" class="py-3 first:pt-0 last:pb-0">
                <router-link :to="`/board/notice/${post.id}`" class="group block">
                  <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                      <p class="text-gray-800 font-medium truncate group-hover:text-blue-600 transition-colors">
                        {{ decodeHtmlEntities(post.title) }}
                      </p>
                      <p class="text-xs text-gray-500 mt-1">{{ formatDate(post.publishedAt) }}</p>
                    </div>
                    <span v-if="isNew(post.publishedAt)" class="flex-shrink-0 w-1.5 h-1.5 rounded-full bg-red-500 mt-2"></span>
                  </div>
                </router-link>
              </li>
              <li v-if="noticePosts.length === 0" class="text-center text-gray-400 py-8 text-sm">
                등록된 소식이 없습니다.
              </li>
            </ul>
          </div>

          <!-- Free Board Widget (Modern) -->
          <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-300">
            <div class="flex justify-between items-end mb-6">
              <div>
                <span class="text-xs font-bold text-purple-600 uppercase tracking-wider mb-1 block">Community</span>
                <h2 class="text-2xl font-bold text-gray-900">자유 게시판</h2>
              </div>
              <router-link to="/board/free" class="text-sm text-gray-500 hover:text-purple-600 transition-colors flex items-center gap-1">
                더보기 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
              </router-link>
            </div>

            <ul class="divide-y divide-gray-100">
              <li v-for="post in freePosts" :key="post.id" class="py-3 first:pt-0 last:pb-0">
                <router-link :to="`/board/free/${post.id}`" class="group block">
                  <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                      <p class="text-gray-800 font-medium truncate group-hover:text-purple-600 transition-colors">
                        {{ decodeHtmlEntities(post.title) }}
                      </p>
                      <p class="text-xs text-gray-500 mt-1">{{ formatDate(post.publishedAt) }}</p>
                    </div>
                    <span v-if="isNew(post.publishedAt)" class="flex-shrink-0 w-1.5 h-1.5 rounded-full bg-red-500 mt-2"></span>
                  </div>
                </router-link>
              </li>
               <li v-if="freePosts.length === 0" class="text-center text-gray-400 py-8 text-sm">
                게시글이 없습니다.
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
import PopupModal from '../components/PopupModal.vue'

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
      boardService.getBoardPosts('gallery', { limit: 3 }), // 갤러리는 3개만
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

<style scoped>
/* Simple Fade In Up Animation for Hero Text */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translate3d(0, 20px, 0);
  }
  to {
    opacity: 1;
    transform: translate3d(0, 0, 0);
  }
}

.animate-fade-in-up {
  animation: fadeInUp 0.8s ease-out forwards;
  opacity: 0; /* Start hidden */
}

.delay-100 { animation-delay: 0.1s; }
.delay-200 { animation-delay: 0.3s; }
.delay-300 { animation-delay: 0.5s; }
</style>
