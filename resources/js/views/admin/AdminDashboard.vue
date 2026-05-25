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
    <nav class="navbar navbar-light bg-white border-bottom px-4">
        <span class="navbar-brand fw-bold">CoworkPlatform — Admin</span>
        <div class="d-flex gap-2 align-items-center">
            <span class="text-muted" style="font-size: 14px;">{{ auth.user?.name }}</span>
            <RouterLink to="/" class="btn btn-sm btn-outline-secondary">Home</RouterLink>
            <button class="btn btn-sm btn-outline-secondary" @click="handleLogout">Logout</button>
        </div>
    </nav>

    <div class="container py-5">
        <h1 class="fw-bold">Admin Dashboard</h1>
        <p class="text-muted">Logged in as admin ✓</p>
    </div>
</template>
