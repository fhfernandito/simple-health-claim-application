<template>
  <div
    ref="navBar"
    style="position: fixed; top: 15px; left: 0; width: 100%; z-index: 10; opacity: 0;"
  >
    <div class="d-flex align-center px-4" :class="$vuetify.display.mdAndUp ? 'ga-12' : 'ga-2'">
      <div class="d-flex align-center ga-1">
        <v-icon color="primary">mdi-hospital-box</v-icon>
        <p class="font-weight-medium">Simple Health Claim</p>
      </div>
      <template v-if="$vuetify.display.mdAndUp">
        <div class="d-flex align-center ga-8">
          <p class="text-label-large font-weight-medium">Product</p>
          <p class="text-label-large font-weight-medium">Features</p>
          <p class="text-label-large font-weight-medium">Pricing</p>
          <p class="text-label-large font-weight-medium">Blog</p>
        </div>
      </template>
      <v-spacer></v-spacer>
      <div>
        <v-btn
          @click="handleEnter"
          color="primary"
          rounded="pill"
          class="text-none font-weight-bold text-label-medium"
          variant="flat"
        >
          Get Started
        </v-btn>
      </div>
    </div>
  </div>

  <div class="landing-page">
    <div 
      ref="threeContainer" 
      class="three-bg"
    ></div>

    <v-container 
      class="landing-content d-flex align-center justify-center"
    >
      <div class="d-flex align-center justify-center fill-height">
        <v-row align="center" justify="center" class="text-center">
          <v-col cols="12" ref="chipCol" style="opacity: 0;">
            <v-chip variant="flat">
              <div class="d-flex align-center ga-1">
                <v-icon icon="mdi-hospital-box"></v-icon>
                Simple Health Claim
              </div>
            </v-chip>
          </v-col>
          <v-col cols="12" sm="12" md="10">
            <p 
              ref="typingText"
              class="font-weight-medium" 
              :style="$vuetify.display.smAndDown ? 'font-size: 2rem; letter-spacing: -1px; line-height: 40px !important;' : 'font-size: 4rem; letter-spacing: -5px; line-height: 70px !important;'"                 
            ></p>
          </v-col>
          <v-col cols="12" ref="buttonsCol" style="opacity: 0;">
            <div class="d-flex flex-wrap align-center justify-center ga-2">
              <v-btn
                color="primary"
                size="x-large"
                rounded="pill"
                class="text-none font-weight-bold"
                elevation="0"
                @click="handleEnter"
              >            
                Get Started
              </v-btn>
              <v-btn
                variant="tonal"
                size="x-large"
                rounded="pill"
                class="text-none font-weight-bold"
                style="backdrop-filter: blur(5px);"
                @click="handleEnter"
              >
                Explore to Learn
              </v-btn>
            </div>
          </v-col>
        </v-row>
      </div>    
    </v-container>
  </div>

  <v-dialog v-model="showLogin" max-width="460" persistent>
    <v-card rounded="xl" class="pa-6 pa-sm-8">
      <v-card-title class="text-h5 font-weight-bold text-center pb-2">
        <v-icon color="primary" size="40" class="mb-2">mdi-hospital-box</v-icon>
        <div>Welcome Back</div>
      </v-card-title>
      <v-card-subtitle class="text-center text-body-2 pb-4">
        Sign in to access the dashboard
      </v-card-subtitle>
      <v-card-text class="px-2 px-sm-4">
        <v-form ref="formRef" autocomplete="off" @submit.prevent="login">
          <v-row density="compact">
            <v-col cols="12"><p class="text-label-large text-medium-emphasis">Email<span class="text-error">*</span></p></v-col>
            <v-col cols="12">
              <v-text-field
                v-model="loginForm.email"
                placeholder="e.g admin@admin.com"
                type="email"
                autocomplete="email"
                variant="outlined"
                color="primary"
                rounded="lg"
                density="comfortable"
                prepend-inner-icon="mdi-email-outline"
                hide-details="auto"
                :rules="[
                  v => !!v || 'Email is required.', 
                  v => /.+@.+\..+/.test(v) || 'Email must be valid.',
                  v => v === 'admin@admin.com' || 'Email must be admin@admin.com.'
                ]"
                @keyup.enter="login"
              ></v-text-field>
            </v-col>

            <v-col cols="12"><p class="text-label-large text-medium-emphasis">Password<span class="text-error">*</span></p></v-col>
            <v-col cols="12">
              <v-text-field
                v-model="loginForm.password"
                placeholder="Enter your password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                variant="outlined"
                color="primary"
                rounded="lg"
                density="comfortable"
                prepend-inner-icon="mdi-lock-outline"
                :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                hide-details="auto"
                :rules="[
                  v => !!v || 'Password is required.',
                  v => v === 'password' || 'Password must be password.'
                ]"
                @click:append-inner="showPassword = !showPassword"
                @keyup.enter="login"
              ></v-text-field>
            </v-col>
          </v-row>
        </v-form>
        <v-alert
          v-if="loginError"
          type="error"
          variant="tonal"
          density="compact"
          rounded="lg"
          class="mt-4"
        >
          {{ loginError }}
        </v-alert>
      </v-card-text>
      <v-card-actions class="px-4 pb-2">
        <v-btn
          block
          color="primary"
          size="large"
          rounded="lg"
          variant="flat"
          class="text-none font-weight-bold"
          :loading="loginLoading"
          @click="login"
        >
          Sign In
        </v-btn>
      </v-card-actions>
      <div class="text-center mt-2">
        <p class="text-caption text-medium-emphasis">
          Demo: admin@admin.com / password
        </p>
      </div>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import * as THREE from 'three'
import gsap from 'gsap'
import { TextPlugin } from 'gsap/TextPlugin'

gsap.registerPlugin(TextPlugin)

const router = useRouter()
const authStore = useAuthStore()

const threeContainer = ref(null)
const formRef = ref(null)
const typingText = ref(null)
const chipCol = ref(null)
const buttonsCol = ref(null)
const navBar = ref(null)

const showLogin = ref(false)
const showPassword = ref(false)
const loginLoading = ref(false)
const loginError = ref('')
const loginForm = ref({ email: '', password: '' })

// --- Three.js State (Mejaku particle pattern) ---
let tScene, tCamera, tRenderer
let pMesh, pGeometry
let animationFrameId
let numParticles = 5000
let posArray = null
let colArray = null
let scatteredArray = null
let mouseX = 0
let mouseY = 0
const morphState = { progress: 0 }

// --- Resource Tracker ---
class ResourceTracker {
  constructor() { this.resources = new Set() }
  track(resource) {
    if (!resource) return resource
    if (Array.isArray(resource)) {
      resource.forEach(r => this.track(r))
      return resource
    }
    if (resource.dispose || resource instanceof THREE.Object3D) this.resources.add(resource)
    if (resource instanceof THREE.Object3D) {
      this.track(resource.geometry)
      this.track(resource.material)
      this.track(resource.children)
    } else if (resource instanceof THREE.Material) {
      for (const value of Object.values(resource)) {
        if (value instanceof THREE.Texture) this.track(value)
      }
    }
    return resource
  }
  dispose() {
    for (const resource of this.resources) {
      if (resource instanceof THREE.Object3D && resource.parent) resource.parent.remove(resource)
      if (resource.dispose) resource.dispose()
    }
    this.resources.clear()
  }
}
const resTracker = new ResourceTracker()
const track = resTracker.track.bind(resTracker)

// --- Extract pixels from health.png icon ---
const extractPixels = (src, w, h) => {
  return new Promise((resolve) => {
    const img = new Image()
    img.crossOrigin = "Anonymous"
    img.src = src
    img.onload = () => {
      const canvas = document.createElement('canvas')
      canvas.width = w; canvas.height = h
      const ctx = canvas.getContext('2d')
      ctx.drawImage(img, 0, 0, w, h)
      const data = ctx.getImageData(0, 0, w, h).data
      const pos = []; const col = []
      const isMobile = window.innerWidth < 600
      for (let y = 0; y < h; y++) {
        for (let x = 0; x < w; x++) {
          const alpha = data[(y * w + x) * 4 + 3]
          if (alpha > 128) {
            const sizeMultiplier = isMobile ? 0.04 : 0.08
            const sizeX = (x - w / 2) * sizeMultiplier
            const sizeY = -(y - h / 2) * sizeMultiplier
            pos.push(sizeX, sizeY, (Math.random() - 0.5) * 0.2)
            
            const colors = [
              [0.08, 0.40, 0.08],  // dark forest
              [0.10, 0.55, 0.30],  // dark emerald
              [0.15, 0.45, 0.18],  // deep green
              [0.06, 0.42, 0.22],  // dark shamrock
            ]
            const pick = colors[Math.floor(Math.random() * colors.length)]
            col.push(pick[0], pick[1], pick[2])
          }
        }
      }
      resolve({ pos, col })
    }
    img.onerror = () => { resolve({ pos: [], col: [] }) }
  })
}

// --- Prepare target arrays ---
const prepareTarget = (data, total) => {
  const p = new Float32Array(total * 3)
  const c = new Float32Array(total * 3)
  const len = data.pos.length / 3
  if (len === 0) {
    for (let i = 0; i < total * 3; i++) {
      p[i] = (Math.random() - 0.5) * 10
      c[i] = 1
    }
    return { p, c }
  }
  const step = len / total
  for (let i = 0; i < total; i++) {
    const idx = len > total ? Math.floor(i * step) % len : i % len
    p[i * 3]     = data.pos[idx * 3]
    p[i * 3 + 1] = data.pos[idx * 3 + 1]
    p[i * 3 + 2] = data.pos[idx * 3 + 2]
    if (len <= total && i >= len) {
      p[i * 3]     += (Math.random() - 0.5) * 0.08
      p[i * 3 + 1] += (Math.random() - 0.5) * 0.08
      p[i * 3 + 2] += (Math.random() - 0.5) * 0.2
    }
    c[i * 3]     = data.col[idx * 3]
    c[i * 3 + 1] = data.col[idx * 3 + 1]
    c[i * 3 + 2] = data.col[idx * 3 + 2]
  }
  for (let i = total - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1))
    for (let k = 0; k < 3; k++) {
      const tempP = p[i * 3 + k]; p[i * 3 + k] = p[j * 3 + k]; p[j * 3 + k] = tempP
      const tempC = c[i * 3 + k]; c[i * 3 + k] = c[j * 3 + k]; c[j * 3 + k] = tempC
    }
  }
  return { p, c }
}

const initThreeJSAsync = async () => {
  if (!threeContainer.value) return

  numParticles = window.innerWidth < 600 ? 1500 : 4000

  const width = window.innerWidth
  const height = window.innerHeight

  tScene = new THREE.Scene()
  tCamera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000)
  tCamera.position.z = 12

  tRenderer = new THREE.WebGLRenderer({ alpha: true, antialias: true, powerPreference: "high-performance" })
  tRenderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))
  tRenderer.setSize(width, height)
  threeContainer.value.appendChild(tRenderer.domElement)

  const dataHealth = await extractPixels('/particles/health.png', 100, 100)
  const target = prepareTarget(dataHealth, numParticles)

  posArray = target.p
  colArray = target.c

  scatteredArray = new Float32Array(numParticles * 3)
  for (let i = 0; i < numParticles; i++) {
    scatteredArray[i * 3]     = (Math.random() - 0.5) * 30
    scatteredArray[i * 3 + 1] = (Math.random() - 0.5) * 30
    scatteredArray[i * 3 + 2] = (Math.random() - 0.5) * 15
  }

  pGeometry = track(new THREE.BufferGeometry())
  const initialPos = new Float32Array(scatteredArray)
  const initialCol = new Float32Array(target.c)

  pGeometry.setAttribute('position', new THREE.BufferAttribute(initialPos, 3))
  pGeometry.setAttribute('color', new THREE.BufferAttribute(initialCol, 3))

  const pCanvas = document.createElement('canvas')
  pCanvas.width = 32; pCanvas.height = 32
  const pCtx = pCanvas.getContext('2d')
  pCtx.beginPath(); pCtx.arc(16, 16, 16, 0, Math.PI * 2)
  pCtx.fillStyle = '#ffffff'; pCtx.fill()
  const circleTexture = track(new THREE.CanvasTexture(pCanvas))

  const pMat = track(new THREE.PointsMaterial({
    size: 0.075,
    vertexColors: true,
    transparent: true,
    map: circleTexture,
    alphaTest: 0.1,
    depthWrite: false,
    opacity: 0.9,
    sizeAttenuation: true
  }))

  pMesh = track(new THREE.Points(pGeometry, pMat))
  tScene.add(pMesh)

  window.addEventListener('resize', handleResize)
  window.addEventListener('mousemove', handleMouseMove)
  animateThreeJS()
}

const handleMouseMove = (event) => {
  mouseX = (event.clientX / window.innerWidth) * 2 - 1
  mouseY = -(event.clientY / window.innerHeight) * 2 + 1
}

const handleResize = () => {
  if (!tCamera || !tRenderer || !threeContainer.value) return
  tCamera.aspect = window.innerWidth / window.innerHeight
  tCamera.updateProjectionMatrix()
  tRenderer.setSize(window.innerWidth, window.innerHeight)
}

const animateThreeJS = () => {
  animationFrameId = requestAnimationFrame(animateThreeJS)
  if (!pMesh || !scatteredArray) return

  const positions = pGeometry.attributes.position.array
  const time = Date.now() * 0.0006
  const isMobileDisp = window.innerWidth < 600
  const t = morphState.progress

  for (let i = 0; i < numParticles * 3; i += 3) {
    const sx = scatteredArray[i]
    const sy = scatteredArray[i + 1]
    const sz = scatteredArray[i + 2]
    const tx = posArray[i]
    const ty = posArray[i + 1]
    const tz = posArray[i + 2]

    const x = sx + (tx - sx) * t
    const y = sy + (ty - sy) * t
    const z = sz + (tz - sz) * t

    const noise = Math.sin(time + i * 0.01) * (isMobileDisp ? 0.04 : 0.1) * t

    positions[i]     = x
    positions[i + 1] = y + noise
    positions[i + 2] = z + Math.cos(time + i * 0.01) * (isMobileDisp ? 0.04 : 0.1) * t
  }

  pGeometry.attributes.position.needsUpdate = true

  const targetRotY = mouseX * 0.4 + Math.sin(time * 0.3) * 0.2
  const targetRotX = mouseY * 0.4 + Math.cos(time * 0.5) * 0.1

  pMesh.rotation.y += (targetRotY - pMesh.rotation.y) * 0.05
  pMesh.rotation.x += (targetRotX - pMesh.rotation.x) * 0.05

  tRenderer.render(tScene, tCamera)
}

function handleEnter() {
  if (authStore.isSignedIn()) {
    router.push('/auth/dashboard')
  } else {
    showLogin.value = true
  }
}

async function login() {
  const { valid } = await formRef.value.validate()
  if (!valid) return

  loginLoading.value = true
  loginError.value = ''
  try {
    await authStore.signIn(loginForm.value.email, loginForm.value.password)
    router.push('/auth/dashboard')
  } catch (e) {
    loginError.value = e.response?.data?.message || 'Login failed. Please try again.'
  } finally {
    loginLoading.value = false
  }
}

onMounted(() => {
  if (authStore.isSignedIn()) {
    router.push('/auth/dashboard')
    return
  }
  initThreeJSAsync()
  startIntroAnimation()
})

function startIntroAnimation() {
  const tl = gsap.timeline({ delay: 1 })

  const chipEl = chipCol.value?.$el || chipCol.value
  const buttonsEl = buttonsCol.value?.$el || buttonsCol.value
  const navEl = navBar.value?.$el || navBar.value
  const typeEl = typingText.value?.$el || typingText.value

  gsap.set(chipEl, { opacity: 0, y: 20 })
  gsap.set(buttonsEl, { opacity: 0, y: 20 })

  tl.to(typeEl, {
    duration: 2.5,
    text: {
      value: 'Streamlined health claim management with AI-powered adjudication',
      delimiter: '',
    },
    ease: 'none',
  })

  tl.to(morphState, {
    progress: 1,
    duration: 2,
    ease: 'power2.out',
  })

  tl.to(navEl, {
    opacity: 1,
    duration: 0.6,
    ease: 'power2.out',
  }, '-=2')

  tl.to(chipEl, {
    opacity: 1,
    y: 0,
    duration: 0.6,
    ease: 'power2.out',
  }, '-=1.8')

  tl.to(buttonsEl, {
    opacity: 1,
    y: 0,
    duration: 0.6,
    ease: 'power2.out',
  }, '-=1.6')
}

onBeforeUnmount(() => {
  if (animationFrameId) cancelAnimationFrame(animationFrameId)
  window.removeEventListener('resize', handleResize)
  window.removeEventListener('mousemove', handleMouseMove)
  resTracker.dispose()
  if (tRenderer) {
    tRenderer.dispose()
    tRenderer.forceContextLoss()
  }
})
</script>

<style scoped>
.landing-page {
  position: relative;
  min-height: 100vh;
}

.three-bg {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 0;
  pointer-events: none;
}

.landing-content {
  position: relative;
  z-index: 1;
  min-height: 100dvh;
}

.text-gradient {
  background: linear-gradient(135deg, #2DD4BF, #06B6D4, #818CF8);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
</style>
