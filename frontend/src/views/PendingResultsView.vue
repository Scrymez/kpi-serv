<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api'

const results = ref([])
const loading = ref(false)
const rejectReason = ref('')
const rejectingId = ref(null)

async function fetchPending() {
  loading.value = true
  try {
    const res = await api.get('/results/pending')
    results.value = res.data
  } finally {
    loading.value = false
  }
}

async function verify(resultId) {
  await api.put(`/results/${resultId}/verify`)
  await fetchPending()
}

async function reject(resultId) {
  if (!rejectReason.value.trim()) return
  await api.put(`/results/${resultId}/reject`, { reason: rejectReason.value })
  rejectingId.value = null
  rejectReason.value = ''
  await fetchPending()
}

function formatDate(d) {
  return new Date(d).toLocaleDateString('ru-RU')
}

onMounted(fetchPending)
</script>

<template>
  <div>
    <h2 class="text-xl font-bold text-gray-800 mb-6">Ожидают верификации</h2>

    <div v-if="loading" class="text-gray-400">Загрузка...</div>

    <div v-else-if="results.length === 0" class="bg-white rounded-xl border p-8 text-center text-gray-400">
      Нет результатов ожидающих верификации
    </div>

    <div v-else class="space-y-4">
      <div v-for="r in results" :key="r.id" class="bg-white rounded-xl border p-5">
        <div class="flex items-start justify-between gap-4 mb-3">
          <div>
            <div class="font-semibold text-gray-800">{{ r.registration?.student?.full_name }}</div>
            <div class="text-sm text-gray-500">{{ r.registration?.olympiad?.title }}</div>
            <div class="text-xs text-gray-400 mt-1">
              Загружено: {{ formatDate(r.uploaded_at) }}
              <span v-if="r.place"> · Место: {{ r.place }}</span>
            </div>
          </div>
          <div class="flex gap-2">
            <button
              @click="verify(r.id)"
              class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition"
            >
              Подтвердить
            </button>
            <button
              @click="rejectingId = rejectingId === r.id ? null : r.id"
              class="bg-red-100 text-red-600 px-4 py-2 rounded-lg text-sm hover:bg-red-200 transition"
            >
              Отклонить
            </button>
          </div>
        </div>

        <div v-if="r.document_path" class="text-xs text-blue-600">
          📎 {{ r.document_original_name }}
        </div>

        <!-- Форма отклонения -->
        <div v-if="rejectingId === r.id" class="mt-3 pt-3 border-t">
          <textarea
            v-model="rejectReason"
            placeholder="Причина отклонения..."
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm resize-none"
            rows="2"
          ></textarea>
          <div class="flex gap-2 mt-2">
            <button @click="reject(r.id)" class="bg-red-600 text-white px-4 py-1.5 rounded-lg text-sm">
              Отклонить
            </button>
            <button @click="rejectingId = null; rejectReason = ''" class="text-gray-500 text-sm px-4">
              Отмена
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
