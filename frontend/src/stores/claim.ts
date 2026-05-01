import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/plugins/axios'

export const useClaimStore = defineStore('claim', () => {
  const claims = ref<any[]>([])
  const loading = ref(false)

  async function getAllClaims() {
    loading.value = true
    try {
      const response = await api.get('/get-all-claims')
      claims.value = response.data.data
      return response.data
    } finally {
      loading.value = false
    }
  }

  async function submitClaim(data: { member_id: number; claim_date: string; claim_amount: number }) {
    const response = await api.post('/submit-claim', data)
    await getAllClaims()
    return response.data
  }

  async function deleteClaim(id: number) {
    const response = await api.delete(`/delete-claim/${id}`)
    await getAllClaims()
    return response.data
  }

  async function getMemberRemainingLimit(memberId: number, year?: number) {
    const params: any = { member_id: memberId }
    if (year) params.year = year
    const response = await api.get('/get-member-remaining-limit', { params })
    return response.data
  }

  return { 
    claims, 
    loading, 
    getAllClaims, 
    submitClaim, 
    deleteClaim, 
    getMemberRemainingLimit 
  }
})
