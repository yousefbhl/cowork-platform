<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth    = useAuthStore()
const router  = useRouter()
const route   = useRoute()

const form    = ref({ email: '', password: '' })
const error   = ref(null)
const loading = ref(false)

async function submit() {
    loading.value = true
    error.value   = null
    try {
        await auth.login(form.value)
        const redirect = route.query.redirect || '/'
        if (auth.isAdmin) return router.push('/admin')
        if (auth.isHost)  return router.push('/host')
        router.push(redirect)
    } catch (e) {
        error.value = e.response?.data?.message || 'Login failed'
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
        <div class="card shadow-sm p-4" style="width: 100%; max-width: 420px;">
            <h4 class="fw-semibold mb-4">Sign in</h4>

            <div v-if="error" class="alert alert-danger py-2">{{ error }}</div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input
                    v-model="form.email"
                    type="email"
                    class="form-control"
                    placeholder="you@example.com"
                />
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <input
                    v-model="form.password"
                    type="password"
                    class="form-control"
                    placeholder="••••••••"
                    @keyup.enter="submit"
                />
            </div>

            <button
                class="btn btn-primary w-100"
                :disabled="loading"
                @click="submit"
            >
                {{ loading ? 'Signing in…' : 'Sign in' }}
            </button>

            <p class="text-center text-muted mt-3 mb-0" style="font-size: 14px;">
                No account?
                <RouterLink to="/register">Create one</RouterLink>
            </p>
        </div>
    </div>
</template>
