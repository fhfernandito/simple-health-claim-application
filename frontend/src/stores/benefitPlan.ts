import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/plugins/axios'

export const useBenefitPlanStore = defineStore('benefitPlan', () => {
  const plans = ref<any[]>([])
  const loading = ref(false)

  async function getAllBenefitPlans() {
    loading.value = true
    try {
      const response = await api.get('/get-all-benefit-plans')
      plans.value = response.data.data
      return response.data
    } finally {
      loading.value = false
    }
  }

  async function addBenefitPlan(data: { plan_name: string; annual_limit: number }) {
    const response = await api.post('/add-benefit-plan', data)
    await getAllBenefitPlans()
    return response.data
  }

  async function editBenefitPlan(id: number, data: { plan_name: string; annual_limit: number }) {
    const response = await api.put(`/edit-benefit-plan/${id}`, data)
    await getAllBenefitPlans()
    return response.data
  }

  async function deleteBenefitPlan(id: number) {
    const response = await api.delete(`/delete-benefit-plan/${id}`)
    await getAllBenefitPlans()
    return response.data
  }

  return { 
    plans, 
    loading, 
    getAllBenefitPlans, 
    addBenefitPlan, 
    editBenefitPlan, 
    deleteBenefitPlan 
  }
})
