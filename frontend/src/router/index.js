import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '../composables/useAuth'

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

  // 관리자 로그인 (인증 불필요)
  {
    path: '/admin/login',
    name: 'AdminLogin',
    component: () => import('../views/admin/Login.vue'),
    meta: { title: '관리자 로그인' }
  },

  // 관리자 페이지 (인증 필요)
  {
    path: '/admin',
    component: () => import('../layouts/AdminLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: '/admin/dashboard'
      },
      {
        path: 'dashboard',
        name: 'AdminDashboard',
        component: () => import('../views/admin/Dashboard.vue'),
        meta: { title: '관리자 대시보드' }
      },
      {
        path: 'posts',
        name: 'AdminPosts',
        component: () => import('../views/admin/PostList.vue'),
        meta: { title: '게시글 관리' }
      },
      {
        path: 'posts/create',
        name: 'AdminPostCreate',
        component: () => import('../views/admin/PostForm.vue'),
        meta: { title: '게시글 작성' }
      },
      {
        path: 'posts/:id/edit',
        name: 'AdminPostEdit',
        component: () => import('../views/admin/PostForm.vue'),
        meta: { title: '게시글 수정' }
      },
      {
        path: 'popups',
        name: 'AdminPopups',
        component: () => import('../views/admin/PopupList.vue'),
        meta: { title: '팝업 관리' }
      },
      {
        path: 'popups/create',
        name: 'AdminPopupCreate',
        component: () => import('../views/admin/PopupForm.vue'),
        meta: { title: '팝업 작성' }
      },
      {
        path: 'popups/:id/edit',
        name: 'AdminPopupEdit',
        component: () => import('../views/admin/PopupForm.vue'),
        meta: { title: '팝업 수정' }
      }
    ]
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

// 인증 가드
router.beforeEach(async (to, from, next) => {
  // 페이지 타이틀 설정
  document.title = to.meta.title || '에센 한인교회'

  // 인증이 필요한 페이지인지 확인
  if (to.meta.requiresAuth) {
    const { checkAuth } = useAuth()
    const isAuthenticated = await checkAuth()

    if (!isAuthenticated) {
      // 인증되지 않았으면 로그인 페이지로
      next('/admin/login')
    } else {
      next()
    }
  } else {
    next()
  }
})

export default router
