<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api'

const auth = useAuthStore()
const olympiads = ref([])
const loading = ref(false)
const search = ref('')
const levelFilter = ref('')
const aiLoading = ref(false)
const canCreateOlympiad = auth.canVerify || auth.isTeacher

const levels = [
  { value: '', label: 'Все уровни' },
  { value: 'school', label: 'Школьный' },
  { value: 'municipal', label: 'Муниципальный' },
  { value: 'regional', label: 'Региональный' },
  { value: 'federal', label: 'Федеральный' },
  { value: 'international', label: 'Международный' },
]

const levelColors = {
  school: 'bg-gray-100 text-gray-600',
  municipal: 'bg-blue-100 text-blue-700',
  regional: 'bg-green-100 text-green-700',
  federal: 'bg-purple-100 text-purple-700',
  international: 'bg-red-100 text-red-700',
}

async function fetchOlympiads() {
  loading.value = true
  try {
    const res = await api.get('/olympiads', {
      params: { search: search.value, level: levelFilter.value, upcoming: 1 }
    })
    olympiads.value = res.data.data
  } finally {
    loading.value = false
  }
}

async function searchWithAi() {
  aiLoading.value = true
  try {
    await api.post('/olympiads/search-ai', { query: search.value })
    await fetchOlympiads()
  } finally {
    aiLoading.value = false
  }
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('ru-RU')
}

onMounted(fetchOlympiads)
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-bold text-gray-800">Олимпиады</h2>
      <div class="flex gap-2">
        <button
          v-if="auth.canVerify"
          @click="searchWithAi"
          :disabled="aiLoading"
          class="flex items-center gap-2 bg-purple-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-purple-700 disabled:opacity-50 transition"
        >
          <span>{{ aiLoading ? '⏳' : '🤖' }}</span>
          {{ aiLoading ? 'Поиск AI...' : 'Найти через AI' }}
        </button>
        <router-link
          v-if="canCreateOlympiad"
          to="/olympiads/create"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition"
        >
          + Добавить
        </router-link>
      </div>
    </div>

    <!-- Фильтры -->
    <div class="flex gap-3 mb-6">
      <input
        v-model="search"
        @input="fetchOlympiads"
        placeholder="Поиск по названию..."
        class="border border-gray-300 rounded-lg px-4 py-2 text-sm flex-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
      />
      <select
        v-model="levelFilter"
        @change="fetchOlympiads"
        class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        <option v-for="l in levels" :key="l.value" :value="l.value">{{ l.label }}</option>
      </select>
    </div>

    <!-- Список -->
    <div v-if="loading" class="text-center py-12 text-gray-400">Загрузка...</div>

    <div v-else-if="olympiads.length === 0" class="text-center py-12 text-gray-400">
      Олимпиады не найдены
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="o in olympiads"
        :key="o.id"
        class="bg-white rounded-xl border border-gray-200 p-5 hover:border-blue-300 hover:shadow-sm transition"
      >
        <div class="flex items-start justify-between gap-4">
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-1">
              <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', levelColors[o.level]]">
                {{ levels.find(l => l.value === o.level)?.label }}
              </span>
              <span v-if="o.subject" class="text-xs text-gray-400">{{ o.subject.name }}</span>
              <span v-if="o.source_type === 'auto'" class="text-xs text-purple-500">🤖 AI</span>
            </div>
            <h3 class="font-semibold text-gray-800">{{ o.title }}</h3>
            <p v-if="o.description" class="text-sm text-gray-500 mt-1 line-clamp-2">{{ o.description }}</p>
            <div class="text-xs text-gray-400 mt-2">
              📅 {{ formatDate(o.start_date) }} — {{ formatDate(o.end_date) }}
            </div>
          </div>
          <router-link
            :to="`/olympiads/${o.id}`"
            class="text-blue-600 text-sm font-medium hover:underline whitespace-nowrap"
          >
            Подробнее →
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>
