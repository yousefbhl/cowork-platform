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
    <nav class="lf-nav px-3 px-lg-4">
        <div class="d-flex align-items-center" style="height: 68px; max-width: 1280px; margin: 0 auto;">
            <RouterLink to="/" class="d-flex align-items-center gap-2 text-decoration-none">
                <span class="lf-logo-mark">C</span>
                <span class="font-display" style="font-size: 18px; color: #1A1A18;">CoworkPlatform</span>
            </RouterLink>

            <div class="d-flex align-items-center gap-3 ms-auto">
                <template v-if="auth.isAuthenticated">
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
                        class="btn btn-sm btn-outline-primary d-none d-md-inline-flex"
                    >
                        Admin
                    </RouterLink>

                    <RouterLink
                        v-if="auth.isCustomer"
                        to="/bookings"
                        class="lf-navlink d-none d-md-inline-flex text-decoration-none"
                    >
                        My bookings
                    </RouterLink>
                    <RouterLink
                        v-if="auth.isCustomer"
                        to="/wishlist"
                        class="lf-navlink d-none d-md-inline-flex align-items-center gap-1 text-decoration-none"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                        Wishlist
                    </RouterLink>

                    <div class="d-flex align-items-center gap-2 ps-3 ms-1" style="border-left: 1px solid #E4DFD7;">
                        <RouterLink
                            to="/profile"
                            class="rounded-circle d-flex align-items-center justify-content-center fw-semibold flex-shrink-0 text-decoration-none"
                            style="width: 32px; height: 32px; font-size: 13px; background: #2D6A4F; color: #fff;"
                            :title="`${auth.user.name} — Settings`"
                        >
                            {{ auth.user.name.charAt(0).toUpperCase() }}
                        </RouterLink>
                        <button class="btn btn-sm btn-outline-secondary" @click="logout">
                            Sign out
                        </button>
                    </div>
                </template>

                <template v-else>
                    <RouterLink to="/login" class="lf-navlink d-none d-sm-inline-flex text-decoration-none">Sign in</RouterLink>
                    <RouterLink to="/register" class="btn btn-sm btn-primary px-3">Sign up</RouterLink>
                </template>
            </div>
        </div>
    </nav>
</template>
