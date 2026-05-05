<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api'

const data = ref(null)
const loading = ref(false)
const appealForm = ref({ kpi_score_id: null, reason: '' })
const appealLoading = ref(false)
const showAppealFor = ref(null)

onMounted(async () => {
  loading.value = true
  try {
    const res = await api.get('/kpi/my')
    data.value = res.data
  } finally {
    loading.value = false
  }
})

async function submitAppeal(scoreId) {
  appealLoading.value = true
  try {
    await api.post('/kpi/appeals', { kpi_score_id: scoreId, reason: appealForm.value.reason })
    showAppealFor.value = null
    appealForm.value.reason = ''
    alert('Апелляция подана!')
  } catch (e) {
    alert(e.response?.data?.message || 'Ошибка')
  } finally {
    appealLoading.value = false
  }
}

function formatDate(d) {
  return new Date(d).toLocaleDateString('ru-RU')
}
</script>

<template>
  <div>
    <h2 class="text-xl font-bold text-gray-800 mb-6">Мой KPI</h2>

    <div v-if="loading" class="text-gray-400">Загрузка...</div>

    <div v-else-if="data">
      <!-- Итог -->
      <div class="bg-blue-600 text-white rounded-xl p-6 mb-6">
        <div class="text-4xl font-bold mb-1">{{ data.total }}</div>
        <div class="text-blue-100">Баллов за {{ data.year?.name || 'текущий год' }}</div>
      </div>

      <!-- История начислений -->
      <div class="bg-white rounded-xl border">
        <div class="px-5 py-4 border-b font-semibold text-gray-700">История начислений</div>
        <div v-if="data.scores.length === 0" class="px-5 py-8 text-center text-gray-400">
          Пока нет начислений
        </div>
        <div v-else class="divide-y">
          <div v-for="score in data.scores" :key="score.id" class="px-5 py-4 flex items-start justify-between gap-4">
            <div>
              <div class="text-sm font-medium text-gray-800">{{ score.reason }}</div>
              <div class="text-xs text-gray-400 mt-0.5">{{ formatDate(score.created_at) }}</div>
            </div>
            <div class="flex items-center gap-3">
              <span class="font-bold text-green-600">+{{ score.points }}</span>
              <button
                @click="showAppealFor = showAppealFor === score.id ? null : score.id"
                class="text-xs text-gray-400 hover:text-blue-600 transition"
              >
                Апелляция
              </button>
            </div>
          </div>

          <!-- Форма апелляции -->
          <div v-if="showAppealFor" class="px-5 py-4 bg-blue-50">
            <textarea
              v-model="appealForm.reason"
              placeholder="Опишите причину апелляции..."
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"
              rows="3"
            ></textarea>
            <div class="flex gap-2 mt-2">
              <button
                @click="submitAppeal(showAppealFor)"
                :disabled="!appealForm.reason || appealLoading"
                class="bg-blue-600 text-white px-4 py-1.5 rounded-lg text-sm disabled:opacity-50"
              >
                Подать
              </button>
              <button @click="showAppealFor = null" class="text-gray-500 text-sm px-4 py-1.5">
                Отмена
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
