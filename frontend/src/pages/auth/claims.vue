<template>
  <v-container 
    fluid 
    class="rounded-ts-xl" 
    min-height="calc(100dvh - 60px)"
  >
    <div style="max-width: 1200px;" class="mx-auto">
      <div class="d-flex align-end justify-space-between mb-6">
        <div>
          <p class="text-headline-small font-weight-bold">Claims</p>
          <p class="text-body-medium text-medium-emphasis mt-1">Submit and track insurance claims with automatic limit calculation</p>
        </div>
      <v-btn
        color="primary"
        rounded="lg"
        class="text-none font-weight-bold"
        prepend-icon="mdi-plus"
        elevation="0"
        @click="openSubmitDialog"
      >
        Submit Claim
      </v-btn>
    </div>

    <v-row density="compact">
      <v-col cols="12">
        <v-data-table
          :headers="headers"
          :items="claimStore.claims"
          :loading="claimStore.loading"
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
        <template #item.member="{ item }">
          <div>
            <p class="font-weight-medium">{{ item.member?.name }}</p>
            <p class="text-label-small text-medium-emphasis">{{ item.member?.policy_number }}</p>
          </div>
        </template>
        <template #item.plan="{ item }">
          <v-chip color="primary" variant="tonal" size="small" label>
            {{ item.member?.benefit_plan?.plan_name || '-' }}
          </v-chip>
        </template>
        <template #item.claim_date="{ item }">
          {{ formatDate(item.claim_date) }}
        </template>
        <template #item.claim_amount="{ item }">
          <span class="font-weight-bold">{{ formatCurrency(item.claim_amount) }}</span>
        </template>
        <template #item.approved_amount="{ item }">
          <v-chip color="success" variant="tonal" size="small" label>
            {{ formatCurrency(item.approved_amount) }}
          </v-chip>
        </template>
        <template #item.excess_amount="{ item }">
          <v-chip :color="parseFloat(item.excess_amount) > 0 ? 'error' : 'default'" variant="tonal" size="small" label>
            {{ formatCurrency(item.excess_amount) }}
          </v-chip>
        </template>
        <template #item.status="{ item }">
          <v-chip 
            :color="parseFloat(item.excess_amount) > 0 ? (parseFloat(item.approved_amount) > 0 ? 'warning' : 'error') : 'success'"
            variant="tonal" 
            size="small"
            label
          >
            {{ parseFloat(item.excess_amount) > 0 ? (parseFloat(item.approved_amount) > 0 ? 'Partial' : 'Rejected') : 'Approved' }}
          </v-chip>
        </template>
        <template #item.actions="{ item }">
          <v-btn icon="mdi-delete" variant="text" size="small" color="error" @click="openDeleteDialog(item)"></v-btn>
        </template>
      </v-data-table>
      </v-col>
    </v-row>

    <v-dialog v-model="showSubmitDialog" max-width="560" persistent>
      <v-card rounded="xl" class="pa-2">
        <v-card-title class="text-title-large font-weight-bold pa-4">
          Submit New Claim
        </v-card-title>
        <v-card-text class="px-4">
          <v-form ref="formRef" autocomplete="off" @submit.prevent="submitClaim">
            <v-row density="compact">
              <v-col cols="12"><p class="text-label-large text-medium-emphasis">Select Member<span class="text-error">*</span></p></v-col>
              <v-col cols="12">
                <v-autocomplete
                  v-model="form.member_id"
                  variant="outlined"
                  color="primary"
                  rounded="lg"
                  density="comfortable"
                  hide-details="auto"
                  :items="memberStore.members"
                  item-title="name"
                  item-value="id"
                  :rules="[(v) => !!v || 'Required.']"
                  @update:model-value="fetchRemainingLimit"
                >
                  <template #item="{ props: itemProps, item }">
                    <v-list-item v-bind="itemProps">
                      <template #subtitle>
                        {{ item.policy_number }} — {{ item.benefit_plan?.plan_name }}
                      </template>
                    </v-list-item>
                  </template>
                </v-autocomplete>
              </v-col>

              <v-col cols="12"><p class="text-label-large text-medium-emphasis">Claim Date<span class="text-error">*</span></p></v-col>
              <v-col cols="12">
                <v-date-input
                  v-model="form.claim_date"
                  input-format="dd/MM/yyyy"
                  variant="outlined"
                  color="primary"
                  rounded="lg"
                  density="comfortable"
                  hide-details="auto"
                  prepend-icon=""
                  append-inner-icon="mdi-calendar"
                  :rules="[(v) => !!v || 'Required.']"
                ></v-date-input>
              </v-col>

              <v-col cols="12"><p class="text-label-large text-medium-emphasis">Claim Amount (IDR)<span class="text-error">*</span></p></v-col>
              <v-col cols="12">
                <v-text-field
                  v-model.number="form.claim_amount"
                  variant="outlined"
                  color="primary"
                  rounded="lg"
                  density="comfortable"
                  hide-details="auto"
                  type="number"
                  prefix="Rp"
                  :rules="[(v) => !!v || 'Required.', (v) => v > 0 || 'Must be greater than 0.']"
                  placeholder="1000000"
                ></v-text-field>                
              </v-col>
            </v-row>

            <v-alert
              v-if="remainingLimitData"
              type="info"
              variant="tonal"
              density="compact"
              rounded="lg"
              class="mt-2"
            >
              <div class="d-flex flex-column ga-1">
                <div class="d-flex justify-space-between">
                  <span class="text-body-medium">Annual Limit:</span>
                  <span class="text-body-medium font-weight-bold">{{ formatCurrency(remainingLimitData.annual_limit) }}</span>
                </div>
                <div class="d-flex justify-space-between">
                  <span class="text-body-medium">Total Approved ({{ remainingLimitData.year }}):</span>
                  <span class="text-body-medium font-weight-bold">{{ formatCurrency(remainingLimitData.total_approved) }}</span>
                </div>
                <v-divider class="my-1"></v-divider>
                <div class="d-flex justify-space-between">
                  <span class="text-body-medium font-weight-bold">Remaining Limit:</span>
                  <span class="text-body-medium font-weight-bold" :class="remainingLimitData.remaining_limit > 0 ? 'text-success' : 'text-error'">
                    {{ formatCurrency(remainingLimitData.remaining_limit) }}
                  </span>
                </div>
                <template v-if="form.claim_amount && remainingLimitData">
                  <v-divider class="my-1"></v-divider>
                  <div class="d-flex justify-space-between">
                    <span class="text-body-medium">Estimated Approved:</span>
                    <span class="text-body-medium font-weight-bold text-success">
                      {{ formatCurrency(Math.min(form.claim_amount, remainingLimitData.remaining_limit)) }}
                    </span>
                  </div>
                  <div class="d-flex justify-space-between">
                    <span class="text-body-medium">Estimated Excess:</span>
                    <span class="text-body-medium font-weight-bold text-error">
                      {{ formatCurrency(Math.max(form.claim_amount - remainingLimitData.remaining_limit, 0)) }}
                    </span>
                  </div>
                </template>
              </div>
            </v-alert>
          </v-form>
        </v-card-text>
        <v-card-actions class="pa-4 pt-2">
          <v-spacer></v-spacer>
          <v-btn variant="text" rounded="lg" class="text-none" @click="showSubmitDialog = false">Cancel</v-btn>
          <v-btn color="primary" variant="flat" rounded="lg" class="text-none font-weight-bold" :loading="saving" @click="submitClaim">
            Submit Claim
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="showDeleteDialog" max-width="400">
      <v-card rounded="xl" class="pa-2">
        <v-card-title class="text-title-large font-weight-bold pa-4">Delete Claim</v-card-title>
        <v-card-text class="px-4">
          Are you sure you want to delete this claim for <strong>{{ formatCurrency(deleteTarget?.claim_amount) }}</strong>? This action cannot be undone.
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

    <v-dialog v-model="showResultDialog" max-width="480">
      <v-card rounded="xl" class="pa-2">
        <v-card-title class="text-title-large font-weight-bold pa-4 text-center">
          <v-icon :color="claimResult?.calculation?.excess_amount > 0 ? 'warning' : 'success'" size="48" class="mb-2">
            {{ claimResult?.calculation?.excess_amount > 0 ? 'mdi-alert-circle' : 'mdi-check-circle' }}
          </v-icon>
          <div>Claim Processed</div>
        </v-card-title>
        <v-card-text v-if="claimResult" class="px-4">
          <v-list density="compact" class="bg-transparent">
            <v-list-item>
              <template #prepend><v-icon size="small" color="primary">mdi-cash</v-icon></template>
              <v-list-item-title>Claim Amount</v-list-item-title>
              <template #append>
                <span class="font-weight-bold">{{ formatCurrency(claimResult.calculation.approved_amount + claimResult.calculation.excess_amount) }}</span>
              </template>
            </v-list-item>
            <v-list-item>
              <template #prepend><v-icon size="small" color="success">mdi-check</v-icon></template>
              <v-list-item-title>Approved</v-list-item-title>
              <template #append>
                <span class="font-weight-bold text-success">{{ formatCurrency(claimResult.calculation.approved_amount) }}</span>
              </template>
            </v-list-item>
            <v-list-item>
              <template #prepend><v-icon size="small" color="error">mdi-close</v-icon></template>
              <v-list-item-title>Excess</v-list-item-title>
              <template #append>
                <span class="font-weight-bold text-error">{{ formatCurrency(claimResult.calculation.excess_amount) }}</span>
              </template>
            </v-list-item>
            <v-divider class="my-2"></v-divider>
            <v-list-item>
              <template #prepend><v-icon size="small" color="primary">mdi-information</v-icon></template>
              <v-list-item-title>Remaining Limit</v-list-item-title>
              <template #append>
                <span class="font-weight-bold">{{ formatCurrency(claimResult.calculation.remaining_limit - claimResult.calculation.approved_amount) }}</span>
              </template>
            </v-list-item>
          </v-list>
        </v-card-text>
        <v-card-actions class="pa-4 pt-0 justify-center">
          <v-btn color="primary" variant="flat" rounded="lg" class="text-none font-weight-bold" @click="showResultDialog = false">
            Done
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    </div>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useClaimStore } from '@/stores/claim'
import { useMemberStore } from '@/stores/member'
import { formatCurrency, formatDate } from '@/utils/format'
import { useToast } from 'vue-toastification'
import confetti from 'canvas-confetti'

const toast = useToast()

const claimStore = useClaimStore()
const memberStore = useMemberStore()

const search = ref('')
const showSubmitDialog = ref(false)
const showDeleteDialog = ref(false)
const showResultDialog = ref(false)
const saving = ref(false)
const deleting = ref(false)
const deleteTarget = ref(null)
const claimResult = ref(null)
const remainingLimitData = ref(null)

const formRef = ref(null)
const form = ref(
  { 
    member_id: null, 
    claim_date: new Date(), 
    claim_amount: null 
  }
)

const headers = [
  { title: 'No', key: 'index', sortable: false, width: '60px' },
  { title: 'Member', key: 'member', sortable: false },
  { title: 'Plan', key: 'plan', sortable: false },
  { title: 'Claim Date', key: 'claim_date' },
  { title: 'Claim Amount', key: 'claim_amount' },
  { title: 'Approved', key: 'approved_amount' },
  { title: 'Excess', key: 'excess_amount' },
  { title: 'Status', key: 'status', sortable: false },
  { title: '', key: 'actions', sortable: false, width: '60px' },
]

function openSubmitDialog() {
  form.value = { member_id: null, claim_date: new Date(), claim_amount: null }
  formErrors.value = {}
  if (formRef.value) formRef.value.resetValidation()
  remainingLimitData.value = null
  showSubmitDialog.value = true
}

function openDeleteDialog(item) {
  deleteTarget.value = item
  showDeleteDialog.value = true
}

async function fetchRemainingLimit() {
  if (!form.value.member_id) return
  try {
    const year = form.value.claim_date ? new Date(form.value.claim_date).getFullYear() : undefined

    const res = await claimStore.getMemberRemainingLimit(form.value.member_id, year)
    remainingLimitData.value = res.data
  } catch (e) {
    remainingLimitData.value = null
  }
}

async function submitClaim() {
  const { valid } = await formRef.value.validate()
  if (!valid) return
  saving.value = true
  formErrors.value = {}
  try {
    const payload = {
      ...form.value,
      claim_date: formatDateToString(form.value.claim_date),
    }
    const res = await claimStore.submitClaim(payload)
    claimResult.value = res
    showSubmitDialog.value = false
    showResultDialog.value = true
    triggerConfetti()
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
    await claimStore.deleteClaim(deleteTarget.value.id)
    toast.success('Claim deleted successfully')
    showDeleteDialog.value = false
  } catch (e) {
    toast.error(e.response?.data?.message || 'An error occurred')
  } finally {
    deleting.value = false
  }
}

function formatDateToString(date) {
  if (!date) return ''
  const d = new Date(date)
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}
const formErrors = ref({})

function customFilter(value, query, item) {
  if (!query) return true
  const q = query.toLowerCase()
  const raw = item?.raw
  if (!raw) return false
  const status = parseFloat(raw.excess_amount) > 0
    ? (parseFloat(raw.approved_amount) > 0 ? 'Partial' : 'Rejected')
    : 'Approved'
  const searchable = [
    raw.member?.name,
    raw.member?.policy_number,
    raw.member?.benefit_plan?.plan_name,
    formatDate(raw.claim_date),
    formatCurrency(raw.claim_amount),
    formatCurrency(raw.approved_amount),
    formatCurrency(raw.excess_amount),
    status,
  ].filter(Boolean).join(' ').toLowerCase()
  return searchable.includes(q)
}

const triggerConfetti = () => {
  const count = 200
  const defaults = { origin: { y: 0.7 }, zIndex: 999999 }

  function fire(particleRatio, opts) {
    confetti({
      ...defaults,
      ...opts,
      particleCount: Math.floor(count * particleRatio),
    })
  }

  fire(0.25, { spread: 26, startVelocity: 55 })
  fire(0.2, { spread: 60 })
  fire(0.35, { spread: 100, decay: 0.91, scalar: 0.8 })
  fire(0.1, { spread: 120, startVelocity: 25, decay: 0.92, scalar: 1.2 })
  fire(0.1, { spread: 120, startVelocity: 45 })
}

onMounted(() => {
  claimStore.getAllClaims()
  memberStore.getAllMembers()
})
</script>
