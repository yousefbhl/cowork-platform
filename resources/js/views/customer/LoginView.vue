<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const form = ref({ email: '', password: '' })
const error = ref(null)
const loading = ref(false)

async function submit() {
    loading.value = true
    error.value = null
    try {
        await auth.login(form.value)
        const redirect = route.query.redirect || '/'
        if (auth.isAdmin) return router.push('/admin')
        if (auth.isHost) return router.push('/host')
        router.push(redirect)
    } catch (e) {
        error.value = e.response?.data?.message || 'Login failed'
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="min-h-screen bg-bg-primary flex-col-center px-lg py-2xl">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-3xl">
                <h1 class="text-heading-xl font-syne font-700 text-text-primary mb-md">
                    CoworkPlatform
                </h1>
                <p class="text-body-md text-text-secondary">
                    Sign in to your account
                </p>
            </div>

            <!-- Form Card -->
            <div class="card-base p-xl border border-border-light">
                <!-- Error Alert -->
                <div
                    v-if="error"
                    class="mb-lg p-lg bg-accent-red bg-opacity-20 border border-accent-red border-opacity-50 rounded-md"
                >
                    <p class="text-body-sm text-accent-red">{{ error }}</p>
                </div>

                <!-- Email Input -->
                <div class="mb-lg">
                    <label class="text-label-md text-text-primary font-600 block mb-md">Email Address</label>
                    <input
                        v-model="form.email"
                        type="email"
                        placeholder="you@example.com"
                        class="input-base"
                        @keyup.enter="submit"
                    />
                </div>

                <!-- Password Input -->
                <div class="mb-2xl">
                    <label class="text-label-md text-text-primary font-600 block mb-md">Password</label>
                    <input
                        v-model="form.password"
                        type="password"
                        placeholder="••••••••"
                        class="input-base"
                        @keyup.enter="submit"
                    />
                </div>

                <!-- Sign In Button -->
                <Button
                    variant="primary"
                    fullWidth
                    :loading="loading"
                    @click="submit"
                >
                    {{ loading ? 'Signing in...' : 'Sign In' }}
                </Button>

                <!-- Sign Up Link -->
                <p class="text-center text-body-sm text-text-secondary mt-lg">
                    Don&apos;t have an account?
                    <RouterLink to="/register" class="text-accent-violet hover:underline font-600">
                        Create one
                    </RouterLink>
                </p>
            </div>

            <!-- Host/Admin Links -->
            <div class="mt-xl text-center space-y-md">
                <p class="text-body-sm text-text-secondary">Are you a host or admin?</p>
                <div class="flex gap-md">
                    <RouterLink
                        to="/login?role=host"
                        class="flex-1 px-lg py-md text-body-sm text-text-secondary hover:text-accent-violet border border-border-light rounded-md transition-colors"
                    >
                        Host Login
                    </RouterLink>
                    <RouterLink
                        to="/login?role=admin"
                        class="flex-1 px-lg py-md text-body-sm text-accent-red hover:text-accent-red border border-border-light rounded-md transition-colors"
                    >
                        Admin Login
                    </RouterLink>
                </div>
            </div>
        </div>
    </div>
</template>
