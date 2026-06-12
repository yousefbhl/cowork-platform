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
    totalUsers: 1240,
    totalSpaces: 456,
    totalBookings: 8234,
    platformRevenue: 125000,
})

const sidebarItems = [
    { id: 'overview', label: 'Overview', route: '/admin', icon: 'chart' },
    { id: 'users', label: 'Users', route: '/admin/users', icon: 'users' },
    { id: 'spaces', label: 'Spaces', route: '/admin/spaces', icon: 'home' },
    { id: 'bookings', label: 'Bookings', route: '/admin/bookings', icon: 'calendar' },
    { id: 'disputes', label: 'Disputes', route: '/admin/disputes', icon: 'alert' },
    { id: 'settings', label: 'Settings', route: '/admin/settings', icon: 'gear' },
]

onMounted(() => {
    const pathSegment = route.path.split('/').pop()
    if (pathSegment && pathSegment !== 'admin') {
        activeTab.value = pathSegment
    }
})
</script>

<template>
    <DashboardLayout title="Admin Dashboard" subtitle="Platform overview and management" :sidebarItems="sidebarItems">
        <!-- Overview Tab -->
        <div v-if="activeTab === 'overview' || route.path === '/admin'">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-lg mb-3xl">
                <div class="card-base p-xl">
                    <p class="text-label-md text-text-tertiary mb-md">Total Users</p>
                    <p class="text-heading-lg font-600 text-text-primary">{{ stats.totalUsers }}</p>
                    <p class="text-body-sm text-accent-green mt-md">↑ 234 this month</p>
                </div>
                <div class="card-base p-xl">
                    <p class="text-label-md text-text-tertiary mb-md">Listed Spaces</p>
                    <p class="text-heading-lg font-600 text-text-primary">{{ stats.totalSpaces }}</p>
                    <p class="text-body-sm text-accent-green mt-md">↑ 45 this month</p>
                </div>
                <div class="card-base p-xl">
                    <p class="text-label-md text-text-tertiary mb-md">Total Bookings</p>
                    <p class="text-heading-lg font-600 text-text-primary">{{ stats.totalBookings }}</p>
                    <p class="text-body-sm text-accent-green mt-md">↑ 1,234 this month</p>
                </div>
                <div class="card-base p-xl">
                    <p class="text-label-md text-text-tertiary mb-md">Platform Revenue</p>
                    <PriceTag :price="stats.platformRevenue" size="lg" />
                    <p class="text-body-sm text-accent-green mt-md">↑ $25,000 this month</p>
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
                                <th class="px-lg py-md text-left text-label-md text-text-secondary font-600">Host</th>
                                <th class="px-lg py-md text-left text-label-md text-text-secondary font-600">Amount</th>
                                <th class="px-lg py-md text-left text-label-md text-text-secondary font-600">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="i in 5" :key="i" class="border-b border-border-light hover:bg-bg-secondary transition-colors">
                                <td class="px-lg py-md text-body-sm text-text-primary">User {{ i }}</td>
                                <td class="px-lg py-md text-body-sm text-text-secondary">Space Name</td>
                                <td class="px-lg py-md text-body-sm text-text-secondary">Host {{ i }}</td>
                                <td class="px-lg py-md"><PriceTag :price="500" size="sm" /></td>
                                <td class="px-lg py-md"><StatusBadge status="booked" /></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Users Tab -->
        <div v-else-if="activeTab === 'users'">
            <div class="flex-between mb-xl">
                <h2 class="text-heading-md font-600 text-text-primary">User Management</h2>
                <Button variant="secondary">Export Users</Button>
            </div>

            <div class="card-base p-xl">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-border-light">
                            <tr>
                                <th class="px-lg py-md text-left text-label-md text-text-secondary font-600">Name</th>
                                <th class="px-lg py-md text-left text-label-md text-text-secondary font-600">Email</th>
                                <th class="px-lg py-md text-left text-label-md text-text-secondary font-600">Role</th>
                                <th class="px-lg py-md text-left text-label-md text-text-secondary font-600">Joined</th>
                                <th class="px-lg py-md text-left text-label-md text-text-secondary font-600">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="i in 10" :key="i" class="border-b border-border-light hover:bg-bg-secondary transition-colors">
                                <td class="px-lg py-md text-body-sm text-text-primary">User {{ i }}</td>
                                <td class="px-lg py-md text-body-sm text-text-secondary">user{{ i }}@example.com</td>
                                <td class="px-lg py-md"><span class="text-label-md text-text-secondary">{{ i % 2 === 0 ? 'Host' : 'Customer' }}</span></td>
                                <td class="px-lg py-md text-body-sm text-text-secondary">Dec 1, 2024</td>
                                <td class="px-lg py-md"><StatusBadge status="available" /></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Spaces Tab -->
        <div v-else-if="activeTab === 'spaces'">
            <div class="flex-between mb-xl">
                <h2 class="text-heading-md font-600 text-text-primary">Space Moderation</h2>
                <Button variant="secondary">Flagged Spaces</Button>
            </div>

            <div class="grid grid-cols-1 gap-lg">
                <div v-for="i in 3" :key="i" class="card-base p-lg flex-between">
                    <div class="flex-1">
                        <h3 class="text-heading-sm font-600 text-text-primary mb-md">Space {{ i }}</h3>
                        <p class="text-body-sm text-text-secondary mb-lg">Host: User {{ i }} • 45 bookings</p>
                        <StatusBadge status="available" />
                    </div>
                    <div class="flex gap-md">
                        <Button variant="secondary" size="md">Review</Button>
                        <Button variant="ghost" size="md">Details</Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bookings Tab -->
        <div v-else-if="activeTab === 'bookings'">
            <h2 class="text-heading-md font-600 text-text-primary mb-xl">All Bookings</h2>
            <div class="card-base p-xl">
                <p class="text-text-secondary">Full booking management and analytics interface</p>
            </div>
        </div>

        <!-- Disputes Tab -->
        <div v-else-if="activeTab === 'disputes'">
            <h2 class="text-heading-md font-600 text-text-primary mb-xl">Disputes & Reports</h2>
            <div class="card-base p-xl">
                <p class="text-text-secondary">Flagged bookings and user reports for review and moderation</p>
            </div>
        </div>

        <!-- Settings Tab -->
        <div v-else-if="activeTab === 'settings'">
            <h2 class="text-heading-md font-600 text-text-primary mb-xl">Platform Settings</h2>
            <div class="card-base p-xl max-w-2xl">
                <p class="text-text-secondary mb-lg">Configure platform-wide settings</p>
                <Button variant="secondary">Edit Settings</Button>
            </div>
        </div>
    </DashboardLayout>
</template>
