<script setup>
import { onMounted, onUnmounted, ref, nextTick } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api'

const auth = useAuthStore()
const messages = ref([])
const body = ref('')
const loading = ref(false)
const sending = ref(false)
const listRef = ref(null)
let stream = null
let reconnectTimer = null

async function fetchMessages() {
  const res = await api.get('/chat/messages')
  messages.value = res.data
  await nextTick()
  listRef.value?.scrollTo({ top: listRef.value.scrollHeight })
}

function appendMessage(message) {
  if (messages.value.some(item => item.id === message.id)) return
  messages.value.push(message)
  if (messages.value.length > 80) messages.value = messages.value.slice(-80)
  nextTick(() => {
    listRef.value?.scrollTo({ top: listRef.value.scrollHeight, behavior: 'smooth' })
  })
}

async function sendMessage() {
  const text = body.value.trim()
  if (!text) return
  sending.value = true
  try {
    await api.post('/chat/messages', { body: text })
    body.value = ''
  } finally {
    sending.value = false
  }
}

function lastMessageId() {
  return messages.value.at(-1)?.id || 0
}

function closeStream() {
  if (stream) {
    stream.close()
    stream = null
  }
  if (reconnectTimer) {
    window.clearTimeout(reconnectTimer)
    reconnectTimer = null
  }
}

function openStream() {
  closeStream()
  const baseUrl = api.defaults.baseURL || ''
  const url = `${baseUrl}/chat/stream?token=${encodeURIComponent(auth.token)}&last_id=${lastMessageId()}`
  stream = new EventSource(url)

  stream.addEventListener('message', event => {
    appendMessage(JSON.parse(event.data))
  })

  stream.onerror = () => {
    stream?.close()
    stream = null
    reconnectTimer = window.setTimeout(openStream, 1500)
  }
}

function initials(user) {
  return user?.first_name?.[0] || user?.full_name?.[0] || user?.last_name?.[0] || 'U'
}

function formatTime(value) {
  return new Date(value).toLocaleString('ru-RU', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' })
}

onMounted(async () => {
  loading.value = true
  await fetchMessages()
  loading.value = false
  openStream()
})

onUnmounted(() => {
  closeStream()
})
</script>

<template>
  <div class="h-[calc(100vh-8rem)] flex flex-col">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="text-xl font-bold text-gray-800">Общий чат</h2>
        <p class="text-sm text-gray-500 mt-1">Единое пространство для школьных объявлений и обсуждений.</p>
      </div>
      <button @click="fetchMessages" class="border px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Обновить</button>
    </div>

    <div ref="listRef" class="flex-1 bg-white border rounded-xl overflow-y-auto p-4 space-y-3">
      <div v-if="loading" class="text-center text-gray-400 py-10">Загрузка...</div>
      <div v-else-if="messages.length === 0" class="text-center text-gray-400 py-10">Пока нет сообщений</div>
      <div
        v-for="message in messages"
        :key="message.id"
        :class="['flex gap-3', message.user_id === auth.user?.id ? 'justify-end' : 'justify-start']"
      >
        <div v-if="message.user_id !== auth.user?.id" class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-semibold shrink-0 overflow-hidden">
          <img v-if="message.user?.avatar_url" :src="message.user.avatar_url" class="w-full h-full object-cover" alt="" />
          <span v-else>{{ initials(message.user) }}</span>
        </div>
        <div :class="['max-w-[70%] rounded-xl px-4 py-3 text-sm', message.user_id === auth.user?.id ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-800']">
          <div :class="['text-xs mb-1', message.user_id === auth.user?.id ? 'text-blue-100' : 'text-gray-500']">
            {{ message.user?.full_name }} · {{ formatTime(message.created_at) }}
          </div>
          <div class="whitespace-pre-wrap break-words">{{ message.body }}</div>
        </div>
      </div>
    </div>

    <form @submit.prevent="sendMessage" class="mt-4 flex gap-3">
      <textarea
        v-model="body"
        rows="2"
        placeholder="Напишите сообщение..."
        class="flex-1 border rounded-xl px-4 py-3 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"
      ></textarea>
      <button
        type="submit"
        :disabled="sending || !body.trim()"
        class="bg-blue-600 text-white px-5 rounded-xl text-sm font-semibold disabled:opacity-50"
      >
        Отправить
      </button>
    </form>
  </div>
</template>
