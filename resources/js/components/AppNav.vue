<script setup>
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const auth   = useAuthStore()
const router = useRouter()

async function logout() {
    await auth.logout()
    router.push('/login')
}
</script>

<template>
    <nav class="navbar bg-white border-bottom px-3 px-lg-4" style="min-height: 57px;">
        <div class="container-fluid">
            <RouterLink to="/" class="navbar-brand fw-bold" style="letter-spacing: -.5px;">
                CoworkPlatform
            </RouterLink>

            <div class="d-flex align-items-center gap-2 ms-auto">
                <template v-if="auth.isAuthenticated">
                    <!-- Role-specific panel links -->
                    <RouterLink
                        v-if="auth.isHost"
                        to="/host"
                        class="btn btn-sm btn-outline-primary d-none d-md-inline-flex"
                    >
                        Host panel
                    </RouterLink>
                    <RouterLink
                        v-if="auth.isAdmin"
                        to="/admin"
                        class="btn btn-sm btn-outline-danger d-none d-md-inline-flex"
                    >
                        Admin
                    </RouterLink>

                    <!-- My bookings (customers only, hide for host/admin) -->
                    <RouterLink
                        v-if="auth.isCustomer"
                        to="/bookings"
                        class="btn btn-sm btn-outline-secondary d-none d-md-inline-flex"
                    >
                        My bookings
                    </RouterLink>

                    <!-- User identity + always-visible sign-out -->
                    <div class="d-flex align-items-center gap-2 ps-2 border-start ms-1">
                        <div
                            class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-semibold flex-shrink-0"
                            style="width: 30px; height: 30px; font-size: 12px;"
                            :title="auth.user.email"
                        >
                            {{ auth.user.name.charAt(0).toUpperCase() }}
                        </div>
                        <span
                            class="text-muted d-none d-md-inline"
                            style="font-size: 13px; max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                        >
                            {{ auth.user.name }}
                        </span>
                        <button class="btn btn-sm btn-outline-secondary" @click="logout">
                            Sign out
                        </button>
                    </div>
                </template>

                <template v-else>
                    <RouterLink to="/login"    class="btn btn-sm btn-outline-secondary">Login</RouterLink>
                    <RouterLink to="/register" class="btn btn-sm btn-primary">Sign up</RouterLink>
                </template>
            </div>
        </div>
    </nav>
</template>
