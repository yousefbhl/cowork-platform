<script setup>
import { useAuthStore } from '@/stores/auth'
import AdminSidebar from '@/components/AdminSidebar.vue'

const auth = useAuthStore()
const firstName = (auth.user?.name || '').split(' ')[0]
</script>

<template>
    <div class="d-flex align-items-stretch bg-linen" style="min-height: 100vh;">
        <AdminSidebar />

        <main class="flex-grow-1 px-4 px-lg-5 py-5" style="min-width: 0;">
            <p class="label-caps mb-2" style="color: #A09890;">Platform Administration</p>
            <h1 class="headline-lg mb-1" style="color: #1A1A18;">Welcome, {{ firstName }}</h1>
            <p class="mb-5" style="font-size: 15px; color: #6B6660;">Review and manage workspace listings across the platform.</p>

            <div style="max-width: 480px;">
                <RouterLink to="/admin/spaces" class="lf-action-card d-flex flex-column text-decoration-none">
                    <div class="lf-action-card__icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2D6A4F" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-3"/></svg>
                    </div>
                    <h2 class="headline-sm mt-3 mb-1" style="color: #1A1A18;">Space Approvals</h2>
                    <p class="mb-3" style="font-size: 14px; color: #6B6660;">Review submitted spaces and publish or pause listings.</p>
                    <span class="mt-auto d-inline-flex align-items-center gap-1 text-forest fw-semibold" style="font-size: 14px;">
                        Open approvals
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </span>
                </RouterLink>
            </div>
        </main>
    </div>
</template>

<style scoped>
.lf-action-card {
    background: #fff; border: 1px solid #E4DFD7; border-radius: 1.125rem; padding: 24px;
    box-shadow: 0 1px 3px rgba(26,26,24,.06), 0 4px 12px rgba(26,26,24,.04);
    transition: border-color .2s, box-shadow .2s, transform .2s;
}
.lf-action-card:hover { border-color: rgba(45,106,79,.35); box-shadow: 0 4px 16px rgba(26,26,24,.10); transform: translateY(-2px); }
.lf-action-card__icon { width: 48px; height: 48px; border-radius: 12px; background: rgba(45,106,79,.10); display: flex; align-items: center; justify-content: center; }
</style>
