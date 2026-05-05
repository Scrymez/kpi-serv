<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api'

const auth = useAuthStore()
const stats = ref(null)
const myKpi = ref(null)
const pendingResults = ref(0)

onMounted(async () => {
  try {
    if (auth.isTeacher) {
      const res = await api.get('/kpi/my')
      myKpi.value = res.data
    }
    if (auth.canVerify) {
      const res = await api.get('/results/pending')
      pendingResults.value = res.data.length
    }
  } catch {}
})

function getRoleGreeting() {
  const map = {
    admin: 'Добро пожаловать, администратор!',
    director: 'Добро пожаловать, директор!',
    deputy_events: 'Добро пожаловать!',
    deputy_edu: 'Добро пожаловать!',
    deputy_science: 'Добро пожаловать!',
    teacher: 'Добро пожаловать, учитель!',
    student: 'Добро пожаловать!',
    parent: 'Добро пожаловать!',
  }
  return map[auth.role] || 'Добро пожаловать!'
}
</script>

<template>
  <div>
    <h2 class="text-2xl font-bold text-gray-800 mb-1">{{ getRoleGreeting() }}</h2>
    <p class="text-gray-500 mb-8">{{ auth.user?.full_name }}</p>

    <!-- KPI учителя -->
    <div v-if="auth.isTeacher && myKpi" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <div class="bg-blue-600 text-white rounded-xl p-5">
        <div class="text-3xl font-bold">{{ myKpi.total }}</div>
        <div class="text-blue-100 text-sm mt-1">Мои KPI баллы</div>
      </div>
      <div class="bg-white rounded-xl border p-5">
        <div class="text-3xl font-bold text-gray-800">{{ myKpi.scores?.length || 0 }}</div>
        <div class="text-gray-500 text-sm mt-1">Записей начислений</div>
      </div>
      <router-link to="/kpi" class="bg-white rounded-xl border p-5 hover:border-blue-300 transition cursor-pointer">
        <div class="text-blue-600 font-semibold">Подробнее →</div>
        <div class="text-gray-500 text-sm mt-1">Моя история KPI</div>
      </router-link>
    </div>

    <!-- Верификация для замов/директора -->
    <div v-if="auth.canVerify && pendingResults > 0" class="bg-amber-50 border border-amber-200 rounded-xl p-5 mb-8 flex items-center gap-4">
      <div class="text-3xl">⚠️</div>
      <div>
        <div class="font-semibold text-amber-800">Ожидают верификации: {{ pendingResults }}</div>
        <div class="text-amber-600 text-sm">Результаты олимпиад требуют вашего подтверждения</div>
      </div>
      <router-link to="/results/pending" class="ml-auto bg-amber-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-amber-600 transition">
        Перейти
      </router-link>
    </div>

    <!-- Быстрые ссылки -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <router-link to="/olympiads"
        class="bg-white rounded-xl border p-5 text-center hover:border-blue-300 hover:shadow-sm transition">
        <div class="text-2xl mb-2">🏆</div>
        <div class="text-sm font-medium text-gray-700">Олимпиады</div>
      </router-link>

      <router-link to="/ratings"
        class="bg-white rounded-xl border p-5 text-center hover:border-blue-300 hover:shadow-sm transition">
        <div class="text-2xl mb-2">🥇</div>
        <div class="text-sm font-medium text-gray-700">Рейтинги</div>
      </router-link>

      <router-link to="/results"
        v-if="!auth.isParent"
        class="bg-white rounded-xl border p-5 text-center hover:border-blue-300 hover:shadow-sm transition">
        <div class="text-2xl mb-2">📋</div>
        <div class="text-sm font-medium text-gray-700">Результаты</div>
      </router-link>

      <router-link to="/chat"
        class="bg-white rounded-xl border p-5 text-center hover:border-blue-300 hover:shadow-sm transition">
        <div class="text-2xl mb-2">💬</div>
        <div class="text-sm font-medium text-gray-700">Общий чат</div>
      </router-link>

      <router-link v-if="auth.isParent" to="/teacher-votes"
        class="bg-white rounded-xl border p-5 text-center hover:border-blue-300 hover:shadow-sm transition">
        <div class="text-2xl mb-2">🗳️</div>
        <div class="text-sm font-medium text-gray-700">Голосование</div>
      </router-link>

      <router-link v-if="auth.canVerify" to="/reports"
        class="bg-white rounded-xl border p-5 text-center hover:border-blue-300 hover:shadow-sm transition">
        <div class="text-2xl mb-2">📑</div>
        <div class="text-sm font-medium text-gray-700">Отчёты</div>
      </router-link>
    </div>
  </div>
</template>
