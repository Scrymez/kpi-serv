<script setup>
import { ref } from 'vue'
import api from '@/api'

const role = ref('student')
const file = ref(null)
const loading = ref(false)
const result = ref(null)
const error = ref('')

async function downloadTemplate() {
  const res = await api.get('/users/export-template', { params: { role: role.value }, responseType: 'blob' })
  const url = URL.createObjectURL(res.data)
  const a = document.createElement('a'); a.href = url; a.download = `шаблон_${role.value}.xlsx`; a.click()
}

async function handleImport() {
  if (!file.value) return
  loading.value = true
  error.value = ''
  result.value = null
  try {
    const form = new FormData()
    form.append('file', file.value)
    form.append('role', role.value)
    const res = await api.post('/users/import', form, { headers: { 'Content-Type': 'multipart/form-data' } })
    result.value = res.data
  } catch (e) {
    error.value = e.response?.data?.message || 'Ошибка импорта'
  } finally {
    loading.value = false
  }
}

async function downloadCredentials() {
  const ids = result.value.credentials.map(c => c.id)
  const res = await api.get('/users/export-credentials', { params: { ids }, responseType: 'blob' })
  const url = URL.createObjectURL(res.data)
  const a = document.createElement('a'); a.href = url; a.download = 'логины_пароли.xlsx'; a.click()
}
</script>

<template>
  <div>
    <h2 class="text-xl font-bold text-gray-800 mb-6">Импорт пользователей</h2>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Форма импорта -->
      <div class="bg-white rounded-xl border p-6 space-y-5">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Кого импортируем</label>
          <select v-model="role" class="border rounded-lg px-4 py-2 text-sm w-full">
            <option value="student">Ученики</option>
            <option value="parent">Родители</option>
            <option value="teacher">Учителя (только учителя)</option>
            <option value="staff">Сотрудники (с должностью)</option>
          </select>
        </div>

        <div>
          <button @click="downloadTemplate" class="text-sm text-blue-600 underline hover:text-blue-800">
            Скачать шаблон Excel
          </button>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Файл Excel (.xlsx)</label>
          <input
            type="file"
            accept=".xlsx,.xls"
            @change="e => file = e.target.files[0]"
            class="border rounded-lg px-4 py-2 text-sm w-full"
          />
        </div>

        <div v-if="error" class="text-red-600 text-sm bg-red-50 px-4 py-3 rounded-lg border border-red-200">{{ error }}</div>

        <button
          @click="handleImport"
          :disabled="!file || loading"
          class="bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-700 disabled:opacity-50 transition w-full"
        >
          {{ loading ? 'Импортируем...' : 'Загрузить файл' }}
        </button>

        <!-- Результат импорта -->
        <div v-if="result" class="bg-green-50 rounded-xl p-4 border border-green-200">
          <div class="font-semibold text-green-800 mb-2">Импортировано: {{ result.imported }} пользователей</div>
          <div v-if="result.errors.length" class="text-red-600 text-sm mb-2 space-y-0.5">
            <div v-for="e in result.errors" :key="e">{{ e }}</div>
          </div>
          <button @click="downloadCredentials" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm mt-2 hover:bg-green-700 transition">
            Скачать логины/пароли (.xlsx)
          </button>
        </div>
      </div>

      <!-- Инструкция по формату -->
      <div class="space-y-4">
        <!-- Ученики -->
        <div v-if="role === 'student'" class="bg-white rounded-xl border p-5">
          <h3 class="font-semibold text-gray-800 mb-3">Формат файла — Ученики</h3>
          <div class="overflow-x-auto">
            <table class="text-xs w-full">
              <thead>
                <tr class="bg-blue-50">
                  <th class="px-3 py-2 text-left font-semibold text-blue-800 rounded-tl">Столбец</th>
                  <th class="px-3 py-2 text-left font-semibold text-blue-800">Обязателен</th>
                  <th class="px-3 py-2 text-left font-semibold text-blue-800 rounded-tr">Описание / Пример</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr><td class="px-3 py-2 font-mono font-semibold text-gray-700">familiya</td><td class="px-3 py-2 text-green-600">Да</td><td class="px-3 py-2 text-gray-600">Фамилия — <em>Иванов</em></td></tr>
                <tr><td class="px-3 py-2 font-mono font-semibold text-gray-700">imya</td><td class="px-3 py-2 text-green-600">Да</td><td class="px-3 py-2 text-gray-600">Имя — <em>Иван</em></td></tr>
                <tr><td class="px-3 py-2 font-mono font-semibold text-gray-700">otchestvo</td><td class="px-3 py-2 text-gray-400">Нет</td><td class="px-3 py-2 text-gray-600">Отчество — <em>Иванович</em></td></tr>
                <tr><td class="px-3 py-2 font-mono font-semibold text-gray-700">vozrast</td><td class="px-3 py-2 text-gray-400">Нет</td><td class="px-3 py-2 text-gray-600">Возраст числом — <em>14</em></td></tr>
                <tr><td class="px-3 py-2 font-mono font-semibold text-gray-700">klass</td><td class="px-3 py-2 text-green-600">Да</td><td class="px-3 py-2 text-gray-600">Класс точно как в системе — <em>9А</em>, <em>11Б</em></td></tr>
              </tbody>
            </table>
          </div>
          <div class="mt-3 text-xs text-gray-500 bg-gray-50 rounded-lg p-3">
            <strong>Пример строки:</strong><br>
            <span class="font-mono">Иванов | Иван | Иванович | 14 | 9А</span>
          </div>
        </div>

        <div v-if="role === 'parent'" class="bg-white rounded-xl border p-5">
          <h3 class="font-semibold text-gray-800 mb-3">Формат файла — Родители</h3>
          <div class="overflow-x-auto">
            <table class="text-xs w-full">
              <thead>
                <tr class="bg-green-50">
                  <th class="px-3 py-2 text-left font-semibold text-green-800">Столбец</th>
                  <th class="px-3 py-2 text-left font-semibold text-green-800">Обязателен</th>
                  <th class="px-3 py-2 text-left font-semibold text-green-800">Описание / Пример</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr><td class="px-3 py-2 font-mono font-semibold text-gray-700">familiya</td><td class="px-3 py-2 text-green-600">Да</td><td class="px-3 py-2 text-gray-600">Фамилия — <em>Иванова</em></td></tr>
                <tr><td class="px-3 py-2 font-mono font-semibold text-gray-700">imya</td><td class="px-3 py-2 text-green-600">Да</td><td class="px-3 py-2 text-gray-600">Имя — <em>Ольга</em></td></tr>
                <tr><td class="px-3 py-2 font-mono font-semibold text-gray-700">otchestvo</td><td class="px-3 py-2 text-gray-400">Нет</td><td class="px-3 py-2 text-gray-600">Отчество — <em>Павловна</em></td></tr>
                <tr><td class="px-3 py-2 font-mono font-semibold text-gray-700">vozrast</td><td class="px-3 py-2 text-gray-400">Нет</td><td class="px-3 py-2 text-gray-600">Возраст — <em>39</em></td></tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Сотрудники -->
        <div v-if="role === 'teacher' || role === 'staff'" class="bg-white rounded-xl border p-5">
          <h3 class="font-semibold text-gray-800 mb-3">Формат файла — Сотрудники</h3>
          <div class="overflow-x-auto">
            <table class="text-xs w-full">
              <thead>
                <tr class="bg-purple-50">
                  <th class="px-3 py-2 text-left font-semibold text-purple-800">Столбец</th>
                  <th class="px-3 py-2 text-left font-semibold text-purple-800">Обязателен</th>
                  <th class="px-3 py-2 text-left font-semibold text-purple-800">Описание / Пример</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr><td class="px-3 py-2 font-mono font-semibold text-gray-700">familiya</td><td class="px-3 py-2 text-green-600">Да</td><td class="px-3 py-2 text-gray-600">Фамилия — <em>Петрова</em></td></tr>
                <tr><td class="px-3 py-2 font-mono font-semibold text-gray-700">imya</td><td class="px-3 py-2 text-green-600">Да</td><td class="px-3 py-2 text-gray-600">Имя — <em>Анна</em></td></tr>
                <tr><td class="px-3 py-2 font-mono font-semibold text-gray-700">otchestvo</td><td class="px-3 py-2 text-gray-400">Нет</td><td class="px-3 py-2 text-gray-600">Отчество — <em>Петровна</em></td></tr>
                <tr><td class="px-3 py-2 font-mono font-semibold text-gray-700">vozrast</td><td class="px-3 py-2 text-gray-400">Нет</td><td class="px-3 py-2 text-gray-600">Возраст — <em>38</em></td></tr>
                <tr v-if="role === 'staff'"><td class="px-3 py-2 font-mono font-semibold text-gray-700">dolzhnost</td><td class="px-3 py-2 text-green-600">Да</td><td class="px-3 py-2 text-gray-600">Должность (см. допустимые ниже)</td></tr>
                <tr><td class="px-3 py-2 font-mono font-semibold text-gray-700">predmet</td><td class="px-3 py-2 text-gray-400">Нет</td><td class="px-3 py-2 text-gray-600">Предмет для учителей — <em>Математика</em></td></tr>
              </tbody>
            </table>
          </div>

          <div v-if="role === 'staff'" class="mt-3 text-xs bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-gray-700">
            <strong class="text-yellow-800">Допустимые значения столбца <span class="font-mono">dolzhnost</span>:</strong>
            <div class="mt-1 grid grid-cols-1 gap-0.5 font-mono">
              <span>учитель</span>
              <span>директор</span>
              <span>зам. по мероприятиям</span>
              <span>зам. по УВР</span>
              <span>зам. по НТР</span>
            </div>
          </div>

          <div class="mt-3 text-xs text-gray-500 bg-gray-50 rounded-lg p-3">
            <strong>Пример строки (staff):</strong><br>
            <span class="font-mono">Петрова | Анна | Петровна | 38 | учитель | Математика</span>
          </div>
        </div>

        <!-- Общие правила -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-xs text-blue-800">
          <strong>Важно:</strong>
          <ul class="mt-1 list-disc list-inside space-y-0.5 text-blue-700">
            <li>Первая строка — заголовки (точно как указано выше)</li>
            <li>Классы должны быть заранее созданы в системе</li>
            <li>Регистр заголовков не важен</li>
            <li>Логин и пароль генерируются автоматически</li>
            <li>Скачайте шаблон — он уже содержит правильные заголовки</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>
