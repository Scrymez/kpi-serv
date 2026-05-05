<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api'

const activeTab = ref('kpi')

// KPI настройки
const kpiSettings = ref({})
const kpiForm = ref({})
const kpiLoading = ref(false)
const kpiSaving = ref(false)
const kpiSuccess = ref(false)

// AI настройки
const aiSettings = ref({})
const aiForm = ref({ gemini_api_key: '', gemini_system_prompt: '' })
const aiLoading = ref(false)
const aiSaving = ref(false)
const aiSuccess = ref(false)
const aiError = ref('')
const showKey = ref(false)
const testLoading = ref(false)
const testResult = ref(null)

onMounted(async () => {
  await Promise.all([loadKpi(), loadAi()])
})

async function loadKpi() {
  kpiLoading.value = true
  try {
    const res = await api.get('/kpi-settings')
    kpiSettings.value = res.data
    kpiForm.value = Object.fromEntries(Object.entries(res.data).map(([k, v]) => [k, v.value]))
  } finally { kpiLoading.value = false }
}

async function saveKpi() {
  kpiSaving.value = true
  try {
    await api.put('/kpi-settings', kpiForm.value)
    kpiSuccess.value = true
    setTimeout(() => kpiSuccess.value = false, 3000)
  } finally { kpiSaving.value = false }
}

async function loadAi() {
  aiLoading.value = true
  try {
    const res = await api.get('/ai-settings')
    aiSettings.value = res.data
    aiForm.value.gemini_api_key = res.data.gemini_api_key?.value || ''
    aiForm.value.gemini_system_prompt = res.data.gemini_system_prompt?.value || ''
  } finally { aiLoading.value = false }
}

async function saveAi() {
  aiSaving.value = true; aiError.value = ''
  try {
    await api.put('/ai-settings', aiForm.value)
    aiSuccess.value = true
    setTimeout(() => aiSuccess.value = false, 3000)
  } catch (e) {
    aiError.value = e.response?.data?.message || 'Ошибка сохранения'
  } finally { aiSaving.value = false }
}

async function testKey() {
  testLoading.value = true; testResult.value = null
  try {
    // Сначала сохраняем, потом тестируем
    await api.put('/ai-settings', { gemini_api_key: aiForm.value.gemini_api_key })
    const res = await api.post('/ai-settings/test')
    testResult.value = res.data
  } catch (e) {
    testResult.value = { ok: false, message: e.response?.data?.message || 'Ошибка' }
  } finally { testLoading.value = false }
}
</script>

<template>
  <div>
    <h2 class="text-xl font-bold text-gray-800 mb-6">Настройки системы</h2>

    <!-- Вкладки -->
    <div class="flex gap-1 mb-6 bg-gray-100 rounded-xl p-1 w-fit">
      <button
        @click="activeTab = 'kpi'"
        :class="['px-5 py-2 rounded-lg text-sm font-medium transition', activeTab === 'kpi' ? 'bg-white shadow text-blue-700' : 'text-gray-500 hover:text-gray-700']"
      >
        📊 KPI баллы
      </button>
      <button
        @click="activeTab = 'ai'"
        :class="['px-5 py-2 rounded-lg text-sm font-medium transition', activeTab === 'ai' ? 'bg-white shadow text-blue-700' : 'text-gray-500 hover:text-gray-700']"
      >
        🤖 AI агент
      </button>
    </div>

    <!-- KPI настройки -->
    <div v-if="activeTab === 'kpi'">
      <div v-if="kpiLoading" class="text-gray-400">Загрузка...</div>
      <form v-else @submit.prevent="saveKpi" class="max-w-lg space-y-4">
        <div
          v-for="(meta, key) in kpiSettings"
          :key="key"
          class="bg-white rounded-xl border p-4 flex items-center gap-4"
        >
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700">{{ meta.description }}</label>
          </div>
          <input
            v-model="kpiForm[key]"
            type="number"
            step="0.5"
            min="0"
            class="w-24 border border-gray-300 rounded-lg px-3 py-2 text-sm text-center font-mono focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div v-if="kpiSuccess" class="text-green-600 text-sm bg-green-50 px-4 py-3 rounded-lg border border-green-200">
          ✅ Настройки KPI сохранены!
        </div>

        <button type="submit" :disabled="kpiSaving"
          class="bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-700 disabled:opacity-50 transition">
          {{ kpiSaving ? 'Сохраняем...' : 'Сохранить KPI настройки' }}
        </button>
      </form>
    </div>

    <!-- AI настройки -->
    <div v-if="activeTab === 'ai'">
      <div v-if="aiLoading" class="text-gray-400">Загрузка...</div>
      <div v-else class="max-w-2xl space-y-5">

        <!-- Инфо-блок -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
          <div class="font-semibold mb-1">Как получить Gemini API ключ (бесплатно)</div>
          <ol class="list-decimal list-inside space-y-1 text-blue-700">
            <li>Перейдите на <span class="font-mono">aistudio.google.com</span></li>
            <li>Войдите через Google аккаунт</li>
            <li>Нажмите <strong>Get API Key → Create API key</strong></li>
            <li>Скопируйте ключ и вставьте ниже</li>
          </ol>
          <div class="mt-2 text-blue-600">Бесплатный лимит: <strong>1500 запросов/день</strong></div>
        </div>

        <!-- API ключ -->
        <div class="bg-white rounded-xl border p-5 space-y-3">
          <label class="block text-sm font-semibold text-gray-700">Gemini API ключ</label>
          <div class="flex gap-2">
            <div class="relative flex-1">
              <input
                v-model="aiForm.gemini_api_key"
                :type="showKey ? 'text' : 'password'"
                placeholder="AIza..."
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 pr-12"
              />
              <button
                type="button"
                @click="showKey = !showKey"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs"
              >
                {{ showKey ? '🙈' : '👁' }}
              </button>
            </div>
            <button
              @click="testKey"
              :disabled="!aiForm.gemini_api_key || testLoading"
              class="bg-purple-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-purple-700 disabled:opacity-50 transition whitespace-nowrap"
            >
              {{ testLoading ? '⏳' : '🧪 Тест' }}
            </button>
          </div>

          <!-- Результат теста -->
          <div v-if="testResult" :class="['text-sm px-4 py-2 rounded-lg', testResult.ok ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200']">
            {{ testResult.ok ? '✅' : '❌' }} {{ testResult.message }}
          </div>
        </div>

        <!-- Системный промпт -->
        <div class="bg-white rounded-xl border p-5 space-y-3">
          <div>
            <label class="block text-sm font-semibold text-gray-700">Системный промпт AI агента</label>
            <p class="text-xs text-gray-400 mt-0.5">Инструкция для AI — что и как искать. Можно настроить под нужды вашей школы.</p>
          </div>
          <textarea
            v-model="aiForm.gemini_system_prompt"
            rows="6"
            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
            placeholder="Ты помощник для поиска школьных олимпиад..."
          ></textarea>
          <div class="text-xs text-gray-400">{{ aiForm.gemini_system_prompt.length }} / 2000 символов</div>
        </div>

        <div v-if="aiError" class="bg-red-50 text-red-600 text-sm px-4 py-3 rounded-lg border border-red-200">
          ❌ {{ aiError }}
        </div>
        <div v-if="aiSuccess" class="bg-green-50 text-green-600 text-sm px-4 py-3 rounded-lg border border-green-200">
          ✅ AI настройки сохранены!
        </div>

        <button @click="saveAi" :disabled="aiSaving"
          class="bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-700 disabled:opacity-50 transition">
          {{ aiSaving ? 'Сохраняем...' : 'Сохранить AI настройки' }}
        </button>
      </div>
    </div>
  </div>
</template>
