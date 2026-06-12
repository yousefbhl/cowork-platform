<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import DashboardLayout from '@/components/layouts/DashboardLayout.vue'
import Button from '@/components/ui/Button.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import PriceTag from '@/components/ui/PriceTag.vue'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const activeTab = ref('overview')
const stats = ref({
    totalBookings: 24,
    revenue: 12500,
    rating: 4.8,
    spaces: 3,
})

const sidebarItems = [
    { id: 'overview', label: 'Overview', route: '/host', icon: 'chart' },
    { id: 'listings', label: 'Listings', route: '/host/listings', icon: 'home' },
    { id: 'bookings', label: 'Bookings', route: '/host/bookings', icon: 'calendar' },
    { id: 'messages', label: 'Messages', route: '/host/messages', icon: 'mail' },
    { id: 'payouts', label: 'Payouts', route: '/host/payouts', icon: 'credit' },
    { id: 'settings', label: 'Settings', route: '/host/settings', icon: 'gear' },
]

onMounted(() => {
    const pathSegment = route.path.split('/').pop()
    if (pathSegment && pathSegment !== 'host') {
        activeTab.value = pathSegment
    }
})
</script>

<template>
    <DashboardLayout title="Host Dashboard" subtitle="Manage your spaces and bookings" :sidebarItems="sidebarItems">
        <!-- Overview Tab -->
        <div v-if="activeTab === 'overview' || route.path === '/host'">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-lg mb-3xl">
                <div class="card-base p-xl">
                    <p class="text-label-md text-text-tertiary mb-md">Total Bookings</p>
                    <p class="text-heading-lg font-600 text-text-primary">{{ stats.totalBookings }}</p>
                    <p class="text-body-sm text-accent-green mt-md">↑ 12% this month</p>
                </div>
                <div class="card-base p-xl">
                    <p class="text-label-md text-text-tertiary mb-md">Revenue</p>
                    <PriceTag :price="stats.revenue" size="lg" />
                    <p class="text-body-sm text-accent-green mt-md">↑ $2,500 this month</p>
                </div>
                <div class="card-base p-xl">
                    <p class="text-label-md text-text-tertiary mb-md">Rating</p>
                    <p class="text-heading-lg font-600 text-text-primary">{{ stats.rating }}</p>
                    <p class="text-body-sm text-text-secondary mt-md">Based on 48 reviews</p>
                </div>
                <div class="card-base p-xl">
                    <p class="text-label-md text-text-tertiary mb-md">Active Spaces</p>
                    <p class="text-heading-lg font-600 text-text-primary">{{ stats.spaces }}</p>
                    <p class="text-body-sm text-accent-blue mt-md">All verified</p>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="card-base p-xl">
                <div class="flex-between mb-lg">
                    <h2 class="text-heading-md font-600 text-text-primary">Recent Bookings</h2>
                    <Button variant="secondary" size="sm">View All</Button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-border-light">
                            <tr>
                                <th class="px-lg py-md text-left text-label-md text-text-secondary font-600">Guest</th>
                                <th class="px-lg py-md text-left text-label-md text-text-secondary font-600">Space</th>
                                <th class="px-lg py-md text-left text-label-md text-text-secondary font-600">Dates</th>
                                <th class="px-lg py-md text-left text-label-md text-text-secondary font-600">Amount</th>
                                <th class="px-lg py-md text-left text-label-md text-text-secondary font-600">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="i in 5" :key="i" class="border-b border-border-light hover:bg-bg-secondary transition-colors">
                                <td class="px-lg py-md text-body-sm text-text-primary">Guest {{ i }}</td>
                                <td class="px-lg py-md text-body-sm text-text-secondary">Office Space</td>
                                <td class="px-lg py-md text-body-sm text-text-secondary">Dec {{ i }} - Dec {{ i + 3 }}</td>
                                <td class="px-lg py-md"><PriceTag :price="500" size="sm" /></td>
                                <td class="px-lg py-md"><StatusBadge status="booked" /></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Listings Tab -->
        <div v-else-if="activeTab === 'listings'">
            <div class="flex-between mb-xl">
                <h2 class="text-heading-md font-600 text-text-primary">Your Listings</h2>
                <Button variant="primary">Add New Space</Button>
            </div>

            <div class="grid grid-cols-1 gap-lg">
                <div v-for="i in 3" :key="i" class="card-base p-lg flex-between">
                    <div class="flex-1">
                        <h3 class="text-heading-sm font-600 text-text-primary mb-md">Office Space {{ i }}</h3>
                        <p class="text-body-sm text-text-secondary mb-lg">Downtown Area • 500 sq ft</p>
                        <div class="flex gap-md">
                            <StatusBadge status="available" />
                            <span class="text-body-sm text-text-secondary">★ 4.8 (24 reviews)</span>
                        </div>
                    </div>
                    <div class="flex gap-md">
                        <Button variant="secondary" size="md">Edit</Button>
                        <Button variant="ghost" size="md">Stats</Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bookings Tab -->
        <div v-else-if="activeTab === 'bookings'">
            <h2 class="text-heading-md font-600 text-text-primary mb-xl">Manage Bookings</h2>
            <div class="card-base p-xl">
                <p class="text-text-secondary">Calendar and booking management interface</p>
            </div>
        </div>

        <!-- Messages Tab -->
        <div v-else-if="activeTab === 'messages'">
            <h2 class="text-heading-md font-600 text-text-primary mb-xl">Messages</h2>
            <div class="card-base p-xl">
                <p class="text-text-secondary">Guest messages and inquiries will appear here</p>
            </div>
        </div>

        <!-- Payouts Tab -->
        <div v-else-if="activeTab === 'payouts'">
            <h2 class="text-heading-md font-600 text-text-primary mb-xl">Payouts</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
                <div class="card-base p-xl">
                    <p class="text-label-md text-text-tertiary mb-md">Available Balance</p>
                    <PriceTag :price="3250" size="lg" />
                </div>
                <div class="card-base p-xl">
                    <p class="text-label-md text-text-tertiary mb-md">Total Earned</p>
                    <PriceTag :price="12500" size="lg" />
                </div>
            </div>
        </div>

        <!-- Settings Tab -->
        <div v-else-if="activeTab === 'settings'">
            <h2 class="text-heading-md font-600 text-text-primary mb-xl">Host Settings</h2>
            <div class="card-base p-xl max-w-2xl">
                <p class="text-text-secondary mb-lg">Update your profile and preferences</p>
                <Button variant="secondary">Edit Profile</Button>
            </div>
        </div>
    </DashboardLayout>
</template>
