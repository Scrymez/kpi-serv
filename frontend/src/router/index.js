import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  { path: '/login', component: () => import('@/views/auth/LoginView.vue'), meta: { guest: true } },

  {
    path: '/',
    component: () => import('@/components/layout/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '', redirect: '/dashboard' },
      { path: 'dashboard', component: () => import('@/views/DashboardView.vue') },
      { path: 'profile', component: () => import('@/views/ProfileView.vue') },
      { path: 'chat', component: () => import('@/views/ChatView.vue') },
      { path: 'teacher-votes', component: () => import('@/views/TeacherVotesView.vue') },

      // Олимпиады
      { path: 'olympiads', component: () => import('@/views/olympiad/OlympiadListView.vue') },
      { path: 'olympiads/create', component: () => import('@/views/olympiad/OlympiadFormView.vue') },
      { path: 'olympiads/:id', component: () => import('@/views/olympiad/OlympiadDetailView.vue') },

      // Пользователи (только admin)
      { path: 'users', component: () => import('@/views/admin/UsersView.vue'), meta: { roles: ['admin'] } },
      { path: 'users/import', component: () => import('@/views/admin/ImportView.vue'), meta: { roles: ['admin'] } },

      // Результаты
      { path: 'results', component: () => import('@/views/ResultsView.vue'), meta: { roles: ['admin', 'director', 'deputy_events', 'deputy_edu', 'deputy_science', 'teacher', 'student'] } },
      { path: 'results/pending', component: () => import('@/views/PendingResultsView.vue'), meta: { roles: ['admin', 'director', 'deputy_events', 'deputy_edu', 'deputy_science'] } },

      // KPI
      { path: 'kpi', component: () => import('@/views/kpi/KpiView.vue'), meta: { roles: ['admin', 'director', 'deputy_events', 'deputy_edu', 'deputy_science', 'teacher', 'student'] } },
      { path: 'kpi/appeals', component: () => import('@/views/kpi/AppealsView.vue'), meta: { roles: ['admin', 'director', 'deputy_events', 'deputy_edu', 'deputy_science', 'teacher'] } },

      // Рейтинги
      { path: 'ratings', component: () => import('@/views/RatingsView.vue') },

      // Настройки (только admin)
      { path: 'settings', component: () => import('@/views/admin/SettingsView.vue'), meta: { roles: ['admin'] } },

      // Отчёты
      { path: 'reports', component: () => import('@/views/ReportsView.vue'), meta: { roles: ['admin', 'director', 'deputy_events', 'deputy_edu', 'deputy_science'] } },
    ],
  },

  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isLoggedIn) return next('/login')
  if (to.meta.guest && auth.isLoggedIn) return next('/dashboard')

  if (to.meta.roles && !to.meta.roles.includes(auth.role)) return next('/dashboard')
  if (auth.isLoggedIn && auth.mustChangePassword && to.path !== '/profile') return next('/profile')

  next()
})

export default router
