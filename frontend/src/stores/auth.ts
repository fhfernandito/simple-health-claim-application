import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/plugins/axios'
import Cookies from 'js-cookie'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<any>(null)
  const token = ref<string | null>(Cookies.get('auth_token') || null)

  async function signIn(email: string, password: string) {
    const response = await api.post('/sign-in', { email, password })
    user.value = response.data.data
    token.value = response.data.token
    Cookies.set('auth_token', response.data.token, { expires: 7 })
    return response.data
  }

  async function signOut() {
    try {
      await api.post('/sign-out')
    } catch (e) {
    }
    user.value = null
    token.value = null
    Cookies.remove('auth_token')
  }

  async function getUser() {
    const response = await api.get('/get-user')
    user.value = response.data.data
    return response.data
  }

  function isSignedIn(): boolean {
    return !!Cookies.get('auth_token')
  }

  return { 
    user, 
    token, 
    signIn, 
    signOut, 
    getUser, 
    isSignedIn 
  }
})
