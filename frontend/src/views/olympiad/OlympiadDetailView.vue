<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/api'

const route = useRoute()
const auth = useAuthStore()
const olympiad = ref(null)
const registering = ref(false)
const regForm = ref({ student_id: '', teacher2_id: '' })
const students = ref([])
const uploadForm = ref({ place: '', document: null })
const uploadingFor = ref(null)

onMounted(async () => {
  const [oRes, sRes] = await Promise.all([
    api.get(`/olympiads/${route.params.id}`),
    auth.isTeacher || auth.canVerify ? api.get('/users', { params: { role: 'student' } }) : Promise.resolve({ data: { data: [] } })
  ])
  olympiad.value = oRes.data
  students.value = sRes.data.data || []
})

async function register() {
  await api.post('/registrations', {
    olympiad_id: olympiad.value.id,
    student_id: regForm.value.student_id,
    teacher2_id: regForm.value.teacher2_id || null,
  })
  const res = await api.get(`/olympiads/${route.params.id}`)
  olympiad.value = res.data
  regForm.value = { student_id: '', teacher2_id: '' }
  registering.value = false
}

async function uploadResult(regId) {
  const form = new FormData()
  form.append('document', uploadForm.value.document)
  if (uploadForm.value.place) form.append('place', uploadForm.value.place)
  await api.post(`/results/${regId}`, form, { headers: { 'Content-Type': 'multipart/form-data' } })
  uploadingFor.value = null
  const res = await api.get(`/olympiads/${route.params.id}`)
  olympiad.value = res.data
}

function formatDate(d) { return new Date(d).toLocaleDateString('ru-RU') }
</script>

<template>
  <div v-if="olympiad">
    <div class="flex items-start justify-between mb-6">
      <div>
        <router-link to="/olympiads" class="text-sm text-blue-600 hover:underline mb-2 block">← Назад</router-link>
        <h2 class="text-xl font-bold text-gray-800">{{ olympiad.title }}</h2>
        <div class="text-sm text-gray-500 mt-1">
          {{ formatDate(olympiad.start_date) }} — {{ formatDate(olympiad.end_date) }}
          <span v-if="olympiad.subject"> · {{ olympiad.subject.name }}</span>
        </div>
      </div>
      <button
        v-if="!auth.isStudent"
        @click="registering = !registering"
        class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition"
      >
        + Зарегистрировать ученика
      </button>
    </div>

    <p v-if="olympiad.description" class="text-gray-600 mb-6">{{ olympiad.description }}</p>

    <!-- Форма регистрации -->
    <div v-if="registering" class="bg-blue-50 rounded-xl border border-blue-200 p-5 mb-6">
      <h3 class="font-semibold text-gray-800 mb-3">Регистрация ученика</h3>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="text-sm text-gray-600 mb-1 block">Ученик *</label>
          <select v-model="regForm.student_id" class="w-full border rounded-lg px-3 py-2 text-sm">
            <option value="">Выберите...</option>
            <option v-for="s in students" :key="s.id" :value="s.id">{{ s.last_name }} {{ s.first_name }} ({{ s.school_class?.name }})</option>
          </select>
        </div>
      </div>
      <div class="flex gap-2 mt-3">
        <button @click="register" :disabled="!regForm.student_id" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm disabled:opacity-50">
          Зарегистрировать
        </button>
        <button @click="registering = false" class="text-gray-500 text-sm px-4">Отмена</button>
      </div>
    </div>

    <!-- Участники -->
    <div class="bg-white rounded-xl border">
      <div class="px-5 py-4 border-b font-semibold text-gray-700">
        Участники ({{ olympiad.registrations?.length || 0 }})
      </div>
      <div v-if="!olympiad.registrations?.length" class="px-5 py-8 text-center text-gray-400">
        Нет зарегистрированных участников
      </div>
      <div v-else class="divide-y">
        <div v-for="reg in olympiad.registrations" :key="reg.id" class="px-5 py-4 flex items-center justify-between">
          <div>
            <div class="font-medium text-gray-800">{{ reg.student?.full_name }}</div>
            <div class="text-xs text-gray-400">
              Статус: {{ reg.status }}
              <span v-if="reg.result?.status"> · Результат: {{ reg.result.status }}</span>
            </div>
          </div>
          <button
            v-if="reg.result?.status === 'pending_upload'"
            @click="uploadingFor = uploadingFor === reg.id ? null : reg.id"
            class="text-sm text-blue-600 hover:underline"
          >
            Загрузить результат
          </button>
          <span v-else-if="reg.result?.place" class="text-green-600 font-bold">
            {{ reg.result.place }} место
          </span>
        </div>

        <!-- Форма загрузки -->
        <div v-if="uploadingFor" class="px-5 py-4 bg-gray-50">
          <div class="flex gap-3 items-center">
            <input type="number" v-model="uploadForm.place" placeholder="Место (1-3)" min="1" max="3"
              class="border rounded-lg px-3 py-2 text-sm w-24" />
            <input type="file" accept=".pdf,.jpg,.jpeg,.png" @change="e => uploadForm.document = e.target.files[0]"
              class="text-sm" />
            <button @click="uploadResult(uploadingFor)" :disabled="!uploadForm.document"
              class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm disabled:opacity-50">
              Загрузить
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
