<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api'

const router = useRouter()
const subjects = ref([])
const loading = ref(false)
const error = ref('')

const form = ref({
  title: '', description: '', subject_id: '', level: 'federal',
  start_date: '', end_date: '', result_deadline: '', source_url: ''
})

const levels = [
  { value: 'school', label: 'Школьный' },
  { value: 'municipal', label: 'Муниципальный' },
  { value: 'regional', label: 'Региональный' },
  { value: 'federal', label: 'Федеральный' },
  { value: 'international', label: 'Международный' },
]

onMounted(async () => {
  const res = await api.get('/subjects')
  subjects.value = res.data
})

async function submit() {
  loading.value = true; error.value = ''
  try {
    await api.post('/olympiads', form.value)
    router.push('/olympiads')
  } catch (e) {
    error.value = Object.values(e.response?.data?.errors || {}).flat().join(', ') || 'Ошибка'
  } finally { loading.value = false }
}
</script>

<template>
  <div>
    <h2 class="text-xl font-bold text-gray-800 mb-6">Добавить олимпиаду</h2>
    <form @submit.prevent="submit" class="max-w-xl bg-white rounded-xl border p-6 space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Название *</label>
        <input v-model="form.title" required class="w-full border rounded-lg px-4 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Описание</label>
        <textarea v-model="form.description" rows="3" class="w-full border rounded-lg px-4 py-2 text-sm resize-none"></textarea>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Предмет</label>
          <select v-model="form.subject_id" class="w-full border rounded-lg px-4 py-2 text-sm">
            <option value="">Не указан</option>
            <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Уровень *</label>
          <select v-model="form.level" class="w-full border rounded-lg px-4 py-2 text-sm">
            <option v-for="l in levels" :key="l.value" :value="l.value">{{ l.label }}</option>
          </select>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Дата начала *</label>
          <input v-model="form.start_date" type="date" required class="w-full border rounded-lg px-4 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Дата окончания *</label>
          <input v-model="form.end_date" type="date" required class="w-full border rounded-lg px-4 py-2 text-sm" />
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Дедлайн загрузки результатов</label>
        <input v-model="form.result_deadline" type="date" class="w-full border rounded-lg px-4 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Ссылка на источник</label>
        <input v-model="form.source_url" type="url" placeholder="https://..." class="w-full border rounded-lg px-4 py-2 text-sm" />
      </div>
      <div v-if="error" class="text-red-600 text-sm bg-red-50 px-4 py-3 rounded-lg">{{ error }}</div>
      <div class="flex gap-3">
        <button type="submit" :disabled="loading" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-700 disabled:opacity-50">
          {{ loading ? 'Сохраняем...' : 'Сохранить' }}
        </button>
        <router-link to="/olympiads" class="text-gray-500 text-sm px-4 py-2.5">Отмена</router-link>
      </div>
    </form>
  </div>
</template>
