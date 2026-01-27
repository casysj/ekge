<template>
  <header class="shadow-sm sticky top-0 z-50 bg-white/95 backdrop-blur-md transition-all duration-300">
    <!-- Main Navigation -->
    <div class="container mx-auto px-4">
      <div class="flex items-center justify-between h-16 md:h-20">
        <!-- Logo -->
        <router-link to="/" class="flex items-center space-x-3 group">
          <div class="flex flex-col">
            <span class="text-xl md:text-2xl font-bold text-gray-900 group-hover:text-church-green-600 transition-colors">
              에센 한인교회
            </span>
            <span class="text-[10px] text-gray-500 tracking-wider">Ev. Koreanische Gemeinde in Essen e.V.</span>
          </div>
        </router-link>

        <!-- Desktop Navigation -->
        <nav class="hidden md:flex space-x-8">
          <div v-for="menu in menus" :key="menu.id" class="relative group">
            <!-- 1단계 메뉴 -->
            <router-link
              :to="getMenuPath(menu)"
              class="inline-flex items-center gap-1 py-2 text-gray-600 hover:text-church-green-600 font-medium transition-colors relative"
              :class="{ 'text-church-green-600 font-semibold': isMenuActive(menu) }"
            >
              {{ menu.name }}
              <!-- Active Underline -->
              <span 
                class="absolute bottom-0 left-0 w-full h-0.5 bg-church-green-500 transform scale-x-0 transition-transform duration-300 group-hover:scale-x-100"
                :class="{ 'scale-x-100': isMenuActive(menu) }"
              ></span>
            </router-link>

            <!-- 드롭다운 서브메뉴 -->
            <div
              v-if="menu.children?.length"
              class="absolute left-1/2 -translate-x-1/2 top-full pt-2 invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200 z-50"
            >
              <ul class="bg-white rounded-xl shadow-xl border border-gray-100 py-2 min-w-[180px] overflow-hidden">
                <li v-for="child in menu.children" :key="child.id">
                  <router-link
                    :to="getMenuPath(child)"
                    class="block px-4 py-2.5 text-sm text-gray-600 hover:bg-church-green-50 hover:text-church-green-700 transition-colors"
                    :class="{ 'text-church-green-600 bg-church-green-50/50': isMenuActive(child) }"
                  >
                    {{ child.name }}
                  </router-link>
                </li>
              </ul>
            </div>
          </div>
        </nav>

        <!-- Right Icons / Mobile Menu Button -->
        <div class="flex items-center space-x-4">
           <!-- Admin Link (Desktop only or hidden icon) -->
           <router-link
              to="/admin/dashboard"
              class="hidden md:flex items-center justify-center w-8 h-8 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all"
              title="관리자"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </router-link>

          <!-- Mobile Menu Button -->
          <button
            @click="isMobileMenuOpen = true"
            class="md:hidden text-gray-600 hover:text-church-green-600 focus:outline-none p-2"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Navigation Drawer -->
    <Teleport to="body">
      <!-- Backdrop -->
      <Transition
        enter-active-class="transition-opacity duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div 
          v-if="isMobileMenuOpen" 
          class="fixed inset-0 bg-black/50 z-[60]"
          @click="closeMobileMenu"
        ></div>
      </Transition>

      <!-- Drawer -->
      <Transition
        enter-active-class="transform transition duration-300 ease-out"
        enter-from-class="translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transform transition duration-200 ease-in"
        leave-from-class="translate-x-0"
        leave-to-class="translate-x-full"
      >
        <div 
          v-if="isMobileMenuOpen" 
          class="fixed right-0 top-0 w-[280px] h-full bg-white shadow-2xl z-[70] overflow-y-auto flex flex-col"
        >
          <!-- Drawer Header -->
          <div class="p-6 flex justify-between items-center border-b border-gray-100">
             <span class="font-bold text-lg text-gray-900">Menu</span>
             <button 
                @click="closeMobileMenu"
                class="text-gray-400 hover:text-gray-600 p-2"
             >
               <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
             </button>
          </div>

          <!-- Drawer Content -->
          <div class="flex-1 py-4">
             <nav class="space-y-1">
               <div v-for="menu in menus" :key="menu.id">
                 <!-- Menu Item with Submenu -->
                 <div v-if="menu.children?.length">
                   <button
                      @click="toggleSubmenu(menu.id)"
                      class="w-full flex justify-between items-center px-6 py-3 text-left text-gray-700 hover:bg-gray-50 transition-colors"
                      :class="{ 'text-church-green-600': isMenuActive(menu) }"
                   >
                     <span class="font-medium text-base">{{ menu.name }}</span>
                     <svg 
                        class="w-4 h-4 text-gray-400 transition-transform duration-200"
                        :class="{ 'rotate-180': openSubmenu === menu.id }"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                      >
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                     </svg>
                   </button>
                   
                   <!-- Submenu Accordion -->
                   <div 
                      v-show="openSubmenu === menu.id"
                      class="bg-gray-50/50 border-y border-gray-50"
                   >
                      <router-link
                        v-for="child in menu.children"
                        :key="child.id"
                        :to="getMenuPath(child)"
                        @click="closeMobileMenu"
                        class="block px-8 py-2.5 text-sm text-gray-600 hover:text-church-green-600"
                        :class="{ 'text-church-green-600 font-medium': isMenuActive(child) }"
                      >
                        - {{ child.name }}
                      </router-link>
                   </div>
                 </div>

                 <!-- Simple Menu Item -->
                 <router-link
                    v-else
                    :to="getMenuPath(menu)"
                    @click="closeMobileMenu"
                    class="block px-6 py-3 text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-church-green-600 transition-colors"
                    :class="{ 'text-church-green-600 bg-church-green-50/30': isMenuActive(menu) }"
                 >
                   {{ menu.name }}
                 </router-link>
               </div>
             </nav>
          </div>

          <!-- Drawer Footer -->
          <div class="p-6 border-t border-gray-100 bg-gray-50">
             <router-link
                to="/admin/login"
                @click="closeMobileMenu"
                class="flex items-center justify-center space-x-2 text-sm text-gray-500 hover:text-church-green-600 transition-colors"
             >
               <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
               <span>관리자 로그인</span>
             </router-link>
          </div>
        </div>
      </Transition>
    </Teleport>
  </header>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import menuService from '../services/menuService'

const route = useRoute()
const isMobileMenuOpen = ref(false)
const openSubmenu = ref(null)
const menus = ref([])

// 메뉴 데이터 로드
onMounted(async () => {
  try {
    const response = await menuService.getAllMenus()
    if (response.data.success) {
      menus.value = response.data.menus
    }
  } catch (error) {
    console.error('메뉴 로드 실패:', error)
    // 폴백 메뉴
    menus.value = [
      { id: 0, name: '교회소개', type: 'html', children: [] },
      { id: 7, name: '설교말씀', type: 'board', board: { code: 'sermon' } },
      { id: 8, name: '주보', type: 'board', board: { code: 'weekly' } },
      { id: 9, name: '교회소식', type: 'board', board: { code: 'notice' } },
      { id: 10, name: '교회앨범', type: 'board', board: { code: 'gallery' } },
      { id: 11, name: '자유게시판', type: 'board', board: { code: 'free' } },
    ]
  }
})

const getMenuPath = (menu) => {
  switch (menu.type) {
    case 'board':
      if (menu.board?.code === 'gallery') return '/gallery'
      return `/board/${menu.board?.code}`
    case 'html':
      if (menu.children?.length > 0) return `/menu/${menu.children[0].id}`
      return `/menu/${menu.id}`
    case 'external':
      return menu.url || '/'
    default:
      return '/'
  }
}

const isMenuActive = (menu) => {
  const path = route.path
  if (menu.type === 'board' && menu.board) {
    if (menu.board.code === 'gallery') {
       return path === '/gallery' || path.startsWith('/gallery/')
    }
    return path.startsWith(`/board/${menu.board.code}`)
  }
  if (menu.type === 'html') {
    if (path === `/menu/${menu.id}`) return true
    if (menu.children?.some(child => path === `/menu/${child.id}`)) return true
  }
  return false
}

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false
}

const toggleSubmenu = (menuId) => {
  openSubmenu.value = openSubmenu.value === menuId ? null : menuId
}
</script>
