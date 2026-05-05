<script setup>
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

const navItems = [
  { to: '/dashboard', label: 'Главная', icon: '🏠', roles: null },
  { to: '/olympiads', label: 'Олимпиады', icon: '🏆', roles: null },
  { to: '/results', label: 'Результаты', icon: '📋', roles: null },
  { to: '/results/pending', label: 'На верификации', icon: '✅', roles: ['admin', 'director', 'deputy_events', 'deputy_edu', 'deputy_science'] },
  { to: '/kpi', label: 'Мой KPI', icon: '📊', roles: ['teacher'] },
  { to: '/kpi/appeals', label: 'Апелляции', icon: '📝', roles: ['admin', 'director', 'deputy_events', 'deputy_edu', 'deputy_science', 'teacher'] },
  { to: '/ratings', label: 'Рейтинги', icon: '🥇', roles: null },
  { to: '/reports', label: 'Отчёты', icon: '📑', roles: ['admin', 'director', 'deputy_events', 'deputy_edu', 'deputy_science'] },
  { to: '/users', label: 'Пользователи', icon: '👥', roles: ['admin'] },
  { to: '/settings', label: 'Настройки KPI', icon: '⚙️', roles: ['admin'] },
]

function canShow(item) {
  if (!item.roles) return true
  return item.roles.includes(auth.role)
}
</script>

<template>
  <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
    <div class="px-6 py-5 border-b border-gray-100">
      <div class="text-xl font-bold text-blue-600">KPI School</div>
      <div class="text-xs text-gray-400 mt-1">Система управления KPI</div>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-1">
      <router-link
        v-for="item in navItems.filter(canShow)"
        :key="item.to"
        :to="item.to"
        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-blue-50 hover:text-blue-700 transition"
        active-class="bg-blue-50 text-blue-700"
      >
        <span>{{ item.icon }}</span>
        {{ item.label }}
      </router-link>
    </nav>

    <div class="px-6 py-4 border-t border-gray-100 text-xs text-gray-400">
      v1.0.0
    </div>
  </aside>
</template>
