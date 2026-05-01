<template>
  <v-container 
    fluid 
    class="rounded-ts-xl" 
    min-height="calc(100dvh - 60px)"
  >
    <div style="max-width: 1200px;" class="mx-auto">
      <div class="d-flex align-end justify-space-between mb-6">
        <div>
          <p class="text-headline-small font-weight-bold">Benefit Plans</p>
          <p class="text-body-medium text-medium-emphasis mt-1">Manage insurance benefit plans and annual limits</p>
        </div>
      <v-btn
        color="primary"
        rounded="lg"
        class="text-none font-weight-bold"
        prepend-icon="mdi-plus"
        elevation="0"
        @click="openDialog('add')"
      >
        Add Plan
      </v-btn>
    </div>

    <v-row density="compact">
      <v-col cols="12">
        <v-data-table
          :headers="headers"
          :items="benefitPlanStore.plans"
          :loading="benefitPlanStore.loading"
          :search="search"
          :custom-filter="customFilter"
          class="bg-transparent"
        >
          <template #top>
            <v-text-field
              v-model="search"
              prepend-inner-icon="mdi-magnify"
              label="Search"
              variant="underlined"
              color="primary"
              rounded="lg"
              hide-details
              class="mb-4"
              style="max-width: 320px;"
            ></v-text-field>
          </template>
          <template #item.index="{ index }">
            {{ index + 1 }}
          </template>
          <template #item.annual_limit="{ item }">
            <span class="font-weight-bold text-primary">{{ formatCurrency(item.annual_limit) }}</span>
          </template>
          <template #item.members_count="{ item }">
            <v-chip color="secondary" variant="tonal" size="small" label>
              {{ item.members_count || 0 }} members
            </v-chip>
          </template>
          <template #item.created_at="{ item }">
            {{ formatDate(item.created_at) }}
          </template>
          <template #item.actions="{ item }">
            <v-btn icon="mdi-pencil" variant="text" size="small" color="warning" @click="openDialog('edit', item)"></v-btn>
            <v-btn icon="mdi-delete" variant="text" size="small" color="error" @click="openDeleteDialog(item)"></v-btn>
          </template>
        </v-data-table>
      </v-col>
    </v-row>

    <v-dialog v-model="showDialog" max-width="500" persistent>
      <v-card rounded="xl" class="pa-2">
        <v-card-title class="text-title-large font-weight-bold pa-4">
          {{ dialogMode === 'add' ? 'Add New Plan' : 'Edit Plan' }}
        </v-card-title>
        <v-card-text class="px-4">
          <v-form ref="formRef" autocomplete="off" @submit.prevent="save">
            <v-row density="compact">
              <v-col cols="12"><p class="text-label-large text-medium-emphasis">Plan Name<span class="text-error">*</span></p></v-col>
              <v-col cols="12">
                <v-text-field
                  v-model="form.plan_name"
                  variant="outlined"
                  color="primary"
                  rounded="lg"
                  density="comfortable"
                  hide-details="auto"
                  placeholder="e.g. Premium Plan"
                  :rules="[(v) => !!v || 'Required.']"
                ></v-text-field>
              </v-col>

              <v-col cols="12"><p class="text-label-large text-medium-emphasis">Annual Limit (IDR)<span class="text-error">*</span></p></v-col>
              <v-col cols="12">
                <v-text-field
                  v-model.number="form.annual_limit"
                  variant="outlined"
                  color="primary"
                  rounded="lg"
                  density="comfortable"
                  hide-details="auto"
                  type="number"
                  prefix="Rp"
                  placeholder="10000000"
                  :rules="[(v) => !!v || 'Required.', (v) => v > 0 || 'Must be greater than 0']"
                ></v-text-field>                
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-card-actions class="pa-4 pt-0">
          <v-spacer></v-spacer>
          <v-btn variant="text" rounded="lg" class="text-none" @click="showDialog = false">Cancel</v-btn>
          <v-btn color="primary" variant="flat" rounded="lg" class="text-none font-weight-bold" :loading="saving" @click="save">
            {{ dialogMode === 'add' ? 'Create' : 'Update' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="showDeleteDialog" max-width="400">
      <v-card rounded="xl" class="pa-2">
        <v-card-title class="text-title-large font-weight-bold pa-4">Delete Plan</v-card-title>
        <v-card-text class="px-4">
          Are you sure you want to delete <strong>{{ deleteTarget?.plan_name }}</strong>? This action cannot be undone.
        </v-card-text>
        <v-card-actions class="pa-4 pt-0">
          <v-spacer></v-spacer>
          <v-btn variant="text" rounded="lg" class="text-none" @click="showDeleteDialog = false">Cancel</v-btn>
          <v-btn color="error" variant="flat" rounded="lg" class="text-none font-weight-bold" :loading="deleting" @click="confirmDelete">
            Delete
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    </div>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useBenefitPlanStore } from '@/stores/benefitPlan'
import { formatCurrency, formatDate } from '@/utils/format'
import { useToast } from 'vue-toastification'

const toast = useToast()

const benefitPlanStore = useBenefitPlanStore()

const search = ref('')
const showDialog = ref(false)
const showDeleteDialog = ref(false)
const dialogMode = ref('add')
const saving = ref(false)
const deleting = ref(false)
const deleteTarget = ref(null)

const form = ref({ plan_name: '', annual_limit: null })
const formErrors = ref({})
const editId = ref(null)
const formRef = ref(null)

const headers = [
  { title: 'No', key: 'index', sortable: false, width: '60px' },
  { title: 'Plan Name', key: 'plan_name' },
  { title: 'Annual Limit', key: 'annual_limit' },
  { title: 'Members', key: 'members_count' },
  { title: 'Created At', key: 'created_at' },
  { title: 'Actions', key: 'actions', sortable: false, width: '120px' },
]

function openDialog(mode, item = null) {
  dialogMode.value = mode
  formErrors.value = {}
  if (formRef.value) formRef.value.resetValidation()
  if (mode === 'edit' && item) {
    editId.value = item.id
    form.value = {
      plan_name: item.plan_name,
      annual_limit: parseFloat(item.annual_limit),
    }
  } else {
    editId.value = null
    form.value = { plan_name: '', annual_limit: null }
  }
  showDialog.value = true
}

function openDeleteDialog(item) {
  deleteTarget.value = item
  showDeleteDialog.value = true
}

async function save() {
  const { valid } = await formRef.value.validate()
  if (!valid) return
  saving.value = true
  formErrors.value = {}
  try {
    if (dialogMode.value === 'add') {
      await benefitPlanStore.addBenefitPlan(form.value)
      toast.success('Benefit plan created successfully')
    } else {
      await benefitPlanStore.editBenefitPlan(editId.value, form.value)
      toast.success('Benefit plan updated successfully')
    }
    showDialog.value = false
  } catch (e) {
    if (e.response?.data?.errors) {
      formErrors.value = e.response.data.errors
    } else {
      toast.error(e.response?.data?.message || 'An error occurred')
    }
  } finally {
    saving.value = false
  }
}

async function confirmDelete() {
  deleting.value = true
  try {
    await benefitPlanStore.deleteBenefitPlan(deleteTarget.value.id)
    toast.success('Benefit plan deleted successfully')
    showDeleteDialog.value = false
  } catch (e) {
    toast.error(e.response?.data?.message || 'An error occurred')
  } finally {
    deleting.value = false
  }
}

function customFilter(value, query, item) {
  if (!query) return true
  const q = query.toLowerCase()
  const raw = item?.raw
  if (!raw) return false
  const searchable = [
    raw.plan_name,
    formatCurrency(raw.annual_limit),
    formatDate(raw.created_at),
  ].filter(Boolean).join(' ').toLowerCase()
  return searchable.includes(q)
}

onMounted(() => {
  benefitPlanStore.getAllBenefitPlans()
})
</script>
