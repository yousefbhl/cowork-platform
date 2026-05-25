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
        <span class="navbar-brand fw-bold">CoworkPlatform</span>
        <div class="d-flex gap-2 align-items-center">
            <template v-if="auth.isAuthenticated">
                <span class="text-muted" style="font-size: 14px;">
                    {{ auth.user.name }} ({{ auth.user.role }})
                </span>
                <RouterLink v-if="auth.isHost"  to="/host"  class="btn btn-sm btn-outline-primary">Host panel</RouterLink>
                <RouterLink v-if="auth.isAdmin" to="/admin" class="btn btn-sm btn-outline-danger">Admin panel</RouterLink>
                <button class="btn btn-sm btn-outline-secondary" @click="handleLogout">Logout</button>
            </template>
            <template v-else>
                <RouterLink to="/login"    class="btn btn-sm btn-outline-secondary">Login</RouterLink>
                <RouterLink to="/register" class="btn btn-sm btn-primary">Sign up</RouterLink>
            </template>
        </div>
    </nav>

    <div class="container py-5">
        <h1 class="fw-bold">Find your workspace</h1>
        <p class="text-muted">Phase 2 auth working ✓</p>
    </div>
</template>
