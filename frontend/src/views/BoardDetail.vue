<template>
  <div class="bg-gray-50 min-h-screen py-8">
    <div class="container mx-auto px-4 max-w-4xl">
      <!-- 로딩 상태 -->
      <div v-if="isLoading" class="bg-white rounded-lg shadow-md p-12 text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-church-green-500 border-t-transparent"></div>
        <p class="mt-4 text-gray-600">로딩 중...</p>
      </div>

      <!-- 에러 상태 -->
      <div v-else-if="error" class="bg-white rounded-lg shadow-md p-12 text-center">
        <p class="text-red-600">{{ error }}</p>
        <button @click="loadPost" class="mt-4 btn-primary">다시 시도</button>
      </div>

      <!-- 게시글 상세 -->
      <div v-else-if="post" class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- 헤더 -->
        <div class="border-b px-6 py-4 bg-gray-50">
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4">{{ post.title }}</h1>
          <div class="flex flex-wrap items-center text-sm text-gray-600 gap-4">
            <span>작성자: {{ post.authorName }}</span>
            <span>|</span>
            <span>작성일: {{ formatDate(post.publishedAt) }}</span>
            <span>|</span>
            <span>조회수: {{ post.viewCount }}</span>
          </div>
        </div>

        <!-- 본문 -->
        <div class="px-6 py-8">
          <div class="prose max-w-none" v-html="post.content"></div>
        </div>

        <!-- 첨부파일 -->
        <div v-if="post.attachments && post.attachments.length > 0" class="border-t px-6 py-4 bg-gray-50">
          <h3 class="font-semibold text-gray-700 mb-3">첨부파일</h3>
          <ul class="space-y-2">
            <li v-for="file in post.attachments" :key="file.id" class="flex items-center space-x-2">
              <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <a :href="`/uploads/${file.filePath}`" target="_blank" class="text-church-green-500 hover:underline">
                {{ file.originalName }} ({{ formatFileSize(file.fileSize) }})
              </a>
            </li>
          </ul>
        </div>

        <!-- 버튼 -->
        <div class="border-t px-6 py-4 flex justify-between">
          <router-link :to="`/board/${boardCode}`" class="btn-secondary">
            목록으로
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import boardService from '../services/boardService'

const route = useRoute()
const postId = computed(() => route.params.id)
const boardCode = computed(() => route.params.code)

const isLoading = ref(true)
const error = ref(null)
const post = ref(null)

// 게시글 로드
const loadPost = async () => {
  isLoading.value = true
  error.value = null

  try {
    const response = await boardService.getPost(postId.value)
    post.value = response.data.post // API 응답 구조에 맞게 수정
  } catch (err) {
    console.error('Error loading post:', err)
    error.value = '게시글을 불러오는 중 오류가 발생했습니다.'
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
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// 파일 크기 포맷팅
const formatFileSize = (bytes) => {
  if (!bytes) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

onMounted(() => {
  loadPost()
})
</script>

<style scoped>
/* 게시글 본문 스타일 */
.prose {
  line-height: 1.8;
}

.prose img {
  max-width: 100%;
  height: auto;
  margin: 1rem 0;
  border-radius: 0.5rem;
}

.prose p {
  margin-bottom: 1rem;
}

.prose a {
  color: #2d7a4e;
  text-decoration: underline;
}
</style>
