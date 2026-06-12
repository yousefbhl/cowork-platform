<script setup>
import PriceTag from './ui/PriceTag.vue'

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
        class="card-hover group block overflow-hidden"
    >
        <!-- Image -->
        <div class="relative w-full aspect-video bg-bg-tertiary overflow-hidden rounded-lg">
            <img
                v-if="space.cover_photo"
                :src="`/storage/${space.cover_photo}`"
                :alt="space.title"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                loading="lazy"
            />
            <div v-else class="flex-center h-full text-4xl text-text-tertiary opacity-50">
                🏢
            </div>
        </div>

        <!-- Content -->
        <div class="p-lg mt-lg">
            <!-- Title and Rating -->
            <div class="flex-between gap-md mb-md">
                <h3 class="text-heading-sm text-text-primary font-600 truncate flex-1">
                    {{ space.title }}
                </h3>
                <div v-if="space.rating_avg" class="flex items-center gap-xs flex-shrink-0">
                    <span class="text-accent-amber">★</span>
                    <span class="text-body-sm font-500">{{ Number(space.rating_avg).toFixed(1) }}</span>
                </div>
            </div>

            <!-- Location -->
            <p class="text-body-sm text-text-secondary mb-lg">
                {{ space.city }}<span v-if="space.country">, {{ space.country }}</span>
            </p>

            <!-- Amenities -->
            <div v-if="space.amenities?.length" class="flex flex-wrap gap-xs mb-lg">
                <span
                    v-for="amenity in space.amenities.slice(0, 3)"
                    :key="amenity.id"
                    class="badge-default px-md py-xs text-label-md"
                >
                    {{ amenity.name }}
                </span>
            </div>

            <!-- Price -->
            <div v-if="formatPrice(space.workspaces)" class="pt-lg border-t border-border-light">
                <PriceTag :price="formatPrice(space.workspaces)" period="day" size="md" />
            </div>
        </div>
    </RouterLink>
</template>

<style scoped>
/* Smooth transitions handled by Tailwind classes */
</style>
