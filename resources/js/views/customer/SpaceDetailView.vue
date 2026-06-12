<script setup>
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useSpaceStore } from '@/stores/spaces'
import { useBookingStore } from '@/stores/booking'
import { useAuthStore } from '@/stores/auth'
import CustomerLayout from '@/components/layouts/CustomerLayout.vue'
import CalendarPicker from '@/components/CalendarPicker.vue'
import Button from '@/components/ui/Button.vue'
import PriceTag from '@/components/ui/PriceTag.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import Card from '@/components/ui/Card.vue'

const route = useRoute()
const router = useRouter()
const spaceStore = useSpaceStore()
const bookingStore = useBookingStore()
const auth = useAuthStore()

const selectedWorkspace = ref(null)
const range             = ref({ start_date: null, end_date: null })
const bookingError      = ref(null)

const nights = computed(() => {
    if (!range.value.start_date || !range.value.end_date) return 0
    const start = new Date(range.value.start_date)
    const end   = new Date(range.value.end_date)
    return Math.round((end - start) / (1000 * 60 * 60 * 24))
})

const estimatedTotal = computed(() => {
    if (!selectedWorkspace.value?.price_daily || !nights.value) return '0.00'
    return (selectedWorkspace.value.price_daily * nights.value).toFixed(2)
})

const canBook = computed(() =>
    selectedWorkspace.value && range.value.start_date && range.value.end_date
)

function selectWorkspace(workspace) {
    selectedWorkspace.value = workspace
    range.value = { start_date: null, end_date: null }
    bookingError.value = null
}

function onRangeSelected(r) {
    range.value = r
    bookingError.value = null
}

async function submitBooking() {
    bookingError.value = null
    const result = await bookingStore.createBooking({
        space_workspace_id: selectedWorkspace.value.id,
        start_date: range.value.start_date,
        end_date:   range.value.end_date,
    })

    if (result.success) {
        router.push('/bookings')
    } else {
        bookingError.value = result.message
    }
}

onMounted(() => spaceStore.fetchSpace(route.params.slug))
</script>

<template>
    <CustomerLayout>
        <div v-if="spaceStore.loading" class="flex-col-center py-4xl">
            <div class="animate-spin">
                <svg class="w-8 h-8 text-accent-violet" fill="none" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" opacity="0.25"></circle>
                    <path d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" fill="currentColor"></path>
                </svg>
            </div>
        </div>

        <template v-else-if="spaceStore.space">

            <!-- Hero photo gallery -->
            <div class="py-xl px-lg bg-bg-secondary">
                <div class="page-container">
                    <div
                        v-if="spaceStore.space.photos?.length"
                        class="grid grid-cols-4 gap-md rounded-lg overflow-hidden"
                        style="max-height: 480px;"
                    >
                        <div class="col-span-2 row-span-2">
                            <img
                                :src="`/storage/${spaceStore.space.photos[0].path}`"
                                class="w-full h-full object-cover"
                                :alt="spaceStore.space.title"
                            />
                        </div>
                        <img
                            v-for="photo in spaceStore.space.photos.slice(1, 3)"
                            :key="photo.id"
                            :src="`/storage/${photo.path}`"
                            class="w-full h-full object-cover"
                            :alt="spaceStore.space.title"
                        />
                    </div>
                    <div
                        v-else
                        class="flex-col-center h-96 bg-bg-tertiary rounded-lg"
                    >
                        <span class="text-6xl opacity-30">🏢</span>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="page-container py-3xl">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-2xl">
                    <!-- Left: info -->
                    <div class="lg:col-span-2">
                        <!-- Title & Rating -->
                        <div class="flex-between gap-md mb-2xl">
                            <div>
                                <h1 class="text-heading-xl font-600 text-text-primary mb-md">{{ spaceStore.space.title }}</h1>
                                <p class="text-body-md text-text-secondary">
                                    {{ spaceStore.space.city }}<span v-if="spaceStore.space.country">, {{ spaceStore.space.country }}</span>
                                    <span v-if="spaceStore.space.address"> • {{ spaceStore.space.address }}</span>
                                </p>
                            </div>
                            <div v-if="spaceStore.space.rating_avg" class="text-right flex-shrink-0">
                                <p class="text-heading-md font-600 text-text-primary">{{ Number(spaceStore.space.rating_avg).toFixed(1) }}</p>
                                <p class="text-body-sm text-text-secondary">{{ spaceStore.space.reviews_count }} reviews</p>
                            </div>
                        </div>

                        <div class="divider mb-2xl"></div>

                        <!-- Description -->
                        <div v-if="spaceStore.space.description" class="mb-2xl">
                            <p class="text-body-md text-text-secondary leading-relaxed">{{ spaceStore.space.description }}</p>
                        </div>

                        <div class="divider mb-2xl"></div>

                        <!-- Amenities -->
                        <div v-if="spaceStore.space.amenities?.length" class="mb-2xl">
                            <h2 class="text-heading-md font-600 text-text-primary mb-lg">Amenities</h2>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-lg">
                                <div v-for="amenity in spaceStore.space.amenities" :key="amenity.id" class="flex items-start gap-md">
                                    <span class="text-accent-green text-lg flex-shrink-0">✓</span>
                                    <span class="text-body-sm text-text-secondary">{{ amenity.name }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="divider mb-2xl"></div>

                        <!-- Host -->
                        <div v-if="spaceStore.space.host">
                            <h2 class="text-heading-md font-600 text-text-primary mb-lg">Hosted by</h2>
                            <div class="flex items-center gap-lg">
                                <div class="w-16 h-16 rounded-full bg-accent-violet flex-center text-white font-600 text-xl flex-shrink-0">
                                    {{ spaceStore.space.host.name.charAt(0) }}
                                </div>
                                <div>
                                    <p class="text-heading-sm font-600 text-text-primary">{{ spaceStore.space.host.name }}</p>
                                    <p class="text-body-sm text-text-secondary">Space owner</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: booking card -->
                    <div class="lg:col-span-1">
                        <Card class="p-xl sticky top-32">
                            <h3 class="text-heading-md font-600 text-text-primary mb-xl">Book this space</h3>

                            <!-- Workspace selection -->
                            <div class="mb-xl">
                                <label class="text-label-md text-text-primary font-600 mb-md block">Workspace Type</label>
                                <div class="space-y-md">
                                    <button
                                        v-for="workspace in spaceStore.space.workspaces"
                                        :key="workspace.id"
                                        @click="selectWorkspace(workspace)"
                                        :class="[
                                            'w-full p-lg rounded-md border-2 transition-all text-left',
                                            selectedWorkspace?.id === workspace.id
                                                ? 'border-accent-violet bg-accent-violet bg-opacity-10'
                                                : 'border-border-light hover:border-border-medium'
                                        ]"
                                    >
                                        <p class="font-600 text-text-primary mb-xs">{{ workspace.type?.label }}</p>
                                        <p class="text-body-sm text-text-secondary mb-md">
                                            Up to {{ workspace.capacity }} person{{ workspace.capacity > 1 ? 's' : '' }}
                                        </p>
                                        <PriceTag v-if="workspace.price_daily" :price="workspace.price_daily" period="day" size="sm" />
                                    </button>
                                </div>
                            </div>

                            <!-- Calendar -->
                            <div v-if="selectedWorkspace" class="mb-xl">
                                <label class="text-label-md text-text-primary font-600 mb-md block">Select Dates</label>
                                <CalendarPicker :workspace-id="selectedWorkspace.id" @range-selected="onRangeSelected" />
                            </div>

                            <!-- Price summary -->
                            <div v-if="range.start_date && range.end_date" class="border-t border-border-light pt-xl mb-xl">
                                <div class="flex-between mb-md">
                                    <span class="text-body-sm text-text-secondary">{{ nights }} day{{ nights > 1 ? 's' : '' }} × {{ selectedWorkspace.price_daily }}</span>
                                    <span class="font-mono font-500">${{ estimatedTotal }}</span>
                                </div>
                                <div class="flex-between pt-md border-t border-border-light">
                                    <span class="font-600 text-text-primary">Total</span>
                                    <span class="font-mono font-600 text-heading-sm">${{ estimatedTotal }}</span>
                                </div>
                            </div>

                            <!-- CTA -->
                            <Button
                                v-if="auth.isAuthenticated"
                                variant="primary"
                                fullWidth
                                :disabled="!canBook || bookingStore.submitting"
                                @click="submitBooking"
                            >
                                {{ bookingStore.submitting ? 'Reserving...' : 'Reserve Now' }}
                            </Button>
                            <RouterLink
                                v-else
                                to="/login"
                                class="btn-primary inline-block w-full text-center"
                            >
                                Sign in to Book
                            </RouterLink>

                            <p v-if="bookingError" class="text-accent-red text-body-sm mt-lg">{{ bookingError }}</p>
                            <p v-else class="text-body-sm text-text-tertiary text-center mt-lg">
                                Free cancellation · Instant confirmation
                            </p>
                        </Card>
                    </div>
                </div>
            </div>
        </template>

        <div v-else class="flex-col-center py-4xl">
            <div class="text-6xl mb-lg">🔍</div>
            <h2 class="text-heading-md text-text-primary mb-lg">Space not found</h2>
            <Button variant="primary" @click="$router.push('/spaces')">Back to Search</Button>
        </div>
    </CustomerLayout>
</template>
