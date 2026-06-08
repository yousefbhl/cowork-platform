<script setup>
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useSpacesStore } from '@/stores/spaces'
import { useBookingStore } from '@/stores/booking'
import { useAuthStore } from '@/stores/auth'
import AppNav from '@/components/AppNav.vue'
import CalendarPicker from '@/components/CalendarPicker.vue'

const route        = useRoute()
const router       = useRouter()
const store        = useSpacesStore()
const bookingStore = useBookingStore()
const auth         = useAuthStore()

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

onMounted(() => store.fetchSpace(route.params.slug))
</script>

<template>
    <AppNav />

    <div v-if="store.loading" class="container py-5 text-center text-muted">
        <div class="spinner-border spinner-border-sm me-2"></div>
        Loading space…
    </div>

    <template v-else-if="store.space">

        <!-- Hero photo gallery -->
        <div class="container py-3">
            <div
                v-if="store.space.photos?.length"
                class="row g-2"
                style="max-height: 480px; overflow: hidden; border-radius: 1.25rem;"
            >
                <div class="col-8">
                    <img
                        :src="`/storage/${store.space.photos[0].path}`"
                        class="w-100 h-100 object-fit-cover"
                        style="border-radius: 1rem 0 0 1rem;"
                        :alt="store.space.title"
                    />
                </div>
                <div class="col-4 d-flex flex-column gap-2">
                    <img
                        v-for="photo in store.space.photos.slice(1, 3)"
                        :key="photo.id"
                        :src="`/storage/${photo.path}`"
                        class="w-100 object-fit-cover flex-grow-1"
                        :alt="store.space.title"
                    />
                </div>
            </div>
            <div
                v-else
                class="bg-light d-flex align-items-center justify-content-center"
                style="height: 360px; border-radius: 1.25rem;"
            >
                <span style="font-size: 4rem; opacity: .2;">🏢</span>
            </div>
        </div>

        <!-- Content -->
        <div class="container py-4">
            <div class="row g-5">

                <!-- Left: info -->
                <div class="col-12 col-lg-7">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h1 class="h3 fw-bold mb-0">{{ store.space.title }}</h1>
                        <div v-if="store.space.rating_avg" class="d-flex align-items-center gap-1 flex-shrink-0">
                            <span>⭐</span>
                            <span class="fw-semibold">{{ Number(store.space.rating_avg).toFixed(1) }}</span>
                            <span class="text-muted" style="font-size: 13px;">({{ store.space.reviews_count }})</span>
                        </div>
                    </div>

                    <p class="text-muted mb-4">
                        {{ store.space.city }}<span v-if="store.space.country">, {{ store.space.country }}</span>
                        <span v-if="store.space.address"> · {{ store.space.address }}</span>
                    </p>

                    <hr />

                    <p v-if="store.space.description" class="my-4" style="line-height: 1.8;">
                        {{ store.space.description }}
                    </p>

                    <hr />

                    <!-- Amenities -->
                    <div v-if="store.space.amenities?.length" class="my-4">
                        <h5 class="fw-semibold mb-3">Amenities</h5>
                        <div class="row g-2">
                            <div
                                v-for="amenity in store.space.amenities"
                                :key="amenity.id"
                                class="col-6 col-md-4 d-flex align-items-center gap-2"
                                style="font-size: 14px;"
                            >
                                <span class="text-success">✓</span>
                                {{ amenity.name }}
                            </div>
                        </div>
                    </div>

                    <hr />

                    <!-- Host -->
                    <div v-if="store.space.host" class="my-4">
                        <h5 class="fw-semibold mb-3">Hosted by</h5>
                        <div class="d-flex align-items-center gap-3">
                            <div
                                class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                style="width: 48px; height: 48px; font-size: 18px;"
                            >
                                {{ store.space.host.name.charAt(0) }}
                            </div>
                            <div>
                                <p class="fw-semibold mb-0">{{ store.space.host.name }}</p>
                                <p class="text-muted mb-0" style="font-size: 13px;">Space owner</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: booking card -->
                <div class="col-12 col-lg-5">
                    <div
                        class="card border-0 shadow p-4 sticky-top"
                        style="top: 80px; border-radius: 1.25rem;"
                    >
                        <h5 class="fw-bold mb-4">Book this space</h5>

                        <!-- Workspace selection -->
                        <label class="form-label small fw-semibold mb-2">Workspace type</label>
                        <div
                            v-for="workspace in store.space.workspaces"
                            :key="workspace.id"
                            class="border rounded-3 p-3 mb-2"
                            :class="{ 'border-dark border-2': selectedWorkspace?.id === workspace.id }"
                            style="cursor: pointer; transition: border-color .15s;"
                            @click="selectWorkspace(workspace)"
                        >
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="fw-semibold mb-0" style="font-size: 14px;">{{ workspace.type?.label }}</p>
                                    <p class="text-muted mb-0" style="font-size: 12px;">
                                        Up to {{ workspace.capacity }} person{{ workspace.capacity > 1 ? 's' : '' }}
                                    </p>
                                </div>
                                <p v-if="workspace.price_daily" class="fw-bold mb-0" style="font-size: 14px;">
                                    ${{ workspace.price_daily }}
                                    <span class="fw-normal text-muted" style="font-size: 11px;">/day</span>
                                </p>
                            </div>
                        </div>

                        <!-- Calendar -->
                        <div v-if="selectedWorkspace" class="mt-4">
                            <label class="form-label small fw-semibold mb-2">Select dates</label>
                            <CalendarPicker
                                :workspace-id="selectedWorkspace.id"
                                @range-selected="onRangeSelected"
                            />
                        </div>

                        <!-- Price summary -->
                        <div v-if="range.start_date && range.end_date" class="mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-between mb-1" style="font-size: 14px;">
                                <span class="text-muted">
                                    {{ nights }} day{{ nights > 1 ? 's' : '' }} × ${{ selectedWorkspace.price_daily }}
                                </span>
                                <span>${{ estimatedTotal }}</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold mt-1">
                                <span>Total</span>
                                <span>${{ estimatedTotal }}</span>
                            </div>
                        </div>

                        <!-- CTA -->
                        <button
                            v-if="auth.isAuthenticated"
                            class="btn btn-primary w-100 mt-3"
                            style="border-radius: .75rem; padding: .75rem;"
                            :disabled="!canBook || bookingStore.submitting"
                            @click="submitBooking"
                        >
                            {{ bookingStore.submitting ? 'Reserving…' : 'Reserve' }}
                        </button>
                        <RouterLink
                            v-else
                            to="/login"
                            class="btn btn-primary w-100 mt-3"
                            style="border-radius: .75rem; padding: .75rem;"
                        >
                            Sign in to book
                        </RouterLink>

                        <p v-if="bookingError" class="text-danger mt-2 mb-0" style="font-size: 13px;">
                            {{ bookingError }}
                        </p>
                        <p v-else class="text-center text-muted mt-2 mb-0" style="font-size: 12px;">
                            Free cancellation · Instant confirmation
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </template>

    <div v-else class="container py-5 text-center text-muted">
        <div class="mb-3" style="font-size: 3rem;">🔍</div>
        <p class="fw-medium">Space not found</p>
        <RouterLink to="/spaces" class="btn btn-primary btn-sm">Back to search</RouterLink>
    </div>
</template>
