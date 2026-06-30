<script setup>
import { onMounted } from 'vue'
import { useWishlistStore } from '@/stores/wishlist'
import AppNav from '@/components/AppNav.vue'
import SpaceCard from '@/components/SpaceCard.vue'

const store = useWishlistStore()

onMounted(() => store.fetchWishlist())
</script>

<template>
    <AppNav />

    <div class="bg-linen" style="min-height: calc(100vh - 68px);">
        <div class="container py-5">

            <!-- Header -->
            <div class="mb-4">
                <h1 class="headline-lg mb-1" style="color: #1A1A18;">Wishlist</h1>
                <p class="mb-0" style="font-size: 14px; color: #6B6660;">
                    {{ store.spaces.length }} saved space{{ store.spaces.length !== 1 ? 's' : '' }}
                </p>
            </div>

            <!-- Loading skeleton -->
            <div v-if="store.loading" class="row g-3 g-lg-4">
                <div v-for="n in 6" :key="n" class="col-12 col-sm-6 col-lg-4 col-xl-3">
                    <div class="lf-card overflow-hidden">
                        <div class="sk" style="aspect-ratio: 4/3; border-radius: 0;"></div>
                        <div class="p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                                <div class="sk" style="height: 15px; width: 60%;"></div>
                                <div class="sk" style="height: 15px; width: 18%; border-radius: 12px;"></div>
                            </div>
                            <div class="sk mb-3" style="height: 12px; width: 45%;"></div>
                            <div class="d-flex justify-content-between pt-3" style="border-top: 1px solid #E4DFD7;">
                                <div class="sk" style="height: 22px; width: 55px; border-radius: 6px;"></div>
                                <div class="sk" style="height: 16px; width: 72px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grid -->
            <div v-else-if="store.spaces.length" class="row g-3 g-lg-4">
                <div v-for="space in store.spaces" :key="space.id" class="col-12 col-sm-6 col-lg-4 col-xl-3">
                    <SpaceCard :space="space" />
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="text-center py-5 d-flex flex-column align-items-center justify-content-center" style="min-height: 380px;">
                <div class="d-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 96px; height: 96px; background: #fff; border: 1px solid #E4DFD7;">
                    <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#A09890" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                </div>
                <h2 class="headline-sm mb-2" style="color: #1A1A18;">Start saving spaces you love</h2>
                <p class="mb-4" style="font-size: 15px; color: #6B6660; max-width: 340px;">
                    Tap the heart icon on any space to save it to your wishlist for later.
                </p>
                <RouterLink to="/spaces" class="btn btn-primary px-4 py-2 d-inline-flex align-items-center gap-2">
                    Browse spaces
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </RouterLink>
            </div>

        </div>
    </div>
</template>
