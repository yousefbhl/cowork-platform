<script setup>
import { computed } from 'vue'
import { photoUrl } from '@/utils/photoUrl'
import { useAuthStore } from '@/stores/auth'
import { useWishlistStore } from '@/stores/wishlist'

const props = defineProps({
    space: { type: Object, required: true },
})

const auth     = useAuthStore()
const wishlist = useWishlistStore()

const cheapest = computed(() => {
    const ws = (props.space.workspaces || []).filter(w => w.price_daily)
    if (!ws.length) return null
    return ws.reduce((a, b) => (Number(a.price_daily) <= Number(b.price_daily) ? a : b))
})

const priceFrom = computed(() => (cheapest.value ? Math.round(Number(cheapest.value.price_daily)) : null))
const currency  = computed(() => cheapest.value?.currency || 'MAD')
const typeLabel = computed(() => cheapest.value?.type?.label || null)
</script>

<template>
    <RouterLink :to="`/spaces/${space.slug}`" class="lf-space-card d-flex flex-column h-100 text-decoration-none">
        <!-- Photo -->
        <div class="lf-space-card__photo">
            <img
                v-if="space.cover_photo"
                :src="photoUrl(space.cover_photo)"
                :alt="space.title"
                class="w-100 h-100 object-fit-cover"
                loading="lazy"
            />
            <div v-else class="lf-space-card__no-photo d-flex align-items-center justify-content-center">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#B8B2A8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-3"/>
                </svg>
            </div>

            <!-- Workspace-type badge (top-left) -->
            <span v-if="typeLabel" class="lf-space-card__type">{{ typeLabel }}</span>

            <!-- Heart toggle (top-right) -->
            <button
                v-if="auth.isAuthenticated"
                class="lf-space-card__heart"
                :class="{ 'is-saved': wishlist.isSaved(space.id) }"
                :aria-label="wishlist.isSaved(space.id) ? 'Remove from wishlist' : 'Save to wishlist'"
                @click.prevent.stop="wishlist.toggle(space)"
            >
                <svg width="18" height="18" viewBox="0 0 24 24"
                     :fill="wishlist.isSaved(space.id) ? '#2D6A4F' : 'none'"
                     :stroke="wishlist.isSaved(space.id) ? '#2D6A4F' : '#1A1A18'"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
            </button>
        </div>

        <!-- Body -->
        <div class="p-3 d-flex flex-column flex-grow-1">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                <h3 class="headline-sm mb-0 text-truncate" style="color: #1A1A18;">{{ space.title }}</h3>
                <span v-if="space.rating_avg" class="d-flex align-items-center gap-1 flex-shrink-0">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="#D4930A" stroke="none"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <span class="mono-sm" style="color: #4A4740;">{{ Number(space.rating_avg).toFixed(1) }}</span>
                </span>
            </div>

            <p class="mb-3 d-flex align-items-center gap-1" style="font-size: 13px; color: #6B6660;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#A09890" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <span class="text-truncate">{{ space.city }}<template v-if="space.country">, {{ space.country }}</template></span>
            </p>

            <div class="mt-auto d-flex justify-content-between align-items-end gap-2 pt-3" style="border-top: 1px solid #E4DFD7;">
                <div v-if="space.amenities?.length" class="d-flex flex-wrap gap-1">
                    <span v-for="a in space.amenities.slice(0, 2)" :key="a.id" class="lf-chip">{{ a.name }}</span>
                </div>
                <span v-else></span>

                <div v-if="priceFrom" class="text-end flex-shrink-0">
                    <span class="mono-price text-forest" style="font-size: 16px;">{{ priceFrom }} {{ currency }}</span><span style="font-size: 12px; color: #A09890;">/day</span>
                </div>
            </div>
        </div>
    </RouterLink>
</template>

<style scoped>
.lf-space-card {
    background: #FFFFFF;
    border: 1px solid #E4DFD7;
    border-radius: 1.125rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(26,26,24,.06), 0 4px 12px rgba(26,26,24,.04);
    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
    color: inherit;
}
.lf-space-card:hover {
    border-color: rgba(45,106,79,.35);
    box-shadow: 0 4px 16px rgba(26,26,24,.10), 0 1px 4px rgba(26,26,24,.06);
    transform: translateY(-3px);
}
.lf-space-card__photo {
    position: relative;
    width: 100%;
    aspect-ratio: 4 / 3;
    background: #E4DFD7;
    overflow: hidden;
}
.lf-space-card__photo img { transition: transform .5s ease; }
.lf-space-card:hover .lf-space-card__photo img { transform: scale(1.05); }
.lf-space-card__no-photo { width: 100%; height: 100%; background: #EEEAE3; }

.lf-space-card__type {
    position: absolute; top: 12px; left: 12px;
    background: rgba(26,26,24,.72); color: #fff;
    font-size: 11px; font-weight: 600; letter-spacing: .02em;
    padding: 4px 9px; border-radius: 6px;
    backdrop-filter: blur(4px);
}
.lf-space-card__heart {
    position: absolute; top: 12px; right: 12px;
    width: 32px; height: 32px; border-radius: 50%;
    background: rgba(255,255,255,.85); border: 1px solid #E4DFD7;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; backdrop-filter: blur(4px);
    transition: background .15s ease, transform .1s ease;
    box-shadow: 0 1px 3px rgba(26,26,24,.12);
}
.lf-space-card__heart:hover { background: #fff; transform: scale(1.08); }
.lf-space-card__heart.is-saved { background: #fff; }
</style>
