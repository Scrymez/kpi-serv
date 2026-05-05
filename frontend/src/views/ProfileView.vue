<script setup>
import { reactive, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
})
const avatarFile = ref(null)
const savingPassword = ref(false)
const savingAvatar = ref(false)
const message = ref('')
const error = ref('')

async function submitPassword() {
  savingPassword.value = true
  message.value = ''
  error.value = ''
  try {
    await auth.changePassword({
      current_password: passwordForm.current_password,
      password: passwordForm.password,
      password_confirmation: passwordForm.password_confirmation,
      force: auth.mustChangePassword,
    })
    passwordForm.current_password = ''
    passwordForm.password = ''
    passwordForm.password_confirmation = ''
    message.value = 'Пароль обновлен.'
  } catch (e) {
    error.value = e.response?.data?.message || 'Не удалось изменить пароль.'
  } finally {
    savingPassword.value = false
  }
}

async function submitAvatar() {
  if (!avatarFile.value) return
  savingAvatar.value = true
  message.value = ''
  error.value = ''
  try {
    await auth.updateAvatar(avatarFile.value)
    avatarFile.value = null
    message.value = 'Аватар обновлен.'
  } catch (e) {
    error.value = e.response?.data?.message || 'Не удалось обновить аватар.'
  } finally {
    savingAvatar.value = false
  }
}
</script>

<template>
  <div class="max-w-4xl">
    <div class="mb-6">
      <h2 class="text-xl font-bold text-gray-800">Профиль</h2>
      <p class="text-sm text-gray-500 mt-1">Здесь можно изменить только пароль и аватар.</p>
    </div>

    <div v-if="auth.mustChangePassword" class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 text-sm text-amber-800">
      Перед продолжением нужно сменить временный пароль.
    </div>

    <div v-if="message" class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 text-sm text-green-700">{{ message }}</div>
    <div v-if="error" class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 text-sm text-red-700">{{ error }}</div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <section class="bg-white border rounded-xl p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Данные пользователя</h3>
        <div class="flex items-center gap-4 mb-5">
          <img
            v-if="auth.user?.avatar_url"
            :src="auth.user.avatar_url"
            class="w-16 h-16 rounded-full object-cover border"
            alt=""
          />
          <div v-else class="w-16 h-16 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-2xl font-semibold">
            {{ auth.user?.first_name?.[0] || auth.user?.full_name?.[0] || 'U' }}
          </div>
          <div>
            <div class="font-semibold text-gray-800">{{ auth.user?.full_name }}</div>
            <div class="text-sm text-gray-500">{{ auth.user?.login }}</div>
          </div>
        </div>

        <div class="space-y-3 text-sm">
          <div>
            <label class="block text-gray-500 mb-1">ФИО</label>
            <input :value="auth.user?.full_name" disabled class="w-full border rounded-lg px-3 py-2 bg-gray-50 text-gray-500" />
          </div>
          <div>
            <label class="block text-gray-500 mb-1">Класс</label>
            <input :value="auth.user?.class?.name || '—'" disabled class="w-full border rounded-lg px-3 py-2 bg-gray-50 text-gray-500" />
          </div>
          <p class="text-xs text-gray-400">ФИО и класс изменяет только администратор в разделе пользователей.</p>
        </div>
      </section>

      <section class="bg-white border rounded-xl p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Аватар</h3>
        <form @submit.prevent="submitAvatar" class="space-y-4">
          <input
            type="file"
            accept=".jpg,.jpeg,.png,.webp"
            @change="e => avatarFile = e.target.files[0]"
            class="block w-full text-sm"
          />
          <button
            type="submit"
            :disabled="!avatarFile || savingAvatar"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm disabled:opacity-50"
          >
            {{ savingAvatar ? 'Сохраняем...' : 'Обновить аватар' }}
          </button>
        </form>
      </section>

      <section class="bg-white border rounded-xl p-5 lg:col-span-2">
        <h3 class="font-semibold text-gray-800 mb-4">Смена пароля</h3>
        <form @submit.prevent="submitPassword" class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div v-if="!auth.mustChangePassword">
            <label class="block text-sm text-gray-600 mb-1">Текущий пароль</label>
            <input v-model="passwordForm.current_password" type="password" autocomplete="current-password" class="w-full border rounded-lg px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm text-gray-600 mb-1">Новый пароль</label>
            <input v-model="passwordForm.password" type="password" autocomplete="new-password" class="w-full border rounded-lg px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm text-gray-600 mb-1">Повторите пароль</label>
            <input v-model="passwordForm.password_confirmation" type="password" autocomplete="new-password" class="w-full border rounded-lg px-3 py-2 text-sm" />
          </div>
          <div class="md:col-span-3">
            <button
              type="submit"
              :disabled="savingPassword"
              class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm disabled:opacity-50"
            >
              {{ savingPassword ? 'Меняем...' : 'Сменить пароль' }}
            </button>
          </div>
        </form>
      </section>
    </div>
  </div>
</template>

