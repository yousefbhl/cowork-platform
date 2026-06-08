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
    <nav class="navbar navbar-expand-lg bg-white border-bottom px-3 px-lg-4">
        <div class="container-fluid">
            <RouterLink to="/" class="navbar-brand fw-bold" style="letter-spacing: -.5px;">
                CoworkPlatform
            </RouterLink>

            <div class="d-flex align-items-center gap-2 ms-auto">
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
                        class="btn btn-sm btn-outline-danger d-none d-md-inline-flex"
                    >
                        Admin
                    </RouterLink>

                    <div class="dropdown">
                        <button
                            class="btn btn-sm btn-light rounded-circle d-flex align-items-center justify-content-center fw-semibold"
                            style="width: 36px; height: 36px;"
                            data-bs-toggle="dropdown"
                        >
                            {{ auth.user.name.charAt(0).toUpperCase() }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li>
                                <span class="dropdown-item-text text-muted" style="font-size: 13px;">
                                    {{ auth.user.email }}
                                </span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button class="dropdown-item text-danger" @click="logout">
                                    Sign out
                                </button>
                            </li>
                        </ul>
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
