import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
    // ── Public ──────────────────────────────────────────
    {
        path: '/',
        name: 'home',
        component: () => import('@/views/customer/HomeView.vue'),
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('@/views/customer/LoginView.vue'),
        meta: { guestOnly: true },
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('@/views/customer/RegisterView.vue'),
        meta: { guestOnly: true },
    },

    // ── Host ────────────────────────────────────────────
    {
        path: '/host',
        name: 'host.dashboard',
        component: () => import('@/views/host/HostDashboard.vue'),
        meta: { requiresAuth: true, role: 'host' },
    },

    // ── Admin ───────────────────────────────────────────
    {
        path: '/admin',
        name: 'admin.dashboard',
        component: () => import('@/views/admin/AdminDashboard.vue'),
        meta: { requiresAuth: true, role: 'admin' },
    },

    // ── Catch-all ───────────────────────────────────────
    {
        path: '/:pathMatch(.*)*',
        redirect: '/',
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

router.beforeEach(async (to) => {
    const auth = useAuthStore()

    // Rehydrate user on first load if token exists in localStorage
    if (!auth.user && auth.token) {
        await auth.fetchUser()
    }

    // Redirect already-logged-in users away from /login and /register
    if (to.meta.guestOnly && auth.isAuthenticated) {
        if (auth.isAdmin) return { name: 'admin.dashboard' }
        if (auth.isHost)  return { name: 'host.dashboard' }
        return { name: 'home' }
    }

    // Protect auth-required routes
    if (to.meta.requiresAuth) {
        if (!auth.isAuthenticated) {
            return { name: 'login', query: { redirect: to.fullPath } }
        }

        if (to.meta.role && auth.user?.role !== to.meta.role) {
            return { name: 'home' }
        }
    }
})

export default router
