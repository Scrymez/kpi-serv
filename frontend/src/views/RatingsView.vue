<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api'

const teachers = ref([])
const students = ref([])
const tab = ref('teachers')
const educationLevel = ref('')
const loading = ref(false)

const levels = [
  { value: '', label: 'Все уровни' },
  { value: 'primary', label: 'Начальное (1-4)' },
  { value: 'basic', label: 'Основное (5-9)' },
  { value: 'secondary', label: 'Среднее (10-11)' },
]

async function fetchTeachers() {
  const res = await api.get('/ratings/teachers')
  teachers.value = res.data
}

async function fetchStudents() {
  const res = await api.get('/ratings/students', { params: { education_level: educationLevel.value } })
  students.value = res.data
}

onMounted(async () => {
  loading.value = true
  await Promise.all([fetchTeachers(), fetchStudents()])
  loading.value = false
})

function medal(index) {
  return ['🥇', '🥈', '🥉'][index] || `${index + 1}.`
}
</script>

<template>
  <div>
    <h2 class="text-xl font-bold text-gray-800 mb-6">Рейтинги</h2>

    <div class="flex gap-2 mb-6">
      <button
        @click="tab = 'teachers'"
        :class="['px-4 py-2 rounded-lg text-sm font-medium transition', tab === 'teachers' ? 'bg-blue-600 text-white' : 'bg-white border text-gray-600 hover:border-blue-300']"
      >
        Учителя
      </button>
      <button
        @click="tab = 'students'"
        :class="['px-4 py-2 rounded-lg text-sm font-medium transition', tab === 'students' ? 'bg-blue-600 text-white' : 'bg-white border text-gray-600 hover:border-blue-300']"
      >
        Ученики
      </button>
    </div>

    <!-- Рейтинг учителей -->
    <div v-if="tab === 'teachers'">
      <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-4 py-3 text-left text-gray-600">#</th>
              <th class="px-4 py-3 text-left text-gray-600">Учитель</th>
              <th class="px-4 py-3 text-left text-gray-600">Предмет</th>
              <th class="px-4 py-3 text-right text-gray-600">KPI баллы</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="(t, i) in teachers" :key="t.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-bold text-lg">{{ medal(i) }}</td>
              <td class="px-4 py-3 font-medium text-gray-800">{{ t.full_name }}</td>
              <td class="px-4 py-3 text-gray-500">{{ t.subject || '—' }}</td>
              <td class="px-4 py-3 text-right font-bold text-blue-600">{{ t.total_kpi }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Рейтинг учеников -->
    <div v-if="tab === 'students'">
      <div class="mb-4">
        <select
          v-model="educationLevel"
          @change="fetchStudents"
          class="border border-gray-300 rounded-lg px-4 py-2 text-sm"
        >
          <option v-for="l in levels" :key="l.value" :value="l.value">{{ l.label }}</option>
        </select>
      </div>
      <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-4 py-3 text-left text-gray-600">#</th>
              <th class="px-4 py-3 text-left text-gray-600">Ученик</th>
              <th class="px-4 py-3 text-left text-gray-600">Класс</th>
              <th class="px-4 py-3 text-right text-gray-600">Участий</th>
              <th class="px-4 py-3 text-right text-gray-600">Побед</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="(s, i) in students" :key="s.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-bold text-lg">{{ medal(i) }}</td>
              <td class="px-4 py-3 font-medium text-gray-800">{{ s.full_name }}</td>
              <td class="px-4 py-3 text-gray-500">{{ s.class || '—' }}</td>
              <td class="px-4 py-3 text-right text-gray-600">{{ s.participations }}</td>
              <td class="px-4 py-3 text-right font-bold text-green-600">{{ s.wins }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
