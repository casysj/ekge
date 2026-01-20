<template>
  <header class="header-green shadow-md sticky top-0 z-50">
    <!-- Top Bar -->
    <div class="bg-church-green-700">
      <div class="container mx-auto px-4 py-2">
        <div class="flex justify-between items-center text-white text-sm">
          <div class="flex items-center space-x-4">
            <span class="hidden md:inline">Ex.Koreanische Gemeinde in Essen e.V.</span>
          </div>
          <div class="flex items-center space-x-4">
            <router-link
              to="/admin/dashboard"
              class="hover:text-church-light-300 transition-colors"
            >
              관리자
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Navigation -->
    <div class="bg-white">
      <div class="container mx-auto px-4">
        <div class="flex items-center justify-between py-4">
          <!-- Logo -->
          <router-link
            to="/"
            class="flex items-center space-x-3"
          >
            <div class="text-2xl md:text-3xl font-bold text-church-green-500">
              에센 한인교회
            </div>
          </router-link>

          <!-- Mobile Menu Button -->
          <button
            @click="toggleMobileMenu"
            class="md:hidden text-church-green-500 focus:outline-none"
          >
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                v-if="!isMobileMenuOpen"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"
              />
              <path
                v-else
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>

          <!-- Desktop Navigation -->
          <nav class="hidden md:flex space-x-8">
            <router-link
              v-for="menu in mainMenus"
              :key="menu.path"
              :to="menu.path"
              class="text-gray-700 hover:text-church-green-500 font-medium transition-colors"
              :class="{ 'text-church-green-500': isActive(menu.path) }"
            >
              {{ menu.name }}
            </router-link>
          </nav>
        </div>

        <!-- Mobile Navigation -->
        <transition
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="opacity-0 -translate-y-2"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition duration-150 ease-in"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 -translate-y-2"
        >
          <nav
            v-if="isMobileMenuOpen"
            class="md:hidden pb-4 border-t border-gray-200 mt-2"
          >
            <router-link
              v-for="menu in mainMenus"
              :key="menu.path"
              :to="menu.path"
              @click="closeMobileMenu"
              class="block py-3 px-4 text-gray-700 hover:bg-church-light-100 hover:text-church-green-500 transition-colors"
              :class="{ 'text-church-green-500 bg-church-light-50': isActive(menu.path) }"
            >
              {{ menu.name }}
            </router-link>
          </nav>
        </transition>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const isMobileMenuOpen = ref(false)

// 메인 메뉴
const mainMenus = [
  { name: '교회소개', path: '/about' },
  { name: '설교말씀', path: '/board/sermon' },
  { name: '주보', path: '/board/weekly' },
  { name: '교회소식', path: '/board/notice' },
  { name: '교회앨범', path: '/gallery' },
  { name: '자유게시판', path: '/board/free' },
]

// 현재 경로 확인
const isActive = (path) => {
  return route.path === path || route.path.startsWith(path + '/')
}

// 모바일 메뉴 토글
const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}

// 모바일 메뉴 닫기
const closeMobileMenu = () => {
  isMobileMenuOpen.value = false
}
</script>
