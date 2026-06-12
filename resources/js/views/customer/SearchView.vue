<script setup>
import { onMounted, ref } from 'vue'
import { useSpaceStore } from '@/stores/spaces'
import CustomerLayout from '@/components/layouts/CustomerLayout.vue'
import SpaceCard from '@/components/SpaceCard.vue'
import Button from '@/components/ui/Button.vue'

const spaceStore = useSpaceStore()
const showFilters = ref(true)
const priceRange = ref({ min: 0, max: 500 })
const selectedAmenities = ref([])

onMounted(() => spaceStore.fetchSpaces())

function onFilter() {
    // Apply filter logic
    spaceStore.fetchSpaces({
        price_min: priceRange.value.min,
        price_max: priceRange.value.max,
    })
}
</script>

<template>
    <CustomerLayout>
        <div class="bg-bg-primary min-h-screen flex">
            <!-- Sidebar Filters -->
            <aside v-show="showFilters" class="w-64 bg-bg-secondary border-r border-border-light p-lg">
                <div class="mb-2xl">
                    <h3 class="text-heading-sm font-600 text-text-primary mb-lg">Filters</h3>

                    <!-- Price Range -->
                    <div class="mb-xl pb-xl border-b border-border-light">
                        <label class="text-label-md text-text-primary mb-md block">Price Range</label>
                        <div class="flex gap-md">
                            <input
                                v-model="priceRange.min"
                                type="number"
                                placeholder="Min"
                                class="input-base flex-1 py-md px-md"
                            />
                            <input
                                v-model="priceRange.max"
                                type="number"
                                placeholder="Max"
                                class="input-base flex-1 py-md px-md"
                            />
                        </div>
                    </div>

                    <!-- Amenities -->
                    <div class="mb-xl pb-xl border-b border-border-light">
                        <label class="text-label-md text-text-primary mb-md block">Amenities</label>
                        <div class="space-y-md">
                            <label v-for="amenity in ['WiFi', 'Coffee', 'Parking', 'Meeting Rooms', 'Kitchen']" :key="amenity" class="flex items-center gap-md cursor-pointer">
                                <input type="checkbox" class="w-4 h-4 rounded" />
                                <span class="text-body-sm text-text-secondary">{{ amenity }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Availability -->
                    <div>
                        <label class="text-label-md text-text-primary mb-md block">Availability</label>
                        <div class="space-y-md">
                            <label v-for="option in ['Available Now', 'Available This Week', 'Available This Month']" :key="option" class="flex items-center gap-md cursor-pointer">
                                <input type="radio" name="availability" class="w-4 h-4" />
                                <span class="text-body-sm text-text-secondary">{{ option }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <Button variant="primary" fullWidth @click="onFilter">Apply Filters</Button>
            </aside>

            <!-- Main Content -->
            <main class="flex-1">
                <!-- Header -->
                <div class="bg-bg-secondary border-b border-border-light p-xl sticky top-16 z-10">
                    <div class="page-container flex-between">
                        <div>
                            <h1 class="text-heading-lg font-600 text-text-primary">Available Spaces</h1>
                            <p class="text-body-sm text-text-secondary mt-xs">
                                {{ spaceStore.spaces.length }} workspace{{ spaceStore.spaces.length !== 1 ? 's' : '' }} available
                            </p>
                        </div>
                        <button
                            @click="showFilters = !showFilters"
                            class="md:hidden px-lg py-md bg-bg-tertiary border border-border-light rounded-md text-body-sm text-text-primary hover:bg-border-light transition-colors"
                        >
                            {{ showFilters ? 'Hide' : 'Show' }} Filters
                        </button>
                    </div>
                </div>

                <!-- Results Grid -->
                <div class="p-xl">
                    <div v-if="spaceStore.loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-xl">
                        <div v-for="i in 6" :key="i" class="card-base h-96 animate-pulse"></div>
                    </div>

                    <div v-else-if="spaceStore.spaces.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-xl">
                        <SpaceCard v-for="space in spaceStore.spaces" :key="space.id" :space="space" />
                    </div>

                    <div v-else class="text-center py-4xl">
                        <div class="text-6xl mb-lg">🏢</div>
                        <h2 class="text-heading-md text-text-primary mb-md">No spaces found</h2>
                        <p class="text-body-md text-text-secondary">Try adjusting your filters or search criteria</p>
                    </div>
                </div>
            </main>
        </div>
    </CustomerLayout>
</template>
