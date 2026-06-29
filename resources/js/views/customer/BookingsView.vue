<script setup>
import { onMounted } from 'vue'
import { useBookingStore } from '@/stores/booking'
import AppNav from '@/components/AppNav.vue'

const store = useBookingStore()

onMounted(() => store.fetchBookings())

const statusClass = {
    pending:   'bg-warning-subtle text-warning-emphasis',
    confirmed: 'bg-success-subtle text-success-emphasis',
    cancelled: 'bg-danger-subtle text-danger-emphasis',
    completed: 'bg-secondary-subtle text-secondary-emphasis',
}

async function cancel(id) {
    if (confirm('Cancel this booking? The dates will be released immediately.')) {
        await store.cancelBooking(id)
    }
}
</script>

<template>
    <AppNav />

    <div class="container py-5" style="max-width: 820px;">
        <h1 class="h4 fw-bold mb-4">My Bookings</h1>

        <div v-if="store.loading" class="d-flex flex-column gap-3">
            <div v-for="n in 3" :key="n" class="card border-0 shadow-sm p-4" style="border-radius: 1rem;">
                <div class="d-flex justify-content-between gap-3 align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="sk" style="height: 16px; width: 180px;"></div>
                            <div class="sk" style="height: 20px; width: 68px; border-radius: 20px;"></div>
                        </div>
                        <div class="sk mb-2" style="height: 13px; width: 120px;"></div>
                        <div class="sk" style="height: 13px; width: 200px;"></div>
                    </div>
                    <div class="d-flex flex-column align-items-end gap-2">
                        <div class="sk" style="height: 22px; width: 70px;"></div>
                        <div class="sk" style="height: 30px; width: 80px; border-radius: 8px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-else-if="!store.bookings.length"
            class="text-center py-5 d-flex flex-column align-items-center justify-content-center"
            style="min-height: 360px;"
        >
            <div
                class="d-flex align-items-center justify-content-center rounded-circle mb-4 shadow-sm"
                style="width: 88px; height: 88px; background: #fff; border: 1px solid #E4DFD7;"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#A09890" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <h2 class="fw-semibold mb-2" style="font-size: 22px; color: #1A1A18;">No bookings yet</h2>
            <p class="mb-4" style="font-size: 15px; color: #6B6660;">Your upcoming reservations will appear here.</p>
            <RouterLink
                to="/spaces"
                class="btn px-5 py-2"
                style="background: #2D6A4F; color: #fff; border-radius: 9999px; font-size: 15px; font-weight: 600; border: none;"
            >
                Find your first workspace
            </RouterLink>
        </div>

        <div v-else class="d-flex flex-column gap-3">
            <div
                v-for="booking in store.bookings"
                :key="booking.id"
                class="card border-0 shadow-sm p-4"
                style="border-radius: 1rem;"
            >
                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <h6 class="fw-semibold mb-0">
                                {{ booking.workspace?.space?.title ?? '—' }}
                            </h6>
                            <span
                                class="badge rounded-pill"
                                :class="statusClass[booking.status]"
                                style="font-size: 11px;"
                            >
                                {{ booking.status }}
                            </span>
                        </div>

                        <p class="text-muted mb-1" style="font-size: 13px;">
                            {{ booking.workspace?.type?.label }}
                        </p>

                        <p class="text-muted mb-0" style="font-size: 13px;">
                            📅 {{ booking.start_date }} → {{ booking.end_date }}
                        </p>
                    </div>

                    <div class="text-end flex-shrink-0">
                        <p class="fw-bold mb-2" style="font-size: 18px;">
                            ${{ booking.total_amount }}
                        </p>
                        <button
                            v-if="['pending', 'confirmed'].includes(booking.status)"
                            class="btn btn-sm btn-outline-danger"
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
