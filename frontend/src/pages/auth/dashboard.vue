<template>
  <v-container 
    fluid 
    class="rounded-ts-xl" 
    min-height="calc(100dvh - 60px)"
  >
    <div style="max-width: 1200px;" class="mx-auto">
      <div class="d-flex align-end justify-space-between mb-6">
        <div>
          <p class="text-headline-small font-weight-bold">Dashboard</p>
          <p class="text-body-medium text-medium-emphasis mt-1">Overview of your health claim system</p>
        </div>
        <v-btn
          color="primary"
          variant="tonal"
          rounded="lg"
          class="text-none"
          prepend-icon="mdi-refresh"
          :loading="dashboardStore.loading"
          @click="dashboardStore.getDashboardStats()"
        >
          Refresh
        </v-btn>
      </div>
  
      <v-row class="mb-2" density="compact">
        <v-col cols="12" sm="6" md="3">
          <v-card rounded="xl" elevation="0" color="primary" variant="flat" height="140">
            <v-card-text class="pa-5">
              <p class="text-body-medium mb-1">Total Members</p>
              <div class="d-flex align-center justify-space-between">
                <div>
                  <p class="text-headline-large font-weight-bold">{{ stats?.total_members || 0 }}</p>
                </div>                  
              </div>
            </v-card-text>
            <div class="position-absolute" style="bottom: -25px; right: -25px;">
              <v-icon size="110">mdi-account-group-outline</v-icon>
            </div>
          </v-card>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-card rounded="xl" elevation="0" color="accent" variant="flat" height="140" theme="light">
            <v-card-text class="pa-5">
              <p class="text-body-medium mb-1">Total Claims</p>
              <div class="d-flex align-center justify-space-between">
                <div>
                  <p class="text-headline-large font-weight-bold">{{ stats?.total_claims || 0 }}</p>
                </div>
              </div>
            </v-card-text>
            <div class="position-absolute" style="bottom: -25px; right: -25px;">
              <v-icon size="85">mdi-file-document-multiple</v-icon>
            </div>
          </v-card>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-card rounded="xl" elevation="0" color="success" variant="flat" height="140" theme="light">
            <v-card-text class="pa-5">
              <p class="text-body-medium mb-1">Total Approved</p>
              <div class="d-flex align-center justify-space-between">
                <div>
                  <p class="text-headline-small font-weight-bold">{{ formatCurrency(stats?.total_approved || 0) }}</p>
                </div>
              </div>
            </v-card-text>
            <div class="position-absolute" style="bottom: -25px; right: -25px;">
              <v-icon size="95">mdi-check-circle</v-icon>
            </div>
          </v-card>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-card rounded="xl" elevation="0" color="error" variant="flat" height="140">
            <v-card-text class="pa-5">
              <p class="text-body-medium mb-1">Total Excess</p>
              <div class="d-flex align-center justify-space-between">
                <div>
                  <p class="text-headline-small font-weight-bold">{{ formatCurrency(stats?.total_excess || 0) }}</p>
                </div>
              </div>
            </v-card-text>
            <div class="position-absolute" style="bottom: -25px; right: -15px;">
              <v-icon size="95">mdi-alert-circle</v-icon>
            </div>
          </v-card>
        </v-col>
      </v-row>

      <v-row density="compact" class="mb-2">
        <v-col cols="12" md="8">
          <v-card rounded="xl" variant="flat" class="pa-5" height="100%" border="sm" color="transparent">
            <div class="mb-4">
              <p class="text-title-medium font-weight-bold">Monthly Claims Overview</p>
            </div>
            <Bar 
              v-if="monthlyChartData"
              :key="'bar-' + chartKey"
              :data="monthlyChartData" 
              :options="barChartOptions" 
              style="max-height: 300px;"
            />
            <div v-else class="d-flex align-center justify-center" style="height: 300px;">
              <v-progress-circular indeterminate color="primary" />
            </div>
          </v-card>
        </v-col>
        <v-col cols="12" md="4">
          <v-card rounded="xl" elevation="0" variant="flat" class="pa-5" height="100%" border="sm" color="transparent">
            <div class="mb-4">
              <p class="text-title-medium font-weight-bold">Claims by Plan</p>
            </div>
            <Doughnut
              v-if="planChartData"
              :key="'doughnut-' + chartKey"
              :data="planChartData" 
              :options="doughnutChartOptions" 
              style="max-height: 300px;"
            />
            <div v-else class="d-flex align-center justify-center" style="height: 300px;">
              <v-progress-circular indeterminate color="primary" />
            </div>
          </v-card>
        </v-col>
      </v-row>

      <v-row density="compact">
        <v-col cols="12" md="4">
          <v-card rounded="xl" elevation="0" variant="flat" class="pa-5" border="sm" color="transparent">
            <div class="mb-4">
              <p class="text-title-medium font-weight-bold">Activity Timeline</p>
            </div>
            <div style="max-height: 420px; overflow-y: auto;" class="pr-2">
              <v-timeline density="compact" side="end" line-thickness="2" line-color="primary">
                <v-timeline-item
                  v-for="activity in stats?.activity_log || []"
                  :key="activity.id"
                  :dot-color="getStatusColor(activity.status)"
                  size="x-small"
                >
                  <div>
                    <p class="text-body-small font-weight-medium">{{ activity.member_name }}</p>
                    <p class="text-label-small text-medium-emphasis">{{ activity.policy_number }}</p>
                    <div class="d-flex align-center ga-2 mt-1">
                      <v-chip 
                        :color="getStatusColor(activity.status)" 
                        variant="tonal" 
                        size="x-small"
                        label
                      >
                        {{ activity.status_label }}
                      </v-chip>
                      <span class="text-label-small text-medium-emphasis">{{ formatCurrency(activity.claim_amount) }}</span>
                    </div>
                    <p class="text-label-small text-disabled mt-1">{{ activity.time_ago }}</p>
                  </div>
                </v-timeline-item>
              </v-timeline>
              <div v-if="!stats?.activity_log?.length" class="text-center py-8">
                <v-icon size="48" color="disabled">mdi-timeline-clock-outline</v-icon>
                <p class="text-body-small text-disabled mt-2">No activity yet</p>
              </div>
            </div>
          </v-card>
        </v-col>
        <v-col cols="12" md="8">
          <v-card rounded="xl" elevation="0" variant="flat" class="pa-5" border="sm" color="transparent">
            <div class="mb-4">
              <p class="text-title-medium font-weight-bold">Recent Claims</p>
            </div>
            <v-data-table
              :headers="headers"
              :items="stats?.recent_claims || []"
              :items-per-page="10"
              :loading="!stats"
              class="bg-transparent"
            >
              <template #item.index="{ index }">
                {{ index + 1 }}
              </template>
              <template #item.member="{ item }">
                <div>
                  <p class="font-weight-medium">{{ item.member?.name }}</p>
                  <p class="text-label-small text-medium-emphasis">{{ item.member?.policy_number }}</p>
                </div>
              </template>
              <template #item.claim_date="{ item }">
                {{ formatDate(item.claim_date) }}
              </template>
              <template #item.claim_amount="{ item }">
                {{ formatCurrency(item.claim_amount) }}
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
            </v-data-table>
          </v-card>
        </v-col>
      </v-row>
    </div>
  </v-container>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useTheme } from 'vuetify'
import { useDashboardStore } from '@/stores/dashboard'
import { formatCurrency, formatDate } from '@/utils/format'
import { Bar, Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
} from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, Title, Tooltip, Legend)

const dashboardStore = useDashboardStore()
const stats = computed(() => dashboardStore.stats)
const theme = useTheme()
const chartKey = ref(0)

watch(stats, (val, oldVal) => {
  if (val && !oldVal) chartKey.value++
})

const headers = [
  { title: 'No', key: 'index', sortable: false, width: '60px' },
  { title: 'Member', key: 'member', sortable: false },
  { title: 'Claim Date', key: 'claim_date' },
  { title: 'Claim Amount', key: 'claim_amount' },
  { title: 'Approved', key: 'approved_amount' },
  { title: 'Excess', key: 'excess_amount' },
]

const chartColors = computed(() => {
  const isDark = theme.global.name.value === 'dark'
  return {
    claimed: isDark ? '#38BDF8' : '#0EA5E9',
    approved: isDark ? '#4ADE80' : '#2ab25c',
    excess: isDark ? '#F87171' : '#EF4444',
    primary: isDark ? '#00baa2' : '#0D9488',
    secondary: isDark ? '#818CF8' : '#6366F1',
    gridColor: isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)',
    textColor: isDark ? 'rgba(255,255,255,0.7)' : 'rgba(0,0,0,0.6)',
  }
})

const monthlyChartData = computed(() => {
  const monthly = stats.value?.monthly_claims
  if (!monthly) return null

  return {
    labels: monthly.map(m => m.month),
    datasets: [
      {
        label: 'Claimed',
        data: monthly.map(m => m.total_claimed),
        backgroundColor: chartColors.value.claimed + '80',
        borderColor: chartColors.value.claimed,
        borderWidth: 1,
        borderRadius: 6,
      },
      {
        label: 'Approved',
        data: monthly.map(m => m.total_approved),
        backgroundColor: chartColors.value.approved + '80',
        borderColor: chartColors.value.approved,
        borderWidth: 1,
        borderRadius: 6,
      },
      {
        label: 'Excess',
        data: monthly.map(m => m.total_excess),
        backgroundColor: chartColors.value.excess + '80',
        borderColor: chartColors.value.excess,
        borderWidth: 1,
        borderRadius: 6,
      },
    ],
  }
})

const barChartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  animation: {
    duration: 1200,
    easing: 'easeOutQuart',
  },
  transitions: {
    active: {
      animation: { duration: 300 },
    },
  },
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        usePointStyle: true,
        pointStyle: 'rectRounded',
        padding: 20,
        color: chartColors.value.textColor,
        font: { size: 12, family: 'Manrope' },
      },
    },
    tooltip: {
      backgroundColor: 'rgba(0,0,0,0.8)',
      titleFont: { family: 'Manrope' },
      bodyFont: { family: 'Manrope' },
      padding: 12,
      cornerRadius: 8,
      callbacks: {
        label: (ctx) => `${ctx.dataset.label}: ${formatCurrency(ctx.raw)}`,
      },
    },
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: { color: chartColors.value.textColor, font: { size: 11, family: 'Manrope' } },
    },
    y: {
      grid: { color: chartColors.value.gridColor },
      ticks: {
        color: chartColors.value.textColor,
        font: { size: 11, family: 'Manrope' },
        callback: (value) => {
          if (value >= 1000000) return `${(value / 1000000).toFixed(1)}M`
          if (value >= 1000) return `${(value / 1000).toFixed(0)}K`
          return value
        },
      },
    },
  },
}))

const planColors = ['#0D9488', '#0EA5E9', '#6366F1', '#F59E0B', '#EF4444']

const planChartData = computed(() => {
  const plans = stats.value?.claims_by_plan
  if (!plans) return null

  return {
    labels: plans.map(p => p.plan_name),
    datasets: [
      {
        data: plans.map(p => p.total_claims),
        backgroundColor: plans.map((_, i) => planColors[i % planColors.length] + 'CC'),
        borderColor: plans.map((_, i) => planColors[i % planColors.length]),
        borderWidth: 2,
        hoverOffset: 8,
      },
    ],
  }
})

const doughnutChartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  cutout: '65%',
  animation: {
    animateRotate: true,
    animateScale: true,
    duration: 1200,
    easing: 'easeOutQuart',
  },
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        usePointStyle: true,
        pointStyle: 'circle',
        padding: 16,
        color: chartColors.value.textColor,
        font: { size: 12, family: 'Manrope' },
      },
    },
    tooltip: {
      backgroundColor: 'rgba(0,0,0,0.8)',
      titleFont: { family: 'Manrope' },
      bodyFont: { family: 'Manrope' },
      padding: 12,
      cornerRadius: 8,
      callbacks: {
        label: (ctx) => `${ctx.label}: ${ctx.raw} claims`,
      },
    },
  },
}))

function getStatusColor(status) {
  const map = {
    approved: 'success',
    partial: 'warning',
    excess: 'error',
    pending: 'primary',
  }
  return map[status] || 'primary'
}

onMounted(() => {
  dashboardStore.getDashboardStats()
})
</script>