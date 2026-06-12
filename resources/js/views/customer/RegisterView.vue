<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Button from '@/components/ui/Button.vue'

const auth = useAuthStore()
const router = useRouter()

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'customer',
})
const error = ref(null)
const loading = ref(false)

async function submit() {
    loading.value = true
    error.value = null
    try {
        await auth.register(form.value)
        router.push('/login')
    } catch (e) {
        const errors = e.response?.data?.errors
        error.value = errors ? Object.values(errors).flat().join(' ') : 'Registration failed'
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
                    Create your account
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

                <!-- Full Name Input -->
                <div class="mb-lg">
                    <label class="text-label-md text-text-primary font-600 block mb-md">Full Name</label>
                    <input v-model="form.name" type="text" placeholder="John Doe" class="input-base" />
                </div>

                <!-- Email Input -->
                <div class="mb-lg">
                    <label class="text-label-md text-text-primary font-600 block mb-md">Email Address</label>
                    <input v-model="form.email" type="email" placeholder="you@example.com" class="input-base" />
                </div>

                <!-- Password Input -->
                <div class="mb-lg">
                    <label class="text-label-md text-text-primary font-600 block mb-md">Password</label>
                    <input v-model="form.password" type="password" placeholder="••••••••" class="input-base" />
                </div>

                <!-- Confirm Password Input -->
                <div class="mb-lg">
                    <label class="text-label-md text-text-primary font-600 block mb-md">Confirm Password</label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        placeholder="••••••••"
                        class="input-base"
                    />
                </div>

                <!-- Role Selection -->
                <div class="mb-2xl">
                    <label class="text-label-md text-text-primary font-600 block mb-md">I want to</label>
                    <select v-model="form.role" class="input-base">
                        <option value="customer">Book workspaces</option>
                        <option value="host">List my space</option>
                    </select>
                </div>

                <!-- Sign Up Button -->
                <Button variant="primary" fullWidth :loading="loading" @click="submit">
                    {{ loading ? 'Creating account...' : 'Create Account' }}
                </Button>

                <!-- Sign In Link -->
                <p class="text-center text-body-sm text-text-secondary mt-lg">
                    Already have an account?
                    <RouterLink to="/login" class="text-accent-violet hover:underline font-600">
                        Sign in
                    </RouterLink>
                </p>
            </div>
        </div>
    </div>
</template>
