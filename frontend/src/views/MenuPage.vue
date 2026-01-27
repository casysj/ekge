<template>
  <div class="container mx-auto px-4 py-8">
    <!-- 로딩 -->
    <div v-if="loading" class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-church-green-500"></div>
    </div>

    <!-- 에러 -->
    <div v-else-if="error" class="text-center py-20">
      <p class="text-gray-500">{{ error }}</p>
      <router-link to="/" class="mt-4 inline-block text-church-green-500 hover:underline">
        홈으로 돌아가기
      </router-link>
    </div>

    <!-- 컨텐츠 -->
    <div v-else-if="menu">
      <!-- Breadcrumb -->
      <nav v-if="menu.path?.length > 1" class="mb-6 text-sm text-gray-500">
        <router-link to="/" class="hover:text-church-green-500">홈</router-link>
        <span v-for="(item, index) in menu.path" :key="item.id">
          <span class="mx-2">/</span>
          <router-link
            v-if="index < menu.path.length - 1"
            :to="`/menu/${item.id}`"
            class="hover:text-church-green-500"
          >
            {{ item.name }}
          </router-link>
          <span v-else class="text-gray-700 font-medium">{{ item.name }}</span>
        </span>
      </nav>

      <!-- HTML 컨텐츠 -->
      <div class="menu-content" v-html="menu.content"></div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import menuService from '../services/menuService'

const route = useRoute()
const menu = ref(null)
const loading = ref(true)
const error = ref(null)

const loadMenu = async (id) => {
  loading.value = true
  error.value = null
  menu.value = null

  try {
    const response = await menuService.getMenu(id)
    if (response.data.success) {
      menu.value = response.data.menu
      // 페이지 타이틀 설정
      document.title = `${response.data.menu.name} - 에센 한인교회`
    } else {
      error.value = response.data.error || '페이지를 찾을 수 없습니다.'
    }
  } catch (err) {
    error.value = '페이지를 불러오는 중 오류가 발생했습니다.'
  } finally {
    loading.value = false
  }
}

// 라우트 파라미터 변경 감지
watch(
  () => route.params.id,
  (newId) => {
    if (newId) {
      loadMenu(newId)
    }
  },
  { immediate: true }
)
</script>

<style scoped>
.menu-content {
  @apply max-w-4xl mx-auto;
}

.menu-content :deep(h2) {
  @apply text-3xl font-bold text-gray-900 mb-8 pb-4 border-b border-gray-100;
}

.menu-content :deep(h3) {
  @apply text-2xl font-bold text-gray-800 mt-10 mb-5;
}

.menu-content :deep(h4) {
  @apply text-xl font-semibold text-gray-800 mt-8 mb-4;
}

.menu-content :deep(p) {
  @apply mb-4 leading-relaxed text-gray-700 text-lg;
}

.menu-content :deep(ul), .menu-content :deep(ol) {
  @apply mb-6 pl-6 space-y-2 text-gray-700;
}

.menu-content :deep(ul) {
  @apply list-disc;
}

.menu-content :deep(ol) {
  @apply list-decimal;
}

.menu-content :deep(table) {
  @apply w-full border-collapse mb-8 bg-white rounded-lg overflow-hidden shadow-sm border border-gray-200 mt-4;
}

.menu-content :deep(th) {
  @apply bg-gray-50 font-bold p-4 text-left border-b border-gray-200;
}

.menu-content :deep(td) {
  @apply p-4 border-b border-gray-100 align-top text-gray-700;
}

.menu-content :deep(img) {
  @apply max-w-full h-auto rounded-xl shadow-md my-6;
}

.menu-content :deep(a) {
  @apply text-church-green-600 hover:text-church-green-700 hover:underline font-medium transition-colors;
}

.menu-content :deep(blockquote) {
  @apply border-l-4 border-church-green-200 pl-4 py-2 my-6 bg-gray-50 italic rounded-r-lg;
}
</style>
