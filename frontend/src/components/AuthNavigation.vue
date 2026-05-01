<template>
  <v-navigation-drawer
    v-model="showDrawer"
    width="64"
    :permanent="!$vuetify.display.mdAndDown"
    :temporary="$vuetify.display.mdAndDown"
    :scrim="false"
    class="pa-2 border-e-0"
    color="backgroundOne"
    border="0"    
    elevation="0"
  >
    <template #prepend>
      <v-hover v-slot="{ props, isHovering }">
        <v-card
          v-bind="props" 
          rounded="lg"
          :variant="(isHovering) ? 'tonal' : 'flat'"
          :color="(isHovering) ? 'primary' : 'transparent'"
          width="fit-content"
          @click="drawerSecondRail = !drawerSecondRail"
        >
          <v-btn
            icon="mdi-menu"
            variant="text"
            rounded="xl"
            readonly
          ></v-btn>
        </v-card>
      </v-hover>
    </template>
    <div class="d-flex flex-column ga-2 mt-3">
      <v-hover v-slot="{ props, isHovering }" v-for="item in railItems" :key="item.value">
        <v-card       
          v-bind="props" 
          rounded="lg"
          width="fit-content"        
          v-tooltip="{text: item.tooltip, location: 'right'}"          
          :variant="navOption === item.value ? 'flat' : (isHovering ? 'tonal' : 'flat')"
          :color="(navOption === item.value || isHovering) ? 'primary' : 'transparent'"
          @click="navOption = item.value"
        >
          <v-btn
            :icon="item.icon"
            variant="text"
            rounded="xl"
            readonly
          ></v-btn>
        </v-card>
      </v-hover>
    </div>    
  </v-navigation-drawer>

  <v-navigation-drawer      
    v-model="showDrawer"
    width="250"    
    border="0"    
    color="backgroundTwo"
    class="py-2 border-e-0"
    :rail="drawerSecondRail"
    :permanent="!$vuetify.display.mdAndDown"
    :temporary="$vuetify.display.mdAndDown"
    :class="drawerSecondRail ? undefined : 'px-1'"
    :style="$vuetify.display.mdAndDown && showDrawer ? 'margin-left: 64px' : undefined"
  >
    <template #prepend>
      <div class="mt-3" :class="drawerSecondRail ? undefined : 'ms-1'">
        <div class="d-flex align-center ga-2" :class="drawerSecondRail ? 'justify-center' : undefined">
          <v-icon color="primary">mdi-hospital-box</v-icon>
          <template v-if="!drawerSecondRail">
            <span class="text-body-1 font-weight-bold">Health Claim</span>
          </template>
        </div>
      </div>
    </template>
    <v-list density="comfortable" nav class="mt-4">
      <template v-if="navOption === 'home'">
        <template v-if="!drawerSecondRail">
          <v-list-subheader class="text-title-small font-weight-bold text-uppercase px-0">Overview</v-list-subheader>      
        </template>        
        <v-list-item prepend-icon="mdi-view-dashboard" title="Dashboard" rounded="lg" color="primary" to="/auth/dashboard" />
      </template>

      <template v-if="navOption === 'data'">
        <template v-if="!drawerSecondRail">
          <v-list-subheader class="text-title-small font-weight-bold text-uppercase px-0">Data Management</v-list-subheader>      
        </template>
        <v-list-item prepend-icon="mdi-account-group" title="Members" rounded="lg" color="primary" to="/auth/members" />
        <v-list-item prepend-icon="mdi-clipboard-text" title="Benefit Plans" rounded="lg" color="primary" to="/auth/benefit-plans" />
      </template>

      <template v-if="navOption === 'claims'">
        <template v-if="!drawerSecondRail">
          <v-list-subheader class="text-title-small font-weight-bold text-uppercase px-0">Claims</v-list-subheader>      
        </template>
        <v-list-item prepend-icon="mdi-cash-check" title="All Claims" rounded="lg" color="primary" to="/auth/claims" />
      </template>

      <template v-if="navOption === 'settings'">
        <v-list-group value="settings">
          <template #activator="{ props }">
            <v-list-item v-bind="props" prepend-icon="mdi-cog" title="Settings" rounded="lg" color="primary" />
          </template>
  
          <v-list-group value="display">
            <template #activator="{ props }">
              <v-list-item style="padding-inline-start: 8px !important;" v-bind="props" prepend-icon="mdi-circle-medium" title="Display" rounded="lg" />
            </template>
  
            <v-list-item style="padding-inline-start: 65px !important;" :active="!isDark" prepend-icon="mdi-white-balance-sunny" title="Light" value="light" @click="toggleTheme($event, false)" rounded="lg" active-class="text-primary" />
            <v-list-item style="padding-inline-start: 65px !important;" :active="isDark" prepend-icon="mdi-weather-night" title="Dark" value="dark" @click="toggleTheme($event, true)" rounded="lg" active-class="text-primary" />
          </v-list-group>
        </v-list-group>
      </template>
    </v-list>    
  </v-navigation-drawer>

  <v-app-bar
    flat    
    height="60"          
    color="backgroundTwo"    
    scroll-behavior="hide"    
  >
    <template #prepend>
      <template v-if="$vuetify.display.mdAndDown">
        <v-btn
          icon="mdi-menu"
          variant="text"
          rounded="xl"
          @click="showDrawer = !showDrawer"
        ></v-btn>
      </template>
      <v-btn
        icon="mdi-magnify"
        variant="text"                        
      ></v-btn>
    </template>
    <template #append>
      <v-btn
        :icon="isDark ? 'mdi-weather-night' : 'mdi-white-balance-sunny'"
        variant="text"                
        @click="toggleTheme($event)"
        size="small"
        class="me-3"
      ></v-btn>
      <v-menu offset="5">
        <template v-slot:activator="{ props }">
          <v-card
            v-bind="props"
            :color="isDark ? 'backgroundOne' : 'white'"
            variant="flat"
            border="sm"
            rounded="pill"              
            height="35"              
            class="d-flex align-center px-1"
          >
            <div class="d-flex align-center ga-1">
              <v-avatar
                color="primary"
                size="25"
              >
                <v-icon size="16" color="white">mdi-account</v-icon>
              </v-avatar>
              <template v-if="!$vuetify.display.xs">
                <p class="text-label-medium font-weight-medium">{{ authStore.user?.name || 'Admin' }}</p>
              </template>
              <v-icon size="small">mdi-chevron-down</v-icon>
            </div>
          </v-card>            
        </template>
        <v-card :color="isDark ? 'backgroundTwo' : 'white'" variant="flat" border="sm" rounded="lg" elevation="0" class="border">
          <div class="pa-4">
            <div class="d-flex align-center ga-2">
              <v-avatar
                color="primary"
                size="45"
              >
                <v-icon color="white">mdi-account</v-icon>
              </v-avatar>
              <div class="d-flex flex-column ga-1">
                <p class="text-body-medium font-weight-medium">{{ authStore.user?.name || 'Admin' }}</p>
                <p class="text-body-small text-medium-emphasis">{{ authStore.user?.email || '-' }}</p>
              </div>
            </div>
            <v-divider class="my-4"></v-divider>
            <v-list class="py-0" lines="one" density="compact">
              <v-list-item subtitle="Sign Out" class="text-body-medium" @click="handleSignOut"></v-list-item>
            </v-list>
          </div>
        </v-card>
      </v-menu>
    </template>
  </v-app-bar>
</template>

<script setup>
import { useRoute, useRouter } from 'vue-router';
import { useTheme } from 'vuetify';
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const showDrawer = ref(true);
const drawerSecondRail = ref(false);

function getNavFromRoute(path) {
  if (path.includes('/auth/members') || path.includes('/auth/benefit-plans')) return 'data'
  if (path.includes('/auth/claims')) return 'claims'
  if (path.includes('/auth/settings')) return 'settings'
  return 'home'
}

const navOption = ref(getNavFromRoute(route.path))

const railItems = [
  { value: 'home', icon: 'mdi-home', tooltip: 'Home' },
  { value: 'data', icon: 'mdi-database', tooltip: 'Data Management' },
  { value: 'claims', icon: 'mdi-cash-check', tooltip: 'Claims' },
  { value: 'settings', icon: 'mdi-cog', tooltip: 'Settings' },
];

const theme = useTheme();
const isDark = computed({
  get: () => theme.global.name.value === "dark",
  set: (v) => {
    const name = v ? "dark" : "light";
    theme.global.name.value = name;
    localStorage.setItem('theme', name);
  },
});

async function toggleTheme(event, forceDark) {
  const newDark = forceDark !== undefined ? forceDark : !isDark.value;
  if (newDark === isDark.value) return;

  if (!document.startViewTransition) {
    isDark.value = newDark;
    return;
  }

  const x = event.clientX;
  const y = event.clientY;
  const endRadius = Math.hypot(
    Math.max(x, window.innerWidth - x),
    Math.max(y, window.innerHeight - y),
  );

  const transition = document.startViewTransition(() => {
    isDark.value = newDark;
  });

  try {
    await transition.ready;

    document.documentElement.animate(
      {
        clipPath: [
          `circle(0px at ${x}px ${y}px)`,
          `circle(${endRadius}px at ${x}px ${y}px)`,
        ],
      },
      {
        duration: 500,
        easing: 'ease-in-out',
        pseudoElement: '::view-transition-new(root)',
      },
    );
  } catch {
  }
}

async function handleSignOut() {
  await authStore.signOut();
  router.push('/');
}

onMounted(async () => {
  if (authStore.isSignedIn() && !authStore.user) {
    try {
      await authStore.getUser();
    } catch (e) {
      await authStore.signOut();
      router.push('/');
    }
  }
});
</script>