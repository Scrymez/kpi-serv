<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api'

const users = ref([])
const loading = ref(false)
const search = ref('')
const roleFilter = ref('')

const showModal = ref(false)
const saving = ref(false)
const createdUser = ref(null)
const classes = ref([])
const subjects = ref([])

const roleOptions = [
  { value: 'student', label: 'Ученик' },
  { value: 'teacher', label: 'Учитель' },
  { value: 'director', label: 'Директор' },
  { value: 'deputy_events', label: 'Зам. по мероприятиям' },
  { value: 'deputy_edu', label: 'Зам. по УВР' },
  { value: 'deputy_science', label: 'Зам. по НТР' },
]

const form = ref({
  last_name: '',
  first_name: '',
  middle_name: '',
  age: '',
  role: 'student',
  class_id: '',
  subject_id: '',
})

const roles = [
  { value: '', label: 'Все роли' },
  { value: 'admin', label: 'Администратор' },
  { value: 'director', label: 'Директор' },
  { value: 'deputy_events', label: 'Зам. по мероприятиям' },
  { value: 'deputy_edu', label: 'Зам. по УВР' },
  { value: 'deputy_science', label: 'Зам. по НТР' },
  { value: 'teacher', label: 'Учитель' },
  { value: 'student', label: 'Ученик' },
]

async function fetchUsers() {
  loading.value = true
  try {
    const res = await api.get('/users', { params: { search: search.value, role: roleFilter.value } })
    users.value = res.data.data
  } finally {
    loading.value = false
  }
}

async function toggleActive(user) {
  await api.put(`/users/${user.id}`, { is_active: !user.is_active })
  await fetchUsers()
}

async function openModal() {
  createdUser.value = null
  form.value = { last_name: '', first_name: '', middle_name: '', age: '', role: 'student', class_id: '', subject_id: '' }
  showModal.value = true
  if (!classes.value.length) {
    const [c, s] = await Promise.all([api.get('/classes'), api.get('/subjects')])
    classes.value = c.data
    subjects.value = s.data
  }
}

async function submitCreate() {
  saving.value = true
  try {
    const payload = { ...form.value }
    if (!payload.age) delete payload.age
    if (!payload.class_id) delete payload.class_id
    if (!payload.subject_id) delete payload.subject_id
    if (!payload.middle_name) delete payload.middle_name
    const res = await api.post('/users', payload)
    createdUser.value = res.data
    await fetchUsers()
  } finally {
    saving.value = false
  }
}

function closeModal() {
  showModal.value = false
  createdUser.value = null
}

onMounted(fetchUsers)
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-bold text-gray-800">Пользователи</h2>
      <div class="flex gap-2">
        <button @click="openModal" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition">
          + Добавить
        </button>
        <router-link to="/users/import" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">
          Импорт Excel
        </router-link>
      </div>
    </div>

    <div class="flex gap-3 mb-6">
      <input v-model="search" @input="fetchUsers" placeholder="Поиск..." class="border rounded-lg px-4 py-2 text-sm flex-1" />
      <select v-model="roleFilter" @change="fetchUsers" class="border rounded-lg px-4 py-2 text-sm">
        <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
      </select>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="px-4 py-3 text-left text-gray-600">ФИО</th>
            <th class="px-4 py-3 text-left text-gray-600">Логин</th>
            <th class="px-4 py-3 text-left text-gray-600">Роль</th>
            <th class="px-4 py-3 text-left text-gray-600">Класс/Предмет</th>
            <th class="px-4 py-3 text-left text-gray-600">Статус</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="u in users" :key="u.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium">{{ u.last_name }} {{ u.first_name }}</td>
            <td class="px-4 py-3 font-mono text-gray-500">{{ u.login }}</td>
            <td class="px-4 py-3 text-gray-500">{{ roles.find(r => r.value === u.role)?.label }}</td>
            <td class="px-4 py-3 text-gray-500">{{ u.school_class?.name || u.subject?.name || '—' }}</td>
            <td class="px-4 py-3">
              <span :class="u.is_active ? 'text-green-600' : 'text-gray-400'">
                {{ u.is_active ? 'Активен' : 'Заблокирован' }}
              </span>
            </td>
            <td class="px-4 py-3">
              <button @click="toggleActive(u)" class="text-xs text-blue-500 hover:underline">
                {{ u.is_active ? 'Заблокировать' : 'Активировать' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Модальное окно создания пользователя -->
    <div v-if="showModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b">
          <h3 class="text-lg font-bold text-gray-800">Добавить пользователя</h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>

        <!-- Результат создания -->
        <div v-if="createdUser" class="p-6 space-y-4">
          <div class="bg-green-50 border border-green-200 rounded-xl p-4">
            <div class="text-green-800 font-semibold mb-3">Пользователь создан!</div>
            <div class="space-y-1 text-sm">
              <div><span class="text-gray-500">ФИО:</span> <span class="font-medium">{{ createdUser.user.last_name }} {{ createdUser.user.first_name }} {{ createdUser.user.middle_name }}</span></div>
              <div><span class="text-gray-500">Логин:</span> <span class="font-mono font-semibold text-blue-700">{{ createdUser.user.login }}</span></div>
              <div><span class="text-gray-500">Пароль:</span> <span class="font-mono font-semibold text-green-700">{{ createdUser.plain_password }}</span></div>
            </div>
            <p class="text-xs text-gray-400 mt-3">Запишите пароль — он больше не будет показан.</p>
          </div>
          <div class="flex gap-2">
            <button @click="form.role = form.role; createdUser = null" class="flex-1 border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-50">
              Добавить ещё
            </button>
            <button @click="closeModal" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
              Готово
            </button>
          </div>
        </div>

        <!-- Форма -->
        <form v-else @submit.prevent="submitCreate" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Роль</label>
            <select v-model="form.role" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option v-for="r in roleOptions" :key="r.value" :value="r.value">{{ r.label }}</option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Фамилия *</label>
              <input v-model="form.last_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Иванов" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Имя *</label>
              <input v-model="form.first_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Иван" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Отчество</label>
              <input v-model="form.middle_name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Иванович" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Возраст</label>
            <input v-model="form.age" type="number" min="5" max="80" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="14" />
          </div>

          <!-- Класс — только для ученика -->
          <div v-if="form.role === 'student'">
            <label class="block text-sm font-medium text-gray-700 mb-1">Класс</label>
            <select v-model="form.class_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">— не указан —</option>
              <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>

          <!-- Предмет — только для учителя -->
          <div v-if="form.role === 'teacher'">
            <label class="block text-sm font-medium text-gray-700 mb-1">Предмет</label>
            <select v-model="form.subject_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">— не указан —</option>
              <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>

          <div class="flex gap-2 pt-2">
            <button type="button" @click="closeModal" class="flex-1 border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-50">
              Отмена
            </button>
            <button type="submit" :disabled="saving" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 disabled:opacity-50 transition">
              {{ saving ? 'Создаём...' : 'Создать' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
