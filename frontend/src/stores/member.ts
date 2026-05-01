import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/plugins/axios'

export const useMemberStore = defineStore('member', () => {
  const members = ref<any[]>([])
  const loading = ref(false)

  async function getAllMembers() {
    loading.value = true
    try {
      const response = await api.get('/get-all-members')
      members.value = response.data.data
      return response.data
    } finally {
      loading.value = false
    }
  }

  async function addMember(data: { name: string; benefit_plan_id: number }) {
    const response = await api.post('/add-member', data)
    await getAllMembers()
    return response.data
  }

  async function editMember(id: number, data: { name: string; benefit_plan_id: number }) {
    const response = await api.put(`/edit-member/${id}`, data)
    await getAllMembers()
    return response.data
  }

  async function deleteMember(id: number) {
    const response = await api.delete(`/delete-member/${id}`)
    await getAllMembers()
    return response.data
  }

  return { 
    members,
     loading, 
     getAllMembers, 
     addMember, 
     editMember, 
     deleteMember 
    }
})
