<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api'

const auth = useAuthStore()
const appeals = ref([])
const loading = ref(false)

async function fetchAppeals() {
  loading.value = true
  try {
    const res = await api.get('/kpi/appeals')
    appeals.value = res.data
  } finally {
    loading.value = false
  }
}

async function resolve(id, status) {
  const note = status === 'rejected' ? prompt('Причина отклонения:') : ''
  await api.put(`/kpi/appeals/${id}`, { status, resolution_note: note })
  await fetchAppeals()
}

onMounted(fetchAppeals)
</script>

<template>
  <div>
    <h2 class="text-xl font-bold text-gray-800 mb-6">Апелляции KPI</h2>

    <div v-if="loading" class="text-gray-400">Загрузка...</div>
    <div v-else-if="!appeals.length" class="bg-white rounded-xl border p-8 text-center text-gray-400">
      Нет pending апелляций
    </div>

    <div v-else class="space-y-3">
      <div v-for="a in appeals" :key="a.id" class="bg-white rounded-xl border p-5">
        <div class="flex items-start justify-between gap-4">
          <div>
            <div class="font-semibold text-gray-800">{{ a.user?.full_name }}</div>
            <div class="text-sm text-gray-500 mt-1">{{ a.kpi_score?.reason }}</div>
            <div class="text-sm text-gray-600 mt-2 bg-gray-50 rounded-lg px-3 py-2">{{ a.reason }}</div>
          </div>
          <div v-if="auth.canVerify" class="flex gap-2 shrink-0">
            <button @click="resolve(a.id, 'approved')" class="bg-green-600 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-green-700">
              Одобрить
            </button>
            <button @click="resolve(a.id, 'rejected')" class="bg-red-100 text-red-600 px-3 py-1.5 rounded-lg text-sm hover:bg-red-200">
              Отклонить
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
