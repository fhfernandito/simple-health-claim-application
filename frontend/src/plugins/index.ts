import router from '../router';
import i18n from './i18n';
import {createPinia} from 'pinia';
/**
 * plugins/index.ts
 *
 * Automatically included in `./src/main.ts`
 */

// Types
import type { App } from 'vue'

// Plugins
import vuetify from './vuetify'
import VueToastificationPlugin from 'vue-toastification'
import 'vue-toastification/dist/index.css'

const toastOptions = {
  position: "top-right" as const,
  timeout: 7000,
  closeOnClick: true,
  pauseOnHover: true,
  draggable: true,
  progress: undefined,
};

export function registerPlugins (app: App) {
 app.use(vuetify)
 app.use(createPinia());
 app.use(i18n);
 app.use(router);
 app.use(VueToastificationPlugin, toastOptions);
}