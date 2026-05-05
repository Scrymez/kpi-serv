import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(JSON.parse(localStorage.getItem('user') || 'null'))
  const token = ref(localStorage.getItem('token') || null)

  const isLoggedIn = computed(() => !!token.value)
  const role = computed(() => user.value?.role)

  const isAdmin = computed(() => role.value === 'admin')
  const isDirector = computed(() => role.value === 'director')
  const isDeputy = computed(() => ['deputy_events', 'deputy_edu', 'deputy_science'].includes(role.value))
  const isTeacher = computed(() => role.value === 'teacher')
  const isStudent = computed(() => role.value === 'student')
  const isParent = computed(() => role.value === 'parent')
  const canVerify = computed(() => ['admin', 'director', 'deputy_events', 'deputy_edu', 'deputy_science'].includes(role.value))
  const mustChangePassword = computed(() => !!user.value?.must_change_password)

  async function login(login, password) {
    const res = await api.post('/login', { login, password })
    token.value = res.data.token
    user.value = res.data.user
    localStorage.setItem('token', token.value)
    localStorage.setItem('user', JSON.stringify(user.value))
  }

  async function fetchMe() {
    const res = await api.get('/me')
    user.value = res.data
    localStorage.setItem('user', JSON.stringify(user.value))
  }

  async function logout() {
    try { await api.post('/logout') } catch {}
    token.value = null
    user.value = null
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  }

  async function changePassword(payload) {
    const res = await api.put('/profile/password', payload)
    token.value = res.data.token
    user.value = res.data.user
    localStorage.setItem('token', token.value)
    localStorage.setItem('user', JSON.stringify(user.value))
  }

  async function updateAvatar(file) {
    const form = new FormData()
    form.append('avatar', file)
    const res = await api.post('/profile/avatar', form, { headers: { 'Content-Type': 'multipart/form-data' } })
    user.value = res.data.user
    localStorage.setItem('user', JSON.stringify(user.value))
  }

  return {
    user,
    token,
    isLoggedIn,
    role,
    isAdmin,
    isDirector,
    isDeputy,
    isTeacher,
    isStudent,
    isParent,
    canVerify,
    mustChangePassword,
    login,
    logout,
    fetchMe,
    changePassword,
    updateAvatar,
  }
})
