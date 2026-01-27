<template>
  <div class="min-h-screen bg-gray-100">
    <!-- 상단 네비게이션 -->
    <nav class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- 로고 -->
          <div class="flex items-center space-x-4">
            <router-link to="/admin/dashboard" class="text-xl font-bold text-church-green-500">
              에센 한인교회 관리자
            </router-link>
          </div>

          <!-- 우측 메뉴 -->
          <div class="flex items-center space-x-4">
            <span class="text-gray-600">{{ user?.displayName || user?.username }}</span>
            <router-link to="/" class="text-gray-600 hover:text-gray-900" target="_blank">
              사이트 보기
            </router-link>
            <button
              @click="handleLogout"
              class="text-red-600 hover:text-red-700 font-medium"
            >
              로그아웃
            </button>
          </div>
        </div>
      </div>
    </nav>

    <div class="flex">
      <!-- 사이드바 -->
      <aside class="w-64 bg-white shadow-sm min-h-screen">
        <nav class="p-4 space-y-2">
          <router-link
            to="/admin/dashboard"
            class="block px-4 py-2 rounded hover:bg-gray-100"
            :class="{ 'bg-church-green-100 text-church-green-700': $route.path === '/admin/dashboard' }"
          >
            📊 대시보드
          </router-link>

          <div class="pt-4 pb-2 px-4 text-xs font-semibold text-gray-500 uppercase">
            게시글 관리
          </div>

          <router-link
            to="/admin/posts"
            class="block px-4 py-2 rounded hover:bg-gray-100"
            :class="{ 'bg-church-green-100 text-church-green-700': $route.path.startsWith('/admin/posts') }"
          >
            📝 게시글 목록
          </router-link>

          <router-link
            to="/admin/posts/create"
            class="block px-4 py-2 rounded hover:bg-gray-100"
            :class="{ 'bg-church-green-100 text-church-green-700': $route.path === '/admin/posts/create' }"
          >
            ✏️ 새 게시글 작성
          </router-link>

          <div class="pt-4 pb-2 px-4 text-xs font-semibold text-gray-500 uppercase">
            사이트 관리
          </div>

          <router-link
            to="/admin/popups"
            class="block px-4 py-2 rounded hover:bg-gray-100"
            :class="{ 'bg-church-green-100 text-church-green-700': $route.path.startsWith('/admin/popups') }"
          >
            🔔 팝업 관리
          </router-link>

          <div class="pt-4 pb-2 px-4 text-xs font-semibold text-gray-500 uppercase">
            계정 관리
          </div>

          <router-link
            to="/admin/change-password"
            class="block px-4 py-2 rounded hover:bg-gray-100"
            :class="{ 'bg-church-green-100 text-church-green-700': $route.path === '/admin/change-password' }"
          >
            🔒 비밀번호 변경
          </router-link>
        </nav>
      </aside>

      <!-- 메인 콘텐츠 -->
      <main class="flex-1 p-8">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { useAuth } from '../composables/useAuth'
import { useRouter } from 'vue-router'

const { user, logout } = useAuth()
const router = useRouter()

const handleLogout = async () => {
  if (confirm('로그아웃 하시겠습니까?')) {
    await logout()
  }
}
</script>
