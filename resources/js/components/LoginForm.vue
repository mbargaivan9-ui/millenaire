<template>
  <div class="min-h-screen bg-gradient-to-br from-[#2E5BFF] via-[#4A7FFF] to-[#1A3F99] flex items-center justify-center p-4">
    <!-- Background animation -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-20 left-10 w-72 h-72 bg-[#00C48C] rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse"></div>
      <div class="absolute top-40 right-10 w-72 h-72 bg-[#FF9500] rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse animation-delay-2000"></div>
      <div class="absolute -bottom-8 left-20 w-72 h-72 bg-[#2E5BFF] rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse animation-delay-4000"></div>
    </div>

    <!-- Main container -->
    <div class="relative z-10 w-full max-w-md">
      <!-- Header -->
      <div class="text-center mb-8 animate-fade-in">
        <h1 class="text-4xl font-bold text-white mb-2 drop-shadow-lg">
          Millénaire
        </h1>
        <p class="text-blue-100 text-lg">Gestion Académique Intelligente</p>
      </div>

      <!-- Login Card -->
      <div class="bg-white rounded-2xl shadow-2xl p-8 backdrop-blur-sm bg-opacity-95 animate-slide-up">
        <!-- Role Selector -->
        <div class="mb-6">
          <label class="block text-sm font-semibold text-gray-700 mb-3">Vous êtes:</label>
          <div class="grid grid-cols-2 gap-3">
            <button
              v-for="role in roles"
              :key="role.value"
              @click="selectedRole = role.value"
              :class="[
                'py-3 px-4 rounded-lg font-medium transition-all duration-300 flex items-center justify-center gap-2',
                selectedRole === role.value
                  ? 'bg-[#2E5BFF] text-white shadow-lg scale-105'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              ]"
            >
              <span class="text-xl">{{ role.icon }}</span>
              <span class="text-sm">{{ role.label }}</span>
            </button>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleLogin" class="space-y-4">
          <!-- Email/Phone Input -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              {{ selectedRole === 'student' ? 'Email ou Numéro Étudiant' : 'Email ou Téléphone' }}
            </label>
            <div class="relative">
              <input
                v-model="form.identifier"
                type="text"
                :placeholder="getPlaceholder()"
                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-[#2E5BFF] focus:outline-none transition-colors duration-300 placeholder-gray-400"
                :disabled="isLoading"
              />
              <span class="absolute right-4 top-3.5 text-gray-400">📧</span>
            </div>
            <span v-if="errors.identifier" class="text-sm text-[#FF3B30] mt-1 block">{{ errors.identifier }}</span>
          </div>

          <!-- Password Input -->
          <div>
            <div class="flex justify-between items-center mb-2">
              <label class="block text-sm font-semibold text-gray-700">Mot de passe</label>
              <router-link to="/forgot-password" class="text-xs text-[#2E5BFF] hover:underline font-medium">
                Oubliés?
              </router-link>
            </div>
            <div class="relative">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Votre mot de passe sécurisé"
                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-[#2E5BFF] focus:outline-none transition-colors duration-300"
                :disabled="isLoading"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-4 top-3.5 text-gray-400 hover:text-[#2E5BFF] transition-colors"
              >
                {{ showPassword ? '👁️' : '👁️‍🗨️' }}
              </button>
            </div>
            <span v-if="errors.password" class="text-sm text-[#FF3B30] mt-1 block">{{ errors.password }}</span>
          </div>

          <!-- Remember Me -->
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              v-model="form.remember"
              type="checkbox"
              class="w-4 h-4 rounded border-gray-300 text-[#2E5BFF] focus:ring-[#2E5BFF]"
              :disabled="isLoading"
            />
            <span class="text-sm text-gray-600">Me souvenir de moi</span>
          </label>

          <!-- Error Alert -->
          <transition name="fade">
            <div v-if="errors.general" class="bg-[#FF3B30] bg-opacity-10 border-l-4 border-[#FF3B30] p-4 rounded-lg">
              <p class="text-sm text-[#FF3B30] font-medium">{{ errors.general }}</p>
            </div>
          </transition>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full py-3 rounded-lg font-bold text-white bg-gradient-to-r from-[#2E5BFF] to-[#4A7FFF] hover:shadow-lg transition-all duration-300 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2 mt-6"
          >
            <span v-if="isLoading" class="animate-spin">⏳</span>
            <span v-else>🚀</span>
            {{ isLoading ? 'Connexion en cours...' : 'Se Connecter' }}
          </button>
        </form>

        <!-- Divider -->
        <div class="relative my-6">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-200"></div>
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-white text-gray-500">ou</span>
          </div>
        </div>

        <!-- Sign Up Link -->
        <p class="text-center text-sm text-gray-600">
          Nouvel utilisateur?
          <router-link to="/register" class="text-[#2E5BFF] font-bold hover:underline">
            S'inscrire
          </router-link>
        </p>
      </div>

      <!-- Footer Info -->
      <div class="mt-8 text-center text-blue-100 text-xs">
        <p>🔒 Votre sécurité est notre priorité</p>
        <p class="mt-2">Plateforme académique sécurisée - © 2026 Millénaire Connect</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const selectedRole = ref('teacher')
const showPassword = ref(false)
const isLoading = ref(false)

const form = ref({
  identifier: '',
  password: '',
  remember: false
})

const errors = ref({
  identifier: '',
  password: '',
  general: ''
})

const roles = [
  { value: 'teacher', label: 'Enseignant', icon: '👨‍🏫' },
  { value: 'principal', label: 'Principal', icon: '👔' },
  { value: 'parent', label: 'Parent', icon: '👨‍👩‍👧' },
  { value: 'student', label: 'Étudiant', icon: '🎓' }
]

const getPlaceholder = computed(() => {
  const placeholders = {
    teacher: 'nom@millenaire.cm',
    principal: 'principal@millenaire.cm',
    parent: '+237 6XX XXX XXX',
    student: 'STU2024001 ou email@mil.cm'
  }
  return () => placeholders[selectedRole.value]
})

const validateForm = () => {
  errors.value = { identifier: '', password: '', general: '' }

  if (!form.value.identifier.trim()) {
    errors.value.identifier = 'Veuillez entrer votre email ou identifiant'
    return false
  }

  if (!form.value.password.trim()) {
    errors.value.password = 'Veuillez entrer votre mot de passe'
    return false
  }

  if (form.value.password.length < 6) {
    errors.value.password = 'Le mot de passe doit contenir au moins 6 caractères'
    return false
  }

  return true
}

const handleLogin = async () => {
  if (!validateForm()) return

  isLoading.value = true

  try {
    await authStore.login({
      identifier: form.value.identifier,
      password: form.value.password,
      role: selectedRole.value,
      remember: form.value.remember
    })

    // Successful login - router guard will redirect based on role
    router.push('/')
  } catch (error) {
    errors.value.general = error.response?.data?.message || 'Erreur de connexion. Vérifiez vos identifiants'
    console.error('Login error:', error)
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slide-up {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.6s ease-out;
}

.animate-slide-up {
  animation: slide-up 0.6s ease-out 0.2s backwards;
}

.animation-delay-2000 {
  animation-delay: 2s;
}

.animation-delay-4000 {
  animation-delay: 4s;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
