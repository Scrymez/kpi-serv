<script setup>
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import SidebarNav from './SidebarNav.vue'

const auth = useAuthStore()
const router = useRouter()

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <div class="flex h-screen bg-gray-50">
    <SidebarNav />

    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
        <h1 class="text-lg font-semibold text-gray-800">KPI Школьная система</h1>
        <div class="flex items-center gap-4">
          <router-link to="/profile" class="flex items-center gap-2 text-sm text-gray-600 hover:text-blue-700">
            <img
              v-if="auth.user?.avatar_url"
              :src="auth.user.avatar_url"
              class="w-8 h-8 rounded-full object-cover border"
              alt=""
            />
            <span v-else class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-semibold">
              {{ auth.user?.first_name?.[0] || auth.user?.full_name?.[0] || 'U' }}
            </span>
          </router-link>
          <span class="text-sm text-gray-600">{{ auth.user?.full_name }}</span>
          <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full">
            {{ roleLabel(auth.user?.role) }}
          </span>
          <button
            @click="handleLogout"
            class="text-sm text-red-500 hover:text-red-700 transition"
          >
            Выйти
          </button>
        </div>
      </header>

      <!-- Main content -->
      <main class="flex-1 overflow-y-auto p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script>
export default {
  methods: {
    roleLabel(role) {
      const labels = {
        admin: 'Администратор',
        director: 'Директор',
        deputy_events: 'Зам. по мероприятиям',
        deputy_edu: 'Зам. по УВР',
        deputy_science: 'Зам. по НТР',
        teacher: 'Учитель',
        student: 'Ученик',
        parent: 'Родитель',
      }
      return labels[role] || role
    }
  }
}
</script>
