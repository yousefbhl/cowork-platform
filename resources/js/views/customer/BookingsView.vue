<script setup>
import { onMounted } from 'vue'
import { useBookingStore } from '@/stores/booking'
import AppNav from '@/components/AppNav.vue'

const store = useBookingStore()

onMounted(() => store.fetchBookings())

const statusBadge = {
    pending:   'lf-badge--pending',
    confirmed: 'lf-badge--confirmed',
    cancelled: 'lf-badge--cancelled',
    completed: 'lf-badge--completed',
}

function fmtDate(d) {
    return new Date(d).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' })
}

async function cancel(id) {
    if (confirm('Cancel this booking? The dates will be released immediately.')) {
        await store.cancelBooking(id)
    }
}
</script>

<template>
    <AppNav />

    <div class="container py-5" style="max-width: 880px;">
        <h1 class="headline-lg mb-1" style="color: #1A1A18;">My Bookings</h1>
        <p class="mb-4" style="font-size: 14px; color: #6B6660;">Your reservations and their status.</p>

        <!-- Loading -->
        <div v-if="store.loading" class="d-flex flex-column gap-3">
            <div v-for="n in 3" :key="n" class="lf-card p-4">
                <div class="d-flex justify-content-between gap-3 align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="sk" style="height: 18px; width: 200px;"></div>
                            <div class="sk" style="height: 20px; width: 80px; border-radius: 6px;"></div>
                        </div>
                        <div class="sk mb-2" style="height: 13px; width: 130px;"></div>
                        <div class="sk" style="height: 13px; width: 220px;"></div>
                    </div>
                    <div class="d-flex flex-column align-items-end gap-2">
                        <div class="sk" style="height: 22px; width: 90px;"></div>
                        <div class="sk" style="height: 32px; width: 84px; border-radius: 8px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty -->
        <div v-else-if="!store.bookings.length" class="text-center py-5 d-flex flex-column align-items-center justify-content-center" style="min-height: 360px;">
            <div class="d-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 88px; height: 88px; background: #fff; border: 1px solid #E4DFD7;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#A09890" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <h2 class="headline-sm mb-2" style="color: #1A1A18;">No bookings yet</h2>
            <p class="mb-4" style="font-size: 15px; color: #6B6660;">Your upcoming reservations will appear here.</p>
            <RouterLink to="/spaces" class="btn btn-primary px-4 py-2">Find your first workspace</RouterLink>
        </div>

        <!-- List -->
        <div v-else class="d-flex flex-column gap-3">
            <div v-for="booking in store.bookings" :key="booking.id" class="lf-card p-4">
                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                            <h3 class="headline-sm mb-0" style="color: #1A1A18;">{{ booking.workspace?.space?.title ?? '—' }}</h3>
                            <span class="lf-badge" :class="statusBadge[booking.status]"><span class="dot"></span>{{ booking.status }}</span>
                        </div>
                        <p class="mb-1" style="font-size: 13px; color: #6B6660;">{{ booking.workspace?.type?.label }}</p>
                        <p class="mb-0 mono-sm" style="color: #6B6660;">{{ fmtDate(booking.start_date) }} → {{ fmtDate(booking.end_date) }}</p>
                    </div>

                    <div class="text-end flex-shrink-0">
                        <p class="mono-price mb-2" style="font-size: 18px; color: #1A1A18;">{{ Math.round(booking.total_amount) }} {{ booking.currency || 'MAD' }}</p>
                        <button
                            v-if="['pending', 'confirmed'].includes(booking.status)"
                            class="btn btn-sm btn-outline-secondary"
                            style="--bs-btn-color: #C0392B; --bs-btn-border-color: #E4DFD7; --bs-btn-hover-bg: rgba(192,57,43,.06); --bs-btn-hover-color: #C0392B; --bs-btn-hover-border-color: #C0392B;"
                            @click="cancel(booking.id)"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
