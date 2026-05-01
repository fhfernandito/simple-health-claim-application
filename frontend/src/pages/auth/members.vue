<template>
  <v-container 
    fluid 
    class="rounded-ts-xl" 
    min-height="calc(100dvh - 60px)"
  >
    <div style="max-width: 1200px;" class="mx-auto">
      <div class="d-flex align-end justify-space-between mb-6">
        <div>
          <p class="text-headline-small font-weight-bold">Members</p>
          <p class="text-body-medium text-medium-emphasis mt-1">Manage insurance members and their benefit plans</p>
        </div>
      <v-btn
        color="primary"
        rounded="lg"
        class="text-none font-weight-bold"
        prepend-icon="mdi-plus"
        elevation="0"
        @click="openDialog('add')"
      >
        Add Member
      </v-btn>
    </div>

    <v-row density="compact">
      <v-col cols="12">
        <v-data-table
          :headers="headers"
          :items="memberStore.members"
          :loading="memberStore.loading"
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
        <template #item.benefit_plan="{ item }">
          <v-chip color="primary" variant="tonal" size="small" label>
            {{ item.benefit_plan?.plan_name || '-' }}
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
          {{ dialogMode === 'add' ? 'Add New Member' : 'Edit Member' }}
        </v-card-title>
        <v-card-text class="px-4">
          <v-form ref="formRef" autocomplete="off" @submit.prevent="save">
            <v-row density="compact">
              <v-col cols="12"><p class="text-label-large text-medium-emphasis">Full Name<span class="text-error">*</span></p></v-col>
              <v-col cols="12">
                <v-text-field
                  v-model="form.name"
                  placeholder="e.g F. Hanindya Fernandito"
                  variant="outlined"
                  color="primary"
                  rounded="lg"
                  density="comfortable"
                  hide-details="auto"
                  :rules="[(v) => !!v || 'Required.']"
                ></v-text-field>
              </v-col>

              <template v-if="dialogMode === 'edit'">
                <v-col cols="12"><p class="text-label-large text-medium-emphasis">Policy Number</p></v-col>
                <v-col cols="12">
                  <v-text-field
                    v-model="form.policy_number"
                    variant="outlined"
                    color="primary"
                    rounded="lg"
                    density="comfortable"
                    hide-details="auto"
                    readonly
                    bg-color="grey-lighten-4"
                  ></v-text-field>
                </v-col>
              </template>

              <v-col cols="12"><p class="text-label-large text-medium-emphasis">Benefit Plan<span class="text-error">*</span></p></v-col>
              <v-col cols="12">
                <v-select
                  v-model="form.benefit_plan_id"
                  variant="outlined"
                  color="primary"
                  rounded="lg"
                  density="comfortable"
                  hide-details="auto"
                  :items="benefitPlanStore.plans"
                  item-title="plan_name"
                  item-value="id"
                  :rules="[(v) => !!v || 'Required.']"
                >
                  <template #item="{ props: itemProps, item }">
                    <v-list-item v-bind="itemProps">
                      <template #subtitle>
                        Annual Limit: {{ formatCurrency(item.annual_limit) }}
                      </template>
                    </v-list-item>
                  </template>
                </v-select>
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
        <v-card-title class="text-title-large font-weight-bold pa-4">Delete Member</v-card-title>
        <v-card-text class="px-4">
          Are you sure you want to delete <strong>{{ deleteTarget?.name }}</strong>? This action cannot be undone.
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
import { useMemberStore } from '@/stores/member'
import { useBenefitPlanStore } from '@/stores/benefitPlan'
import { formatCurrency, formatDate } from '@/utils/format'
import { useToast } from 'vue-toastification'

const toast = useToast()

const memberStore = useMemberStore()
const benefitPlanStore = useBenefitPlanStore()

const search = ref('')
const showDialog = ref(false)
const showDeleteDialog = ref(false)
const dialogMode = ref('add')
const saving = ref(false)
const deleting = ref(false)
const deleteTarget = ref(null)

const form = ref({ 
  name: '',
  policy_number: '',
  benefit_plan_id: null
})
const formErrors = ref({})
const editId = ref(null)
const formRef = ref(null)

const headers = [
  { title: 'No', key: 'index', sortable: false, width: '60px' },
  { title: 'Name', key: 'name' },
  { title: 'Policy Number', key: 'policy_number' },
  { title: 'Benefit Plan', key: 'benefit_plan' },
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
      name: item.name,
      policy_number: item.policy_number,
      benefit_plan_id: item.benefit_plan_id,
    }
  } else {
    editId.value = null
    form.value = { 
      name: '', 
      policy_number: '', 
      benefit_plan_id: null 
    }
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
      const { policy_number, ...addPayload } = form.value
      await memberStore.addMember(addPayload)
      toast.success('Member created successfully')
    } else {
      await memberStore.editMember(editId.value, form.value)
      toast.success('Member updated successfully')
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
    await memberStore.deleteMember(deleteTarget.value.id)
    toast.success('Member deleted successfully')
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
    raw.name,
    raw.policy_number,
    raw.benefit_plan?.plan_name,
    formatDate(raw.created_at),
  ].filter(Boolean).join(' ').toLowerCase()
  return searchable.includes(q)
}

onMounted(() => {
  memberStore.getAllMembers()
  benefitPlanStore.getAllBenefitPlans()
})
</script>
