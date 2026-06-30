<script setup>
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useSpacesStore } from '@/stores/spaces'
import { useBookingStore } from '@/stores/booking'
import { useAuthStore } from '@/stores/auth'
import AppNav from '@/components/AppNav.vue'
import CalendarPicker from '@/components/CalendarPicker.vue'
import { photoUrl } from '@/utils/photoUrl'

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

/* ── display-only derived values (no logic change) ── */
const workspaces = computed(() => store.space?.workspaces || [])
const cheapest = computed(() => {
    const ws = workspaces.value.filter(w => w.price_daily)
    if (!ws.length) return null
    return ws.reduce((a, b) => (Number(a.price_daily) <= Number(b.price_daily) ? a : b))
})
const headlinePrice = computed(() => selectedWorkspace.value || cheapest.value)
const currency = computed(() => headlinePrice.value?.currency || 'MAD')
const maxCapacity = computed(() =>
    workspaces.value.reduce((m, w) => Math.max(m, w.capacity || 0), 0)
)
function money(v) { return Math.round(Number(v)) }
</script>

<template>
    <AppNav />

    <!-- Loading -->
    <div v-if="store.loading" class="container py-5">
        <div class="row g-2 mb-4">
            <div class="col-8"><div class="sk" style="height: 420px; border-radius: 1.125rem 0 0 1.125rem;"></div></div>
            <div class="col-4 d-flex flex-column gap-2">
                <div class="sk flex-grow-1"></div>
                <div class="sk flex-grow-1"></div>
            </div>
        </div>
        <div class="sk mb-2" style="height: 36px; width: 40%;"></div>
        <div class="sk" style="height: 16px; width: 25%;"></div>
    </div>

    <template v-else-if="store.space">
        <div style="max-width: 1280px; margin: 0 auto;" class="px-3 px-lg-4 pt-4">

            <!-- Photo gallery -->
            <div v-if="store.space.photos?.length" class="lf-gallery mb-4">
                <div class="lf-gallery__main">
                    <img :src="photoUrl(store.space.photos[0].path)" :alt="store.space.title" class="w-100 h-100 object-fit-cover" />
                </div>
                <div class="lf-gallery__grid">
                    <div v-for="(photo, i) in store.space.photos.slice(1, 5)" :key="photo.id" class="lf-gallery__cell">
                        <img :src="photoUrl(photo.path)" :alt="store.space.title" class="w-100 h-100 object-fit-cover" />
                        <div v-if="i === 3 && store.space.photos.length > 5" class="lf-gallery__more">
                            +{{ store.space.photos.length - 5 }} photos
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="lf-gallery__empty mb-4">
                <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#B8B2A8" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-3"/></svg>
            </div>

            <div class="row g-5 pb-5">

                <!-- Left: info -->
                <div class="col-12 col-lg-7">
                    <h1 class="headline-lg mb-2" style="color: #1A1A18;">{{ store.space.title }}</h1>
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-4" style="font-size: 14px; color: #6B6660;">
                        <span class="d-flex align-items-center gap-1">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#6B6660" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            {{ store.space.city }}<template v-if="store.space.country">, {{ store.space.country }}</template>
                        </span>
                        <span v-if="store.space.rating_avg" style="color: #D4CEC6;">·</span>
                        <span v-if="store.space.rating_avg" class="d-flex align-items-center gap-1">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="#D4930A" stroke="none"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            <span class="fw-semibold" style="color: #1A1A18;">{{ Number(store.space.rating_avg).toFixed(1) }}</span>
                            <span>({{ store.space.reviews_count }} reviews)</span>
                        </span>
                    </div>

                    <hr class="lf-divider" />

                    <!-- Host -->
                    <div v-if="store.space.host" class="d-flex align-items-center gap-3 my-4">
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 48px; height: 48px; font-size: 18px; background: #1B4332; color: #fff;">
                            {{ store.space.host.name.charAt(0) }}
                        </div>
                        <div>
                            <p class="fw-semibold mb-0" style="color: #1A1A18;">Hosted by {{ store.space.host.name }}</p>
                            <p class="mb-0" style="font-size: 13px; color: #6B6660;">Space owner</p>
                        </div>
                    </div>

                    <hr class="lf-divider" />

                    <!-- Stat tiles -->
                    <div class="row g-3 my-4">
                        <div v-if="maxCapacity" class="col-6 col-md-3">
                            <div class="lf-stat">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#52796F" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                <p class="lf-stat__label">Capacity</p>
                                <p class="lf-stat__value">Up to {{ maxCapacity }}</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="lf-stat">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#52796F" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                                <p class="lf-stat__label">Workspaces</p>
                                <p class="lf-stat__value">{{ workspaces.length }} type{{ workspaces.length === 1 ? '' : 's' }}</p>
                            </div>
                        </div>
                        <div v-if="store.space.rating_avg" class="col-6 col-md-3">
                            <div class="lf-stat">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="#D4930A" stroke="none"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                <p class="lf-stat__label">Rating</p>
                                <p class="lf-stat__value">{{ Number(store.space.rating_avg).toFixed(1) }}</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="lf-stat">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#52796F" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                <p class="lf-stat__label">Reviews</p>
                                <p class="lf-stat__value">{{ store.space.reviews_count }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- About -->
                    <div v-if="store.space.description" class="my-4">
                        <h2 class="headline-sm mb-3" style="color: #1A1A18;">About this space</h2>
                        <p style="line-height: 1.8; color: #4A4740;">{{ store.space.description }}</p>
                    </div>

                    <hr class="lf-divider" />

                    <!-- Amenities -->
                    <div v-if="store.space.amenities?.length" class="my-4">
                        <h2 class="headline-sm mb-3" style="color: #1A1A18;">What this place offers</h2>
                        <div class="row g-3">
                            <div v-for="amenity in store.space.amenities" :key="amenity.id" class="col-6 col-md-6 d-flex align-items-center gap-2" style="font-size: 15px; color: #1A1A18;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2D6A4F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                {{ amenity.name }}
                            </div>
                        </div>
                    </div>

                    <hr class="lf-divider" />

                    <!-- Location -->
                    <div class="my-4">
                        <h2 class="headline-sm mb-3" style="color: #1A1A18;">Where you'll be</h2>
                        <div class="lf-map-placeholder">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#A09890" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>
                            <p class="mb-0 mt-2" style="font-size: 14px; color: #6B6660;">
                                {{ store.space.address || store.space.city }}<template v-if="store.space.country">, {{ store.space.country }}</template>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right: reserve box -->
                <div class="col-12 col-lg-5">
                    <div class="lf-reserve sticky-top">
                        <div class="d-flex align-items-baseline justify-content-between mb-3">
                            <div>
                                <p class="label-caps mb-1" style="color: #A09890;">Price</p>
                                <p class="mb-0">
                                    <span class="mono-price text-forest" style="font-size: 22px;" v-if="headlinePrice">{{ money(headlinePrice.price_daily) }} {{ currency }}</span>
                                    <span style="font-size: 13px; color: #A09890;"> / day</span>
                                </p>
                            </div>
                        </div>

                        <!-- Workspace selection -->
                        <label class="label-caps d-block mb-2" style="color: #6B6660;">Workspace type</label>
                        <div class="d-flex flex-column gap-2 mb-1">
                            <button
                                v-for="workspace in workspaces"
                                :key="workspace.id"
                                type="button"
                                class="lf-ws-option"
                                :class="{ active: selectedWorkspace?.id === workspace.id }"
                                @click="selectWorkspace(workspace)"
                            >
                                <span class="lf-ws-radio" :class="{ checked: selectedWorkspace?.id === workspace.id }"></span>
                                <span class="flex-grow-1 text-start">
                                    <span class="d-block fw-semibold" style="font-size: 14px; color: #1A1A18;">{{ workspace.type?.label }}</span>
                                    <span class="d-block" style="font-size: 12px; color: #6B6660;">Up to {{ workspace.capacity }} {{ workspace.capacity > 1 ? 'people' : 'person' }}</span>
                                </span>
                                <span v-if="workspace.price_daily" class="mono-price flex-shrink-0" style="font-size: 14px; color: #1A1A18;">
                                    {{ money(workspace.price_daily) }} {{ workspace.currency || 'MAD' }}<span style="font-size: 11px; color: #A09890;">/day</span>
                                </span>
                            </button>
                        </div>

                        <!-- Calendar -->
                        <div v-if="selectedWorkspace" class="mt-3">
                            <label class="label-caps d-block mb-2" style="color: #6B6660;">Select dates</label>
                            <div class="lf-cal-wrap">
                                <CalendarPicker :workspace-id="selectedWorkspace.id" @range-selected="onRangeSelected" />
                            </div>
                        </div>

                        <!-- Price breakdown -->
                        <div v-if="range.start_date && range.end_date" class="lf-breakdown mt-3">
                            <div class="lf-breakdown__row">
                                <span class="label">{{ money(selectedWorkspace.price_daily) }} {{ currency }} × {{ nights }} day{{ nights > 1 ? 's' : '' }}</span>
                                <span class="value">{{ money(estimatedTotal) }} {{ currency }}</span>
                            </div>
                            <div class="lf-breakdown__row lf-breakdown__total">
                                <span class="label">Total</span>
                                <span class="value">{{ money(estimatedTotal) }} {{ currency }}</span>
                            </div>
                        </div>

                        <!-- CTA -->
                        <button
                            v-if="auth.isAuthenticated"
                            class="btn btn-primary w-100 mt-3 py-2"
                            :disabled="!canBook || bookingStore.submitting"
                            @click="submitBooking"
                        >
                            {{ bookingStore.submitting ? 'Reserving…' : 'Reserve now' }}
                        </button>
                        <RouterLink v-else to="/login" class="btn btn-primary w-100 mt-3 py-2">
                            Sign in to book
                        </RouterLink>

                        <p v-if="bookingError" class="text-center mt-2 mb-0" style="font-size: 13px; color: #C0392B;">{{ bookingError }}</p>
                        <p v-else class="text-center mt-2 mb-0" style="font-size: 12px; color: #A09890;">You won't be charged yet · Free cancellation</p>
                    </div>
                </div>

            </div>
        </div>
    </template>

    <div v-else class="container py-5 text-center" style="color: #6B6660;">
        <div class="d-flex align-items-center justify-content-center rounded-circle mx-auto mb-3" style="width: 80px; height: 80px; background: #EEEAE3;">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#707973" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        </div>
        <p class="fw-semibold mb-3" style="color: #1A1A18;">Space not found</p>
        <RouterLink to="/spaces" class="btn btn-primary">Back to search</RouterLink>
    </div>
</template>

<style scoped>
.lf-gallery { display: grid; grid-template-columns: 2fr 1fr; gap: 8px; height: 420px; border-radius: 1.125rem; overflow: hidden; }
.lf-gallery__main { height: 100%; background: #E4DFD7; overflow: hidden; }
.lf-gallery__grid { display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; gap: 8px; }
.lf-gallery__cell { position: relative; background: #E4DFD7; overflow: hidden; }
.lf-gallery__more { position: absolute; inset: 0; background: rgba(26,26,24,.55); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 15px; }
.lf-gallery__empty { height: 360px; border-radius: 1.125rem; background: #EEEAE3; display: flex; align-items: center; justify-content: center; }
@media (max-width: 767px) {
    .lf-gallery { grid-template-columns: 1fr; height: auto; }
    .lf-gallery__main { height: 240px; }
    .lf-gallery__grid { grid-template-rows: 120px; }
}

.lf-stat { background: #fff; border: 1px solid #E4DFD7; border-radius: 12px; padding: 14px 16px; height: 100%; }
.lf-stat__label { font-size: 11px; color: #6B6660; margin: 8px 0 2px; }
.lf-stat__value { font-size: 14px; font-weight: 600; color: #1A1A18; margin: 0; }

.lf-map-placeholder { background: #EEEAE3; border: 1px solid #E4DFD7; border-radius: 14px; height: 240px; display: flex; flex-direction: column; align-items: center; justify-content: center; }

.lf-reserve { top: 88px; background: #fff; border: 1px solid #E4DFD7; border-radius: 1.125rem; padding: 24px; box-shadow: 0 1px 3px rgba(26,26,24,.06), 0 4px 12px rgba(26,26,24,.04); }
.lf-ws-option {
    display: flex; align-items: center; gap: 12px;
    background: #fff; border: 1px solid #E4DFD7; border-radius: 12px; padding: 12px 14px;
    cursor: pointer; transition: border-color .15s, background .15s;
}
.lf-ws-option:hover { border-color: #B8B2A8; }
.lf-ws-option.active { border-color: #2D6A4F; background: rgba(45,106,79,.04); box-shadow: 0 0 0 1px #2D6A4F inset; }
.lf-ws-radio { width: 18px; height: 18px; border-radius: 50%; border: 1.5px solid #D4CEC6; flex-shrink: 0; position: relative; transition: border-color .15s; }
.lf-ws-radio.checked { border-color: #2D6A4F; }
.lf-ws-radio.checked::after { content: ''; position: absolute; inset: 3px; border-radius: 50%; background: #2D6A4F; }
.lf-cal-wrap { background: #fff; border: 1px solid #E4DFD7; border-radius: 14px; padding: 16px; }
</style>
