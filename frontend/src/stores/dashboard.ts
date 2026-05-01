import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/plugins/axios'

export const useDashboardStore = defineStore('dashboard', () => {
  const stats = ref<any>(null)
  const loading = ref(false)

  async function getDashboardStats() {
    loading.value = true
    try {
      const response = await api.get('/get-dashboard-stats')
      stats.value = response.data.data
      return response.data
    } finally {
      loading.value = false
    }
  }

  return { stats, loading, getDashboardStats }
})
