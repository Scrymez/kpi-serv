<script setup>
import { onMounted, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api'

const auth = useAuthStore()
const nominations = ref([])
const teachers = ref([])
const selected = ref({})
const loading = ref(false)
const savingId = ref(null)
const message = ref('')

async function fetchVotes() {
  const res = await api.get('/teacher-votes')
  nominations.value = res.data.nominations
  teachers.value = res.data.teachers
  selected.value = Object.fromEntries(nominations.value.map(n => [n.id, n.my_teacher_id || '']))
}

async function vote(nomination) {
  if (!selected.value[nomination.id]) return
  savingId.value = nomination.id
  message.value = ''
  try {
    await api.post('/teacher-votes', {
      nomination_id: nomination.id,
      teacher_id: selected.value[nomination.id],
    })
    await fetchVotes()
    message.value = 'Голос сохранен.'
  } finally {
    savingId.value = null
  }
}

function teacherName(id) {
  return teachers.value.find(t => t.id === id)?.full_name || '—'
}

onMounted(async () => {
  loading.value = true
  await fetchVotes()
  loading.value = false
})
</script>

<template>
  <div>
    <div class="mb-6">
      <h2 class="text-xl font-bold text-gray-800">Голосование за учителей</h2>
      <p class="text-sm text-gray-500 mt-1">Родители могут выбрать учителя в каждой номинации. Голос можно изменить до завершения голосования.</p>
    </div>

    <div v-if="message" class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 text-sm text-green-700">{{ message }}</div>
    <div v-if="!auth.isParent" class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 text-sm text-amber-800">
      Голосование доступно только пользователям с ролью родителя.
    </div>

    <div v-if="loading" class="text-center text-gray-400 py-10">Загрузка...</div>
    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <section v-for="nomination in nominations" :key="nomination.id" class="bg-white border rounded-xl p-5">
        <div class="flex items-start justify-between gap-4 mb-4">
          <div>
            <h3 class="font-semibold text-gray-800">{{ nomination.title }}</h3>
            <p v-if="nomination.description" class="text-sm text-gray-500 mt-1">{{ nomination.description }}</p>
          </div>
          <span class="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded-full">{{ nomination.votes_count }} голосов</span>
        </div>

        <div v-if="nomination.my_teacher_id" class="text-sm text-green-700 bg-green-50 border border-green-100 rounded-lg px-3 py-2 mb-3">
          Ваш выбор: {{ teacherName(nomination.my_teacher_id) }}
        </div>

        <div class="flex gap-2">
          <select v-model="selected[nomination.id]" :disabled="!auth.isParent" class="flex-1 border rounded-lg px-3 py-2 text-sm">
            <option value="">Выберите учителя...</option>
            <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
              {{ teacher.full_name }}{{ teacher.subject ? ` · ${teacher.subject}` : '' }}
            </option>
          </select>
          <button
            @click="vote(nomination)"
            :disabled="!auth.isParent || !selected[nomination.id] || savingId === nomination.id"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm disabled:opacity-50"
          >
            {{ savingId === nomination.id ? 'Сохраняем...' : 'Голосовать' }}
          </button>
        </div>
      </section>
    </div>
  </div>
</template>

