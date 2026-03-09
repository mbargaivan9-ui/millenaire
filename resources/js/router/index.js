import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Pages
import Dashboard from '@/pages/Dashboard.vue'
import ParentChildren from '@/pages/ParentChildren.vue'
import { LoginForm, LiveBulletin } from '@/components'

// Route definitions
const routes = [
  // Auth Routes
  {
    path: '/login',
    name: 'login',
    component: LoginForm,
    meta: { requiresAuth: false, layout: 'blank' }
  },
  {
    path: '/register',
    name: 'register',
    component: { template: '<div>Register page coming soon</div>' },
    meta: { requiresAuth: false, layout: 'blank' }
  },

  // Dashboard
  {
    path: '/',
    name: 'dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },

  // Teacher Routes
  {
    path: '/teacher',
    meta: { requiresAuth: true, roles: ['teacher', 'principal'] },
    children: [
      {
        path: 'bulletin',
        name: 'teacher-bulletin',
        component: LiveBulletin,
        meta: { requiresAuth: true, roles: ['teacher', 'principal'] }
      },
      {
        path: 'classroom',
        name: 'teacher-classroom',
        component: { template: '<div class="p-8"><h1 class="text-3xl font-bold">Classroom Management</h1></div>' },
        meta: { requiresAuth: true, roles: ['teacher', 'principal'] }
      },
      {
        path: 'templates',
        name: 'teacher-templates',
        component: { template: '<div class="p-8"><h1 class="text-3xl font-bold">Templates</h1></div>' },
        meta: { requiresAuth: true, roles: ['teacher', 'principal'] }
      },
      {
        path: 'assignments',
        name: 'teacher-assignments',
        component: { template: '<div class="p-8"><h1 class="text-3xl font-bold">Assignments</h1></div>' },
        meta: { requiresAuth: true, roles: ['teacher', 'principal'] }
      }
    ]
  },

  // Principal Routes
  {
    path: '/principal',
    meta: { requiresAuth: true, roles: ['principal'] },
    children: [
      {
        path: 'students',
        name: 'principal-students',
        component: { template: '<div class="p-8"><h1 class="text-3xl font-bold">All Students</h1></div>' },
        meta: { requiresAuth: true, roles: ['principal'] }
      },
      {
        path: 'reports',
        name: 'principal-reports',
        component: { template: '<div class="p-8"><h1 class="text-3xl font-bold">Reports</h1></div>' },
        meta: { requiresAuth: true, roles: ['principal'] }
      },
      {
        path: 'management',
        name: 'principal-management',
        component: { template: '<div class="p-8"><h1 class="text-3xl font-bold">Management</h1></div>' },
        meta: { requiresAuth: true, roles: ['principal'] }
      }
    ]
  },

  // Parent Routes
  {
    path: '/parent',
    meta: { requiresAuth: true, roles: ['parent'] },
    children: [
      {
        path: 'dashboard',
        name: 'parent-dashboard',
        component: Dashboard,
        meta: { requiresAuth: true, roles: ['parent'] }
      },
      {
        path: 'children',
        name: 'parent-children',
        component: ParentChildren,
        meta: { requiresAuth: true, roles: ['parent'] }
      },
      {
        path: 'payments',
        name: 'parent-payments',
        component: { template: '<div class="p-8"><h1 class="text-3xl font-bold">Payments</h1></div>' },
        meta: { requiresAuth: true, roles: ['parent'] }
      },
      {
        path: 'announcements',
        name: 'parent-announcements',
        component: { template: '<div class="p-8"><h1 class="text-3xl font-bold">Announcements</h1></div>' },
        meta: { requiresAuth: true, roles: ['parent'] }
      }
    ]
  },

  // Student Routes
  {
    path: '/student',
    meta: { requiresAuth: true, roles: ['student'] },
    children: [
      {
        path: 'grades',
        name: 'student-grades',
        component: { template: '<div class="p-8"><h1 class="text-3xl font-bold">My Grades</h1></div>' },
        meta: { requiresAuth: true, roles: ['student'] }
      },
      {
        path: 'performance',
        name: 'student-performance',
        component: { template: '<div class="p-8"><h1 class="text-3xl font-bold">Performance</h1></div>' },
        meta: { requiresAuth: true, roles: ['student'] }
      },
      {
        path: 'announcements',
        name: 'student-announcements',
        component: { template: '<div class="p-8"><h1 class="text-3xl font-bold">Announcements</h1></div>' },
        meta: { requiresAuth: true, roles: ['student'] }
      },
      {
        path: 'certificates',
        name: 'student-certificates',
        component: { template: '<div class="p-8"><h1 class="text-3xl font-bold">Certificates</h1></div>' },
        meta: { requiresAuth: true, roles: ['student'] }
      }
    ]
  },

  // Admin Routes
  {
    path: '/admin',
    meta: { requiresAuth: true, roles: ['admin'] },
    children: [
      {
        path: 'dashboard',
        name: 'admin-dashboard',
        component: Dashboard,
        meta: { requiresAuth: true, roles: ['admin'] }
      },
      {
        path: 'users',
        name: 'admin-users',
        component: { template: '<div class="p-8"><h1 class="text-3xl font-bold">Users Management</h1></div>' },
        meta: { requiresAuth: true, roles: ['admin'] }
      },
      {
        path: 'classes',
        name: 'admin-classes',
        component: { template: '<div class="p-8"><h1 class="text-3xl font-bold">Classes</h1></div>' },
        meta: { requiresAuth: true, roles: ['admin'] }
      },
      {
        path: 'financial',
        name: 'admin-financial',
        component: { template: '<div class="p-8"><h1 class="text-3xl font-bold">Financial Overview</h1></div>' },
        meta: { requiresAuth: true, roles: ['admin'] }
      },
      {
        path: 'audit',
        name: 'admin-audit',
        component: { template: '<div class="p-8"><h1 class="text-3xl font-bold">Audit Logs</h1></div>' },
        meta: { requiresAuth: true, roles: ['admin'] }
      }
    ]
  },

  // Settings Routes
  {
    path: '/profile',
    name: 'profile',
    component: { template: '<div class="p-8"><h1 class="text-3xl font-bold">Profile Settings</h1></div>' },
    meta: { requiresAuth: true }
  },
  {
    path: '/settings',
    name: 'settings',
    component: { template: '<div class="p-8"><h1 class="text-3xl font-bold">Settings</h1></div>' },
    meta: { requiresAuth: true }
  },

  // 404 Not Found
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: {
      template: `
        <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#F5F7FA] to-[#E8ECEF]">
          <div class="text-center">
            <h1 class="text-6xl font-bold text-gray-800 mb-4">404</h1>
            <p class="text-2xl text-gray-600 mb-8">Page non trouvée</p>
            <router-link to="/" class="px-6 py-3 rounded-lg bg-[#2E5BFF] text-white font-bold hover:shadow-lg transition-all">
              Retourner à l'accueil
            </router-link>
          </div>
        </div>
      `
    },
    meta: { requiresAuth: false }
  }
]

// Create router
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

// Global navigation guard
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  // Check if route requires auth
  if (to.meta.requiresAuth) {
    if (!authStore.isAuthenticated) {
      next('/login')
      return
    }

    // Check if user has required role
    if (to.meta.roles && !to.meta.roles.includes(authStore.user?.role)) {
      next('/')
      return
    }
  }

  // Redirect authenticated users away from login
  if (to.path === '/login' && authStore.isAuthenticated) {
    next('/')
    return
  }

  next()
})

// After navigation: scroll to top
router.afterEach(() => {
  window.scrollTo(0, 0)
})

export default router
