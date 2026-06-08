<script setup>
defineProps({
    space: { type: Object, required: true },
})

function formatPrice(workspaces) {
    if (!workspaces?.length) return null
    const prices = workspaces.map(w => w.price_daily).filter(Boolean)
    if (!prices.length) return null
    return Math.min(...prices)
}
</script>

<template>
    <RouterLink
        :to="`/spaces/${space.slug}`"
        class="card h-100 text-decoration-none space-card"
    >
        <!-- Photo -->
        <div class="space-card__photo">
            <img
                v-if="space.cover_photo"
                :src="`/storage/${space.cover_photo}`"
                :alt="space.title"
                class="w-100 h-100 object-fit-cover"
                loading="lazy"
            />
            <div v-else class="space-card__no-photo d-flex align-items-center justify-content-center">
                <span style="font-size: 2rem; opacity: .3;">🏢</span>
            </div>
        </div>

        <!-- Body -->
        <div class="card-body px-3 pt-3 pb-2">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                <h6 class="card-title mb-0 fw-semibold text-truncate">{{ space.title }}</h6>
                <div v-if="space.rating_avg" class="d-flex align-items-center gap-1 flex-shrink-0">
                    <span style="font-size: 13px;">⭐</span>
                    <span style="font-size: 13px; font-weight: 500;">{{ Number(space.rating_avg).toFixed(1) }}</span>
                </div>
            </div>

            <p class="text-muted mb-2" style="font-size: 13px;">
                {{ space.city }}<span v-if="space.country">, {{ space.country }}</span>
            </p>

            <div v-if="space.amenities?.length" class="d-flex flex-wrap gap-1 mb-3">
                <span
                    v-for="amenity in space.amenities.slice(0, 3)"
                    :key="amenity.id"
                    class="badge bg-light text-dark"
                    style="font-size: 11px; font-weight: 400;"
                >
                    {{ amenity.name }}
                </span>
            </div>

            <div v-if="formatPrice(space.workspaces)" class="mt-auto">
                <span class="fw-semibold" style="font-size: 15px;">${{ formatPrice(space.workspaces) }}</span>
                <span class="text-muted" style="font-size: 13px;"> / day</span>
            </div>
        </div>
    </RouterLink>
</template>

<style scoped>
.space-card {
    border: 1px solid rgba(0,0,0,.07);
    border-radius: 1rem;
    overflow: hidden;
    transition: box-shadow .2s ease, transform .2s ease;
    color: inherit;
}

.space-card:hover {
    box-shadow: 0 8px 32px rgba(0,0,0,.12);
    transform: translateY(-2px);
}

.space-card__photo {
    width: 100%;
    aspect-ratio: 16 / 10;
    background: #f1f0ea;
    overflow: hidden;
}

.space-card__no-photo {
    width: 100%;
    height: 100%;
    background: #f1f0ea;
}
</style>
