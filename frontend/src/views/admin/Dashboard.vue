<template>
  <div>
    <h1 class="text-3xl font-bold text-gray-800 mb-8">대시보드</h1>

    <!-- 로딩 상태 -->
    <div v-if="isLoading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-church-green-500 border-t-transparent"></div>
      <p class="mt-4 text-gray-600">로딩 중...</p>
    </div>

    <!-- 통계 카드 -->
    <div v-else-if="stats" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- 전체 게시글 -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600 mb-1">전체 게시글</p>
            <p class="text-3xl font-bold text-gray-800">{{ stats.totalPosts || 0 }}</p>
          </div>
          <div class="bg-church-green-100 p-3 rounded-full">
            <svg class="w-8 h-8 text-church-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- 게시판 수 -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600 mb-1">게시판</p>
            <p class="text-3xl font-bold text-gray-800">{{ stats.totalBoards || 0 }}</p>
          </div>
          <div class="bg-blue-100 p-3 rounded-full">
            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
          </div>
        </div>
      </div>

      <!-- 첨부파일 -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600 mb-1">첨부파일</p>
            <p class="text-3xl font-bold text-gray-800">{{ stats.totalAttachments || 0 }}</p>
          </div>
          <div class="bg-yellow-100 p-3 rounded-full">
            <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
            </svg>
          </div>
        </div>
      </div>

      <!-- 메뉴 -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600 mb-1">메뉴</p>
            <p class="text-3xl font-bold text-gray-800">{{ stats.totalMenus || 0 }}</p>
          </div>
          <div class="bg-purple-100 p-3 rounded-full">
            <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- 최근 게시글 -->
    <div class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b">
        <h2 class="text-xl font-semibold text-gray-800">최근 게시글</h2>
      </div>
      <div class="p-6">
        <div v-if="recentPosts.length === 0" class="text-center text-gray-500 py-8">
          게시글이 없습니다
        </div>
        <div v-else class="space-y-4">
          <div
            v-for="post in recentPosts"
            :key="post.id"
            class="flex items-center justify-between p-4 hover:bg-gray-50 rounded"
          >
            <div class="flex-grow">
              <router-link
                :to="`/admin/posts/${post.id}/edit`"
                class="text-lg font-medium text-gray-800 hover:text-church-green-500"
              >
                {{ post.title }}
              </router-link>
              <p class="text-sm text-gray-500 mt-1">
                {{ post.boardName }} · {{ formatDate(post.publishedAt) }}
              </p>
            </div>
            <span class="text-sm text-gray-400">조회 {{ post.viewCount }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import adminService from '../../services/adminService'

const isLoading = ref(true)
const stats = ref(null)
const recentPosts = ref([])

const loadDashboard = async () => {
  isLoading.value = true

  try {
    const response = await adminService.stats()

    if (response.data.success) {
      stats.value = response.data.stats
      recentPosts.value = response.data.recentPosts || []
    }
  } catch (error) {
    console.error('Dashboard load error:', error)
  } finally {
    isLoading.value = false
  }
}

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
  loadDashboard()
})
</script>
