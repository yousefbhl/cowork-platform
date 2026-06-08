<script setup>
import { onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useSpacesStore } from '@/stores/spaces'
import AppNav from '@/components/AppNav.vue'

const route = useRoute()
const store = useSpacesStore()

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
                        <h5 class="fw-bold mb-4">Choose a workspace</h5>

                        <div
                            v-for="workspace in store.space.workspaces"
                            :key="workspace.id"
                            class="border rounded-3 p-3 mb-3"
                        >
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="fw-semibold mb-0">{{ workspace.type?.label }}</p>
                                    <p class="text-muted mb-0" style="font-size: 13px;">
                                        Up to {{ workspace.capacity }} person{{ workspace.capacity > 1 ? 's' : '' }}
                                    </p>
                                </div>
                                <div class="text-end">
                                    <p v-if="workspace.price_daily" class="fw-bold mb-0">
                                        ${{ workspace.price_daily }}
                                        <span class="fw-normal text-muted" style="font-size: 12px;">/day</span>
                                    </p>
                                    <p v-if="workspace.price_monthly" class="text-muted mb-0" style="font-size: 12px;">
                                        ${{ workspace.price_monthly }}/mo
                                    </p>
                                </div>
                            </div>
                        </div>

                        <RouterLink
                            to="/login"
                            class="btn btn-primary w-100 mt-2"
                            style="border-radius: .75rem; padding: .75rem;"
                        >
                            Reserve this space
                        </RouterLink>

                        <p class="text-center text-muted mt-2 mb-0" style="font-size: 12px;">
                            Free cancellation · Instant confirmation
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </template>
</template>
