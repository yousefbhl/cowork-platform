<script setup>
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import { ref } from 'vue'
import Button from './ui/Button.vue'

const auth = useAuthStore()
const router = useRouter()
const showProfileMenu = ref(false)

async function logout() {
    await auth.logout()
    router.push('/login')
}
</script>

<template>
    <nav class="sticky top-0 z-40 bg-bg-secondary border-b border-border-light">
        <div class="page-container flex-between py-lg">
            <!-- Logo -->
            <RouterLink to="/" class="text-heading-md font-syne font-700 text-text-primary hover:text-accent-violet transition-colors">
                CoworkPlatform
            </RouterLink>

            <!-- Search Bar (centered) -->
            <div class="hidden md:flex flex-1 max-w-lg mx-xl">
                <input
                    type="text"
                    placeholder="Find your workspace..."
                    class="input-base"
                />
            </div>

            <!-- Navigation Actions -->
            <div class="flex items-center gap-lg">
                <template v-if="auth.isAuthenticated">
                    <!-- Host Panel Link -->
                    <RouterLink
                        v-if="auth.isHost"
                        to="/host"
                        class="hidden md:inline-block px-lg py-md text-body-sm text-text-secondary hover:text-text-primary transition-colors"
                    >
                        Host Panel
                    </RouterLink>

                    <!-- Admin Link -->
                    <RouterLink
                        v-if="auth.isAdmin"
                        to="/admin"
                        class="hidden md:inline-block px-lg py-md text-body-sm text-accent-red hover:text-accent-red transition-colors"
                    >
                        Admin
                    </RouterLink>

                    <!-- Profile Menu -->
                    <div class="relative">
                        <button
                            @click="showProfileMenu = !showProfileMenu"
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-accent-violet text-white font-600 hover:bg-opacity-90 transition-all"
                        >
                            {{ auth.user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                        </button>

                        <!-- Dropdown Menu -->
                        <div
                            v-show="showProfileMenu"
                            @click.outside="showProfileMenu = false"
                            class="absolute right-0 mt-md bg-bg-tertiary border border-border-light rounded-lg shadow-lg min-w-48 animate-slide-in-up"
                        >
                            <div class="p-lg border-b border-border-light">
                                <p class="text-body-sm text-text-primary font-600">{{ auth.user?.name }}</p>
                                <p class="text-label-md text-text-tertiary">{{ auth.user?.email }}</p>
                            </div>

                            <ul class="py-md">
                                <li>
                                    <RouterLink
                                        to="/bookings"
                                        class="px-lg py-md text-body-sm text-text-secondary hover:text-text-primary hover:bg-bg-secondary flex items-center gap-md transition-colors"
                                        @click="showProfileMenu = false"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h18V7H7v12a2 2 0 002 2h12a2 2 0 002-2V7z" />
                                        </svg>
                                        My Bookings
                                    </RouterLink>
                                </li>
                                <li>
                                    <button
                                        @click="logout"
                                        class="w-full px-lg py-md text-body-sm text-accent-red hover:bg-bg-secondary flex items-center gap-md transition-colors text-left"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Sign Out
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </template>

                <template v-else>
                    <RouterLink
                        to="/login"
                        class="px-lg py-md text-body-sm text-text-secondary hover:text-text-primary transition-colors"
                    >
                        Login
                    </RouterLink>
                    <Button variant="primary" size="md" @click="$router.push('/register')">
                        Sign Up
                    </Button>
                </template>
            </div>
        </div>
    </nav>
</template>

<style scoped>
button:focus-visible {
    outline: 2px solid #6C63FF;
    outline-offset: 2px;
}
</style>
