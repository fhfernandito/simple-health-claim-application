<template>
  <v-app>
    <AuthNavigation v-if="route.fullPath.includes('/auth')" />
    <v-main class="bg-backgroundTwo">
      <router-view />
    </v-main>
  </v-app>
</template>

<script setup>
import { useRoute } from 'vue-router';
import AuthNavigation from '@/components/AuthNavigation.vue';

const route = useRoute()
</script>
