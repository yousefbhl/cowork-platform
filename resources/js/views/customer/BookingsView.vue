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

        <div v-if="store.loading" class="text-center py-5 text-muted">
            <div class="spinner-border spinner-border-sm"></div>
        </div>

        <div v-else-if="!store.bookings.length" class="text-center py-5 text-muted">
            <div class="mb-3" style="font-size: 3rem;">📅</div>
            <p class="fw-medium mb-2">No bookings yet</p>
            <RouterLink to="/spaces" class="btn btn-primary btn-sm">Browse spaces</RouterLink>
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
