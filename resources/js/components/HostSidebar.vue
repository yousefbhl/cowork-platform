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
    <aside class="lf-host-sidebar d-flex flex-column flex-shrink-0">
        <!-- Brand -->
        <div class="d-flex align-items-center gap-2 px-3 pt-4 pb-3">
            <span class="lf-logo-mark">C</span>
            <div>
                <div class="font-display" style="font-size: 15px; color: #1B4332; line-height: 1.1;">Host Portal</div>
                <div style="font-size: 11px; color: #6B6660;">{{ auth.user?.name }}</div>
            </div>
        </div>

        <!-- Primary CTA -->
        <div class="px-3 pb-3">
            <RouterLink to="/host/listings/new" class="btn btn-primary w-100 btn-sm py-2 d-inline-flex align-items-center justify-content-center gap-1">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add New Space
            </RouterLink>
        </div>

        <!-- Nav -->
        <nav class="px-2 d-flex flex-column gap-1">
            <RouterLink to="/host" class="lf-sidebar__item" active-class="router-link-active" exact-active-class="router-link-active">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Dashboard
            </RouterLink>
            <RouterLink to="/host/listings" class="lf-sidebar__item">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Listings
            </RouterLink>
            <RouterLink to="/host/bookings" class="lf-sidebar__item">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Bookings
            </RouterLink>
        </nav>

        <!-- Footer -->
        <div class="mt-auto px-2 pb-3 pt-3 d-flex flex-column gap-1" style="border-top: 1px solid #D4CEC6;">
            <RouterLink to="/" class="lf-sidebar__item">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                Switch to customer
            </RouterLink>
            <button class="lf-sidebar__item border-0 bg-transparent w-100 text-start" @click="logout">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Sign out
            </button>
        </div>
    </aside>
</template>

<style scoped>
.lf-host-sidebar {
    width: 240px;
    min-height: 100vh;
    background: #EEEAE3;
    border-right: 1px solid #D4CEC6;
    position: sticky;
    top: 0;
}
</style>
