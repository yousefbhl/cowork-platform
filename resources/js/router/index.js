import { createRouter, createWebHistory } from 'vue-router'

const routes = [
    {
        path: '/',
        component: () => import('@/views/customer/HomeView.vue'),
    },
    {
        path: '/host',
        component: () => import('@/views/host/HostDashboard.vue'),
        meta: { requiresAuth: true, role: 'host' },
    },
    {
        path: '/admin',
        component: () => import('@/views/admin/AdminDashboard.vue'),
        meta: { requiresAuth: true, role: 'admin' },
    },
    {
        path: '/login',
        component: () => import('@/views/customer/LoginView.vue'),
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

// Route guard - will plug into auth store later
router.beforeEach((to) => {
    if (to.meta.requiresAuth) {
        // placeholder - Phase 2 wires this to Pinia auth store
        console.log('Guard: route requires auth + role:', to.meta.role)
    }
})

export default router
