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
          <div class="prose max-w-none" v-html="processedContent"></div>
        </div>

        <!-- 첨부파일 -->
        <div v-if="post.attachments && post.attachments.length > 0" class="border-t px-6 py-4 bg-gray-50">
          <h3 class="font-semibold text-gray-700 mb-3">첨부파일 ({{ post.attachments.length }})</h3>
          <ul class="space-y-2">
            <li v-for="file in post.attachments" :key="file.id" class="flex items-center justify-between p-3 bg-white rounded border">
              <div class="flex items-center space-x-3">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                </svg>
                <div>
                  <p class="font-medium text-gray-800">{{ file.originalName }}</p>
                  <p class="text-sm text-gray-500">
                    {{ file.imageWidth && file.imageHeight ? `${file.imageWidth}x${file.imageHeight}` : '파일' }}
                  </p>
                </div>
              </div>
              <span class="text-xs bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full">
                준비중
              </span>
            </li>
          </ul>
          <p class="text-xs text-gray-500 mt-3">
            ℹ️ 첨부파일은 서버 이전 작업 후 다운로드 가능합니다.
          </p>
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

// 게시글 내용 처리 (이미지 경로 변환)
const processedContent = computed(() => {
  if (!post.value || !post.value.content) return ''

  let content = post.value.content

  // 구 사이트의 이미지 경로를 플레이스홀더로 대체
  content = content.replace(
    /<img([^>]*)src="[^"]*\/upfile\/[^"]*"([^>]*)>/gi,
    '<div class="bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center my-4">' +
    '<svg class="w-16 h-16 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />' +
    '</svg>' +
    '<p class="text-gray-500 text-sm">이미지 준비중</p>' +
    '<p class="text-gray-400 text-xs mt-1">서버 이전 작업 후 표시됩니다</p>' +
    '</div>'
  )

  return content
})

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
