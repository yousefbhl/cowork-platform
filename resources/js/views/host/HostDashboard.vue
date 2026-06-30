<script setup>
import { useAuthStore } from '@/stores/auth'
import HostSidebar from '@/components/HostSidebar.vue'

const auth = useAuthStore()

const firstName = (auth.user?.name || '').split(' ')[0]
const greeting = (() => {
    const h = new Date().getHours()
    if (h < 12) return 'Good morning'
    if (h < 18) return 'Good afternoon'
    return 'Good evening'
})()
</script>

<template>
    <div class="d-flex align-items-stretch bg-linen" style="min-height: 100vh;">
        <HostSidebar />

        <main class="flex-grow-1 px-4 px-lg-5 py-5" style="min-width: 0;">
            <h1 class="headline-lg mb-1" style="color: #1A1A18;">{{ greeting }}, {{ firstName }}</h1>
            <p class="mb-5" style="font-size: 15px; color: #6B6660;">Here's what's happening with your spaces today.</p>

            <!-- Quick actions -->
            <div class="row g-3" style="max-width: 760px;">
                <div class="col-12 col-md-6">
                    <RouterLink to="/host/listings" class="lf-action-card d-flex flex-column h-100 text-decoration-none">
                        <div class="lf-action-card__icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2D6A4F" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        </div>
                        <h2 class="headline-sm mt-3 mb-1" style="color: #1A1A18;">My Listings</h2>
                        <p class="mb-3" style="font-size: 14px; color: #6B6660;">View, edit, and track the performance of your workspaces.</p>
                        <span class="mt-auto d-inline-flex align-items-center gap-1 text-forest fw-semibold" style="font-size: 14px;">
                            Manage listings
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </span>
                    </RouterLink>
                </div>
                <div class="col-12 col-md-6">
                    <RouterLink to="/host/bookings" class="lf-action-card d-flex flex-column h-100 text-decoration-none">
                        <div class="lf-action-card__icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2D6A4F" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </div>
                        <h2 class="headline-sm mt-3 mb-1" style="color: #1A1A18;">Bookings</h2>
                        <p class="mb-3" style="font-size: 14px; color: #6B6660;">Confirm or cancel customer reservations across your spaces.</p>
                        <span class="mt-auto d-inline-flex align-items-center gap-1 text-forest fw-semibold" style="font-size: 14px;">
                            Manage bookings
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </span>
                    </RouterLink>
                </div>
                <div class="col-12 col-md-6">
                    <RouterLink to="/host/listings/new" class="lf-action-card lf-action-card--accent d-flex align-items-center gap-3 text-decoration-none">
                        <div class="lf-action-card__icon lf-action-card__icon--solid">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </div>
                        <div>
                            <h2 class="headline-sm mb-0" style="color: #1A1A18;">List a new space</h2>
                            <p class="mb-0" style="font-size: 13px; color: #6B6660;">Start earning from an unused workspace.</p>
                        </div>
                    </RouterLink>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
.lf-action-card {
    background: #fff;
    border: 1px solid #E4DFD7;
    border-radius: 1.125rem;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(26,26,24,.06), 0 4px 12px rgba(26,26,24,.04);
    transition: border-color .2s, box-shadow .2s, transform .2s;
}
.lf-action-card:hover {
    border-color: rgba(45,106,79,.35);
    box-shadow: 0 4px 16px rgba(26,26,24,.10);
    transform: translateY(-2px);
}
.lf-action-card__icon {
    width: 48px; height: 48px; border-radius: 12px;
    background: rgba(45,106,79,.10);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.lf-action-card__icon--solid { background: #2D6A4F; }
.lf-action-card--accent { background: #fff; }
</style>
