<template>
  <div class="flex h-screen bg-[#F5F7FA]">
    <!-- Sidebar -->
    <nav class="w-64 bg-white shadow-lg flex flex-col">
      <!-- Logo -->
      <div class="p-6 border-b-2 border-gray-200">
        <h1 class="text-2xl font-bold text-[#2E5BFF]">
          📚 Millénaire
        </h1>
        <p class="text-xs text-gray-500 mt-1">Gestion Académique</p>
      </div>

      <!-- User Info -->
      <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-br from-[#2E5BFF] to-[#4A7FFF] text-white rounded-lg m-4">
        <p class="font-bold text-sm">{{ userRole }}</p>
        <p class="text-xs opacity-90 truncate">{{ userName }}</p>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
        <router-link
          v-for="item in menuItems"
          :key="item.path"
          :to="item.path"
          class="block px-4 py-3 rounded-lg font-medium transition-all duration-300"
          :class="[
            isActive(item.path)
              ? 'bg-[#2E5BFF] text-white shadow-md'
              : 'text-gray-700 hover:bg-gray-100'
          ]"
        >
          <span class="mr-3">{{ item.icon }}</span>
          {{ item.label }}
        </router-link>
      </nav>

      <!-- Logout -->
      <div class="px-4 py-6 border-t border-gray-200">
        <button
          @click="handleLogout"
          class="w-full px-4 py-2 rounded-lg bg-[#FF3B30] text-white font-medium hover:shadow-lg transition-all duration-300 flex items-center justify-center gap-2"
        >
          <span>🚪</span>
          Déconnexion
        </button>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Top Header -->
      <header class="bg-white border-b-2 border-gray-200 shadow-md px-8 py-4">
        <div class="flex justify-between items-center">
          <h2 class="text-2xl font-bold text-gray-800">{{ currentPageTitle }}</h2>
          <div class="flex items-center gap-6">
            <!-- Notifications -->
            <button class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
              🔔
              <span v-if="notificationCount > 0" class="absolute top-1 right-0 w-5 h-5 bg-[#FF3B30] text-white text-xs rounded-full flex items-center justify-center">
                {{ notificationCount }}
              </span>
            </button>

            <!-- Settings -->
            <button class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
              ⚙️
            </button>

            <!-- Profile Menu -->
            <button
              @click="showProfileMenu = !showProfileMenu"
              class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-100 transition-colors"
            >
              <div class="w-8 h-8 rounded-full bg-[#2E5BFF] text-white flex items-center justify-center font-bold">
                {{ userName.charAt(0) }}
              </div>
              <span class="text-sm font-medium text-gray-700">{{ userName }}</span>
            </button>

            <!-- Profile Dropdown -->
            <div v-if="showProfileMenu" class="absolute top-16 right-8 bg-white border-2 border-gray-200 rounded-lg shadow-lg w-48 z-50">
              <router-link
                to="/profile"
                class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors border-b border-gray-200"
              >
                👤 Profil
              </router-link>
              <router-link
                to="/settings"
                class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors border-b border-gray-200"
              >
                ⚙️ Paramètres
              </router-link>
              <button
                @click="handleLogout"
                class="w-full text-left px-4 py-2 text-[#FF3B30] hover:bg-red-50 transition-colors font-medium"
              >
                🚪 Déconnexion
              </button>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 overflow-y-auto">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const showProfileMenu = ref(false)
const notificationCount = ref(3)

const userName = computed(() => authStore.user?.name || 'Utilisateur')
const userRole = computed(() => {
  const roles = {
    teacher: 'Enseignant',
    principal: 'Principal',
    parent: 'Parent',
    student: 'Étudiant'
  }
  return roles[authStore.user?.role] || 'Utilisateur'
})

const currentPageTitle = computed(() => {
  const titles = {
    '/teacher/bulletin': '📚 Bulletin de Notes',
    '/teacher/classroom': '👥 Ma Classe',
    '/parent/dashboard': '📊 Tableau de Bord',
    '/parent/children': '👶 Mes Enfants',
    '/student/grades': '📈 Mes Notes',
    '/admin/dashboard': '🏢 Administration',
    '/admin/users': '👤 Utilisateurs',
    '/admin/classes': '🏫 Classes'
  }
  return titles[route.path] || 'Tableau de Bord'
})

const menuItems = computed(() => {
  const role = authStore.user?.role
  const baseItems = [
    { icon: '🏠', label: 'Accueil', path: '/' }
  ]

  const roleItems = {
    teacher: [
      { icon: '📚', label: 'Bulletin de Notes', path: '/teacher/bulletin' },
      { icon: '👥', label: 'Ma Classe', path: '/teacher/classroom' },
      { icon: '📋', label: 'Modèles', path: '/teacher/templates' },
      { icon: '🎯', label: 'Allocations', path: '/teacher/assignments' }
    ],
    principal: [
      { icon: '📚', label: 'Bulletin de Notes', path: '/teacher/bulletin' },
      { icon: '👥', label: 'Tous les Étudiants', path: '/principal/students' },
      { icon: '📊', label: 'Rapports', path: '/principal/reports' },
      { icon: '⚙️', label: 'Gestion', path: '/principal/management' }
    ],
    parent: [
      { icon: '📊', label: 'Tableau de Bord', path: '/parent/dashboard' },
      { icon: '👶', label: 'Mes Enfants', path: '/parent/children' },
      { icon: '💰', label: 'Paiements', path: '/parent/payments' },
      { icon: '📢', label: 'Annonces', path: '/parent/announcements' }
    ],
    student: [
      { icon: '📈', label: 'Mes Notes', path: '/student/grades' },
      { icon: '📊', label: 'Performance', path: '/student/performance' },
      { icon: '📢', label: 'Annonces', path: '/student/announcements' },
      { icon: '🎓', label: 'Certificats', path: '/student/certificates' }
    ],
    admin: [
      { icon: '🏢', label: 'Tableau de Bord', path: '/admin/dashboard' },
      { icon: '👤', label: 'Utilisateurs', path: '/admin/users' },
      { icon: '🏫', label: 'Classes', path: '/admin/classes' },
      { icon: '💰', label: 'Finances', path: '/admin/financial' },
      { icon: '🔍', label: 'Audit Log', path: '/admin/audit' }
    ]
  }

  return [...baseItems, ...(roleItems[role] || [])]
})

const isActive = (path) => {
  return route.path === path || route.path.startsWith(path.split('/').slice(0, -1).join('/'))
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
nav {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 transparent;
}

nav::-webkit-scrollbar {
  width: 6px;
}

nav::-webkit-scrollbar-track {
  background: transparent;
}

nav::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}
</style>
