<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useSpaceStore } from '@/stores/spaces'
import CustomerLayout from '@/components/layouts/CustomerLayout.vue'
import SpaceCard from '@/components/SpaceCard.vue'
import Button from '@/components/ui/Button.vue'

const router = useRouter()
const spaceStore = useSpaceStore()
const searchQuery = ref('')
const loading = ref(true)

const cities = [
    { name: 'New York', count: 245 },
    { name: 'San Francisco', count: 189 },
    { name: 'London', count: 156 },
    { name: 'Berlin', count: 134 },
    { name: 'Tokyo', count: 198 },
    { name: 'Singapore', count: 76 },
]

onMounted(async () => {
    await spaceStore.fetchSpaces()
    loading.value = false
})

const handleSearch = () => {
    if (searchQuery.value.trim()) {
        router.push(`/spaces?q=${encodeURIComponent(searchQuery.value)}`)
    }
}

const topSpaces = ref([])
</script>

<template>
    <CustomerLayout>
        <!-- Hero Section -->
        <section class="bg-gradient-to-br from-bg-secondary to-bg-tertiary py-5xl px-lg">
            <div class="page-container">
                <div class="max-w-2xl mx-auto text-center">
                    <h1 class="text-display-sm md:text-display-md font-syne font-700 text-text-primary mb-lg leading-tight">
                        Find Your Space.<br />Own Your Day.
                    </h1>
                    <p class="text-body-lg text-text-secondary mb-3xl">
                        Discover the perfect coworking space, meeting room, or office for your next project
                    </p>

                    <!-- Search Bar -->
                    <div class="flex gap-md max-w-lg mx-auto">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search by location or space name..."
                            class="input-base flex-1"
                            @keyup.enter="handleSearch"
                        />
                        <Button variant="primary" @click="handleSearch">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </Button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Spaces Section -->
        <section class="py-4xl px-lg">
            <div class="page-container">
                <div class="mb-3xl">
                    <h2 class="text-heading-xl font-600 text-text-primary mb-md">Featured Spaces</h2>
                    <p class="text-body-md text-text-secondary">Curated selection of top-rated coworking spaces</p>
                </div>

                <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-xl">
                    <div v-for="i in 6" :key="i" class="card-base h-96 animate-pulse"></div>
                </div>

                <div v-else-if="spaceStore.spaces.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-xl">
                    <SpaceCard v-for="space in spaceStore.spaces.slice(0, 6)" :key="space.id" :space="space" />
                </div>

                <div v-else class="text-center py-3xl">
                    <p class="text-body-lg text-text-secondary mb-lg">No spaces found. Check back soon!</p>
                    <Button variant="secondary" @click="$router.push('/spaces')">
                        Browse All Spaces
                    </Button>
                </div>
            </div>
        </section>

        <!-- Cities Section -->
        <section class="py-4xl px-lg bg-bg-secondary">
            <div class="page-container">
                <div class="mb-3xl">
                    <h2 class="text-heading-xl font-600 text-text-primary mb-md">Explore by City</h2>
                    <p class="text-body-md text-text-secondary">Find coworking spaces in your favorite cities</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-lg">
                    <button
                        v-for="city in cities"
                        :key="city.name"
                        @click="$router.push(`/spaces?city=${city.name}`)"
                        class="group card-hover p-xl text-left"
                    >
                        <div class="flex-between mb-md">
                            <h3 class="text-heading-sm font-600 text-text-primary group-hover:text-accent-violet transition-colors">
                                {{ city.name }}
                            </h3>
                            <svg class="w-5 h-5 text-text-tertiary group-hover:text-accent-violet group-hover:translate-x-md transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </div>
                        <p class="text-body-sm text-text-secondary">{{ city.count }} spaces available</p>
                    </button>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-4xl px-lg">
            <div class="page-container max-w-2xl mx-auto text-center">
                <h2 class="text-heading-xl font-600 text-text-primary mb-lg">Ready to become a host?</h2>
                <p class="text-body-lg text-text-secondary mb-3xl">
                    List your workspace and start earning. Join thousands of hosts already on CoworkPlatform.
                </p>
                <Button variant="primary" @click="$router.push('/register')">
                    Get Started as a Host
                </Button>
            </div>
        </section>
    </CustomerLayout>
</template>
