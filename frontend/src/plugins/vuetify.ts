/**
 * plugins/vuetify.ts
 *
 * Framework documentation: https://vuetifyjs.com`
 */

// Composables
import { createVuetify } from 'vuetify'
import { VDateInput } from 'vuetify/labs/VDateInput'
// Styles
import '@mdi/font/css/materialdesignicons.css'

import '../styles/layers.css'
import 'vuetify/styles'

// https://vuetifyjs.com/en/introduction/why-vuetify/#feature-guides
export default createVuetify({
  components: {
    VDateInput,
  },
  theme: {
    defaultTheme: localStorage.getItem('theme') || 'light',
    themes: {
      light: {
        dark: false,
        colors: {              
          primary: '#0D9488',
          secondary: '#6366F1',
          accent: '#0EA5E9',
          success: '#2ab25cff',
          warning: '#F59E0B',
          error: '#EF4444',
          backgroundOne:'#f0fdfa',
          backgroundTwo:'#ffffff',
        },
      },
      dark: {
        dark: true,
        colors: {              
          primary: '#00baa2ff',
          secondary: '#818CF8',
          accent: '#38BDF8',
          success: '#4ADE80',
          warning: '#FBBF24',
          error: '#F87171',
          backgroundOne: '#0f1729',
          backgroundTwo: '#1a2438',
        },
      },
    },
  },
})
