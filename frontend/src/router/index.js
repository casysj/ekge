import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: () => import('../views/Home.vue'),
    meta: { title: '에센 한인교회' }
  },
  {
    path: '/board/:code',
    name: 'BoardList',
    component: () => import('../views/BoardList.vue'),
    meta: { title: '게시판' }
  },
  {
    path: '/board/:code/:id',
    name: 'BoardDetail',
    component: () => import('../views/BoardDetail.vue'),
    meta: { title: '게시글' }
  },
  {
    path: '/gallery',
    name: 'Gallery',
    component: () => import('../views/Gallery.vue'),
    meta: { title: '교회앨범' }
  },
  {
    path: '/about',
    name: 'About',
    component: () => import('../views/About.vue'),
    meta: { title: '교회소개' }
  },
  // 404 페이지
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('../views/NotFound.vue'),
    meta: { title: '페이지를 찾을 수 없습니다' }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  // 페이지 전환 시 스크롤 최상단으로
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

// 페이지 타이틀 설정
router.beforeEach((to, from, next) => {
  document.title = to.meta.title || '에센 한인교회'
  next()
})

export default router
