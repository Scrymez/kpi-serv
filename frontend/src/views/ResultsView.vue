<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api'

const auth = useAuthStore()
const results = ref([])
const loading = ref(false)

async function fetchResults() {
  loading.value = true
  try {
    const res = await api.get('/results', { params: { status: auth.canVerify ? null : null } })
    results.value = res.data.data
  } finally {
    loading.value = false
  }
}

function statusLabel(status) {
  const map = {
    pending_upload: 'Ожидает загрузки',
    pending_verification: 'На проверке',
    approved: 'Подтверждён',
    rejected: 'Отклонён',
  }
  return map[status] || status
}

function statusClass(status) {
  const map = {
    pending_upload: 'bg-gray-100 text-gray-600',
    pending_verification: 'bg-amber-100 text-amber-700',
    approved: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700',
  }
  return map[status] || ''
}

onMounted(fetchResults)
</script>

<template>
  <div>
    <h2 class="text-xl font-bold text-gray-800 mb-6">Результаты олимпиад</h2>

    <div v-if="loading" class="text-gray-400">Загрузка...</div>

    <div v-else-if="results.length === 0" class="bg-white rounded-xl border p-8 text-center text-gray-400">
      Результаты не найдены
    </div>

    <div v-else class="bg-white rounded-xl border overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="px-4 py-3 text-left text-gray-600">Ученик</th>
            <th class="px-4 py-3 text-left text-gray-600">Олимпиада</th>
            <th class="px-4 py-3 text-left text-gray-600">Место</th>
            <th class="px-4 py-3 text-left text-gray-600">Статус</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="r in results" :key="r.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium">{{ r.registration?.student?.full_name || '—' }}</td>
            <td class="px-4 py-3 text-gray-600">{{ r.registration?.olympiad?.title || '—' }}</td>
            <td class="px-4 py-3">{{ r.place ? `${r.place} место` : '—' }}</td>
            <td class="px-4 py-3">
              <span :class="['text-xs px-2 py-1 rounded-full', statusClass(r.status)]">
                {{ statusLabel(r.status) }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
