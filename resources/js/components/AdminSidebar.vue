<script setup>
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const auth   = useAuthStore()
const router = useRouter()

async function logout() {
    await auth.logout()
    router.push('/login')
}
</script>

<template>
    <aside class="lf-admin-sidebar d-flex flex-column flex-shrink-0">
        <div class="d-flex align-items-center gap-2 px-3 pt-4 pb-3">
            <span class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: 30px; height: 30px; border-radius: 8px; background: #1B4332;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </span>
            <div>
                <div class="font-display" style="font-size: 15px; color: #1B4332; line-height: 1.1;">Admin Console</div>
                <div style="font-size: 11px; color: #6B6660;">{{ auth.user?.name }}</div>
            </div>
        </div>

        <nav class="px-2 d-flex flex-column gap-1 mt-2">
            <RouterLink to="/admin" class="lf-sidebar__item" exact-active-class="router-link-active">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Dashboard
            </RouterLink>
            <RouterLink to="/admin/spaces" class="lf-sidebar__item">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-3"/></svg>
                Space Approvals
            </RouterLink>
        </nav>

        <div class="mt-auto px-2 pb-3 pt-3 d-flex flex-column gap-1" style="border-top: 1px solid #D4CEC6;">
            <RouterLink to="/" class="lf-sidebar__item">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                Home
            </RouterLink>
            <button class="lf-sidebar__item border-0 bg-transparent w-100 text-start" @click="logout">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Sign out
            </button>
        </div>
    </aside>
</template>

<style scoped>
.lf-admin-sidebar {
    width: 240px;
    min-height: 100vh;
    background: #EEEAE3;
    border-right: 1px solid #D4CEC6;
    position: sticky;
    top: 0;
}
</style>
