<script setup>
import { onMounted } from 'vue'
import { useBookingStore } from '@/stores/booking'
import CustomerLayout from '@/components/layouts/CustomerLayout.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import Button from '@/components/ui/Button.vue'
import PriceTag from '@/components/ui/PriceTag.vue'

const store = useBookingStore()

onMounted(() => store.fetchBookings())

async function cancel(id) {
    if (confirm('Cancel this booking? The dates will be released immediately.')) {
        await store.cancelBooking(id)
    }
}

const getStatusVariant = (status) => {
    const map = {
        pending: 'pending',
        confirmed: 'booked',
        cancelled: 'cancelled',
        completed: 'completed',
    }
    return map[status] || 'pending'
}
</script>

<template>
    <CustomerLayout>
        <div class="page-container py-2xl">
            <!-- Header -->
            <div class="mb-3xl">
                <h1 class="text-heading-xl font-600 text-text-primary mb-md">My Bookings</h1>
                <p class="text-body-md text-text-secondary">Manage your workspace reservations</p>
            </div>

            <!-- Loading State -->
            <div v-if="store.loading" class="flex-col-center py-4xl">
                <div class="animate-spin">
                    <svg class="w-8 h-8 text-accent-violet" fill="none" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" opacity="0.25"></circle>
                        <path d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" fill="currentColor"></path>
                    </svg>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="!store.bookings.length" class="text-center py-4xl">
                <div class="text-6xl mb-lg">📅</div>
                <h2 class="text-heading-md text-text-primary mb-md">No bookings yet</h2>
                <p class="text-body-md text-text-secondary mb-xl">Start exploring spaces and make your first booking</p>
                <Button variant="primary" @click="$router.push('/spaces')">
                    Browse Spaces
                </Button>
            </div>

            <!-- Bookings List -->
            <div v-else class="space-y-lg">
                <div
                    v-for="booking in store.bookings"
                    :key="booking.id"
                    class="card-base p-lg border border-border-light hover:border-border-medium transition-colors"
                >
                    <div class="flex-between gap-lg flex-wrap">
                        <!-- Left Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start gap-md mb-md">
                                <div class="flex-1">
                                    <h3 class="text-heading-sm font-600 text-text-primary truncate mb-md">
                                        {{ booking.workspace?.space?.title || '—' }}
                                    </h3>
                                    <div class="space-y-xs">
                                        <p class="text-body-sm text-text-secondary">
                                            {{ booking.workspace?.type?.label }}
                                        </p>
                                        <p class="text-body-sm text-text-tertiary">
                                            {{ booking.start_date }} → {{ booking.end_date }}
                                        </p>
                                    </div>
                                </div>
                                <StatusBadge :status="getStatusVariant(booking.status)" />
                            </div>
                        </div>

                        <!-- Right Content -->
                        <div class="flex flex-col items-end gap-md">
                            <PriceTag
                                :price="booking.total_amount"
                                size="lg"
                            />
                            <Button
                                v-if="['pending', 'confirmed'].includes(booking.status)"
                                variant="ghost"
                                size="sm"
                                @click="cancel(booking.id)"
                            >
                                Cancel Booking
                            </Button>
                            <Button
                                v-else
                                variant="secondary"
                                size="sm"
                                disabled
                            >
                                {{ booking.status }}
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>
