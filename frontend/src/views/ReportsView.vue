<script setup>
import api from '@/api'

async function downloadTeachers() {
  const res = await api.get('/reports/teachers', { responseType: 'blob' })
  const url = URL.createObjectURL(res.data)
  const a = document.createElement('a'); a.href = url; a.download = 'отчёт_учителя.xlsx'; a.click()
}

async function downloadOlympiads() {
  const res = await api.get('/reports/olympiads', { responseType: 'blob' })
  const url = URL.createObjectURL(res.data)
  const a = document.createElement('a'); a.href = url; a.download = 'отчёт_олимпиады.xlsx'; a.click()
}
</script>

<template>
  <div>
    <h2 class="text-xl font-bold text-gray-800 mb-6">Отчёты</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="bg-white rounded-xl border p-6">
        <div class="text-2xl mb-3">👨‍🏫</div>
        <h3 class="font-semibold text-gray-800 mb-1">Рейтинг учителей</h3>
        <p class="text-sm text-gray-500 mb-4">KPI баллы, количество регистраций и побед учеников</p>
        <button @click="downloadTeachers" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">
          Скачать Excel
        </button>
      </div>
      <div class="bg-white rounded-xl border p-6">
        <div class="text-2xl mb-3">🏆</div>
        <h3 class="font-semibold text-gray-800 mb-1">Отчёт по олимпиадам</h3>
        <p class="text-sm text-gray-500 mb-4">Все олимпиады, участие и статистика</p>
        <button @click="downloadOlympiads" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">
          Скачать Excel
        </button>
      </div>
    </div>
  </div>
</template>
