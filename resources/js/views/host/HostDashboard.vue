<script setup>
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const auth   = useAuthStore()
const router = useRouter()

async function handleLogout() {
    await auth.logout()
    router.push('/login')
}
</script>

<template>
    <nav class="navbar navbar-light bg-white border-bottom px-4" style="min-height: 57px;">
        <span class="navbar-brand fw-bold" style="letter-spacing: -.3px; color: #1B4332;">CoworkPlatform — Host</span>
        <div class="d-flex gap-2 align-items-center">
            <RouterLink
                to="/host/listings"
                class="btn btn-sm btn-outline-secondary"
                style="font-size: 13px;"
            >
                My Listings
            </RouterLink>
            <RouterLink
                to="/host/bookings"
                class="btn btn-sm btn-outline-secondary"
                style="font-size: 13px;"
            >
                Bookings
            </RouterLink>
            <span class="text-muted d-none d-md-inline" style="font-size: 13px;">{{ auth.user?.name }}</span>
            <RouterLink to="/" class="btn btn-sm btn-outline-secondary">Home</RouterLink>
            <button class="btn btn-sm btn-outline-secondary" @click="handleLogout">Logout</button>
        </div>
    </nav>

    <div style="background: #F7F4EF; min-height: calc(100vh - 57px);">
        <div class="container py-5">
            <h1 class="h4 fw-bold mb-1" style="color: #1B4332;">Host Dashboard</h1>
            <p class="text-muted mb-4">Welcome back, {{ auth.user?.name }}</p>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <RouterLink
                        to="/host/listings"
                        class="card border-0 shadow-sm text-decoration-none p-4"
                        style="border-radius: .75rem; background: #fff;"
                    >
                        <div class="fw-semibold mb-1" style="color: #1B4332; font-size: 15px;">My Listings</div>
                        <p class="text-muted mb-0" style="font-size: 13px;">View and manage your spaces</p>
                    </RouterLink>
                </div>
                <div class="col-12 col-md-6">
                    <RouterLink
                        to="/host/bookings"
                        class="card border-0 shadow-sm text-decoration-none p-4"
                        style="border-radius: .75rem; background: #fff;"
                    >
                        <div class="fw-semibold mb-1" style="color: #1B4332; font-size: 15px;">Bookings</div>
                        <p class="text-muted mb-0" style="font-size: 13px;">Confirm or cancel customer reservations</p>
                    </RouterLink>
                </div>
            </div>
        </div>
    </div>
</template>
