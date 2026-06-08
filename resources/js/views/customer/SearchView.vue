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
            <div v-for="n in 8" :key="n" class="col-12 col-sm-6 col-lg-4 col-xl-3">
                <div class="card h-100" style="border-radius: 1rem; overflow: hidden;">
                    <div class="placeholder-glow" style="aspect-ratio: 16/10; background: #f1f0ea;">
                        <span class="placeholder w-100 h-100 d-block"></span>
                    </div>
                    <div class="card-body placeholder-glow">
                        <span class="placeholder col-8 mb-2 d-block"></span>
                        <span class="placeholder col-5"></span>
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
        <div v-else class="text-center py-5 text-muted">
            <div class="mb-3" style="font-size: 3rem;">🏢</div>
            <p class="fw-medium">No spaces match your search</p>
            <p style="font-size: 14px;">Try adjusting your filters</p>
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
