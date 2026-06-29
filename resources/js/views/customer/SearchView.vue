<script setup>
import { onMounted } from 'vue'
import { useSpacesStore } from '@/stores/spaces'
import SpaceCard from '@/components/SpaceCard.vue'
import FilterBar from '@/components/FilterBar.vue'
import AppNav from '@/components/AppNav.vue'

const store = useSpacesStore()

onMounted(() => store.fetchSpaces())

function onFilter(params) {
    store.fetchSpaces(params)
}
</script>

<template>
    <AppNav />
    <FilterBar @filter="onFilter" />

    <div class="container py-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 fw-bold mb-0">Workspaces</h1>
                <p class="text-muted mb-0" style="font-size: 14px;">
                    {{ store.meta.total ?? store.spaces.length }} spaces found
                </p>
            </div>
        </div>

        <!-- Loading skeleton -->
        <div v-if="store.loading" class="row g-3">
            <div v-for="n in 6" :key="n" class="col-12 col-sm-6 col-lg-4 col-xl-3">
                <div class="card border-0 overflow-hidden" style="border-radius: 1rem;">
                    <div class="sk" style="aspect-ratio: 16/10;"></div>
                    <div class="card-body px-3 pt-3 pb-3">
                        <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                            <div class="sk" style="height: 14px; width: 63%;"></div>
                            <div class="sk" style="height: 14px; width: 20%; border-radius: 12px;"></div>
                        </div>
                        <div class="sk mb-3" style="height: 12px; width: 42%;"></div>
                        <div class="d-flex gap-1 mb-3">
                            <div class="sk" style="height: 20px; width: 58px; border-radius: 10px;"></div>
                            <div class="sk" style="height: 20px; width: 70px; border-radius: 10px;"></div>
                            <div class="sk" style="height: 20px; width: 50px; border-radius: 10px;"></div>
                        </div>
                        <div class="sk" style="height: 14px; width: 34%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid -->
        <div v-else-if="store.spaces.length" class="row g-3 g-lg-4">
            <div
                v-for="space in store.spaces"
                :key="space.id"
                class="col-12 col-sm-6 col-lg-4 col-xl-3"
            >
                <SpaceCard :space="space" />
            </div>
        </div>

        <!-- Empty state -->
        <div v-else class="text-center py-5 d-flex flex-column align-items-center justify-content-center" style="min-height: 320px;">
            <div
                class="d-flex align-items-center justify-content-center rounded-circle mb-4"
                style="width: 88px; height: 88px; background: #EEEAE3;"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#707973" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                    <line x1="8" y1="11" x2="14" y2="11"/>
                </svg>
            </div>
            <h2 class="fw-semibold mb-2" style="font-size: 20px; color: #1A1A18;">No spaces found for these filters</h2>
            <p class="mb-4" style="font-size: 15px; color: #6B6660; max-width: 340px;">
                Try adjusting your filters or searching in a different city.
            </p>
            <button
                class="btn px-5 py-2"
                style="background: #1B4332; color: #fff; border-radius: 9999px; font-size: 15px; font-weight: 600; border: none;"
                @click="store.fetchSpaces()"
            >
                Clear filters
            </button>
        </div>

        <!-- Pagination -->
        <div v-if="store.meta.last_page > 1" class="d-flex justify-content-center mt-5">
            <nav>
                <ul class="pagination">
                    <li
                        v-for="page in store.meta.last_page"
                        :key="page"
                        class="page-item"
                        :class="{ active: page === store.meta.current_page }"
                    >
                        <button class="page-link" @click="store.fetchSpaces({ page })">
                            {{ page }}
                        </button>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</template>
