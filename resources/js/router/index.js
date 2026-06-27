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
        path: '/spaces',
        name: 'spaces',
        component: () => import('@/views/customer/SearchView.vue'),
    },
    {
        path: '/spaces/:slug',
        name: 'spaces.show',
        component: () => import('@/views/customer/SpaceDetailView.vue'),
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

    // ── Customer (auth) ─────────────────────────────────
    {
        path: '/bookings',
        name: 'bookings',
        component: () => import('@/views/customer/BookingsView.vue'),
        meta: { requiresAuth: true },
    },

    // ── Host ────────────────────────────────────────────
    {
        path: '/host',
        name: 'host.dashboard',
        component: () => import('@/views/host/HostDashboard.vue'),
        meta: { requiresAuth: true, role: 'host' },
    },
    {
        path: '/host/listings',
        name: 'host.listings',
        component: () => import('@/views/host/MyListings.vue'),
        meta: { requiresAuth: true, role: 'host' },
    },
    {
        path: '/host/listings/new',
        name: 'host.listings.create',
        component: () => import('@/views/host/CreateListing.vue'),
        meta: { requiresAuth: true, role: 'host' },
    },
    {
        path: '/host/bookings',
        name: 'host.bookings',
        component: () => import('@/views/host/HostBookings.vue'),
        meta: { requiresAuth: true, role: 'host' },
    },

    // ── Admin ───────────────────────────────────────────
    {
        path: '/admin',
        name: 'admin.dashboard',
        component: () => import('@/views/admin/AdminDashboard.vue'),
        meta: { requiresAuth: true, role: 'admin' },
    },
    {
        path: '/admin/spaces',
        name: 'admin.spaces',
        component: () => import('@/views/admin/AdminSpaces.vue'),
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

    if (!auth.user && auth.token) {
        await auth.fetchUser()
    }

    if (to.meta.guestOnly && auth.isAuthenticated) {
        if (auth.isAdmin) return { name: 'admin.dashboard' }
        if (auth.isHost)  return { name: 'host.dashboard' }
        return { name: 'spaces' }
    }

    if (to.meta.requiresAuth) {
        if (!auth.isAuthenticated) {
            return { name: 'login', query: { redirect: to.fullPath } }
        }
        if (to.meta.role && auth.user?.role !== to.meta.role) {
            return { name: 'spaces' }
        }
    }
})

export default router
