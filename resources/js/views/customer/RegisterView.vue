<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth   = useAuthStore()
const router = useRouter()

const form = ref({
    name: '', email: '', password: '',
    password_confirmation: '', role: 'customer',
})
const error   = ref(null)
const loading = ref(false)

async function submit() {
    loading.value = true
    error.value   = null
    try {
        await auth.register(form.value)
        router.push('/login')
    } catch (e) {
        const errors = e.response?.data?.errors
        error.value  = errors
            ? Object.values(errors).flat().join(' ')
            : 'Registration failed'
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
        <div class="card shadow-sm p-4" style="width: 100%; max-width: 440px;">
            <h4 class="fw-semibold mb-4">Create account</h4>

            <div v-if="error" class="alert alert-danger py-2">{{ error }}</div>

            <div class="mb-3">
                <label class="form-label">Full name</label>
                <input v-model="form.name" type="text" class="form-control" />
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input v-model="form.email" type="email" class="form-control" />
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input v-model="form.password" type="password" class="form-control" />
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm password</label>
                <input v-model="form.password_confirmation" type="password" class="form-control" />
            </div>

            <div class="mb-4">
                <label class="form-label">I want to</label>
                <select v-model="form.role" class="form-select">
                    <option value="customer">Book workspaces</option>
                    <option value="host">List my space</option>
                </select>
            </div>

            <button
                class="btn btn-primary w-100"
                :disabled="loading"
                @click="submit"
            >
                {{ loading ? 'Creating account…' : 'Create account' }}
            </button>

            <p class="text-center text-muted mt-3 mb-0" style="font-size: 14px;">
                Already have an account?
                <RouterLink to="/login">Sign in</RouterLink>
            </p>
        </div>
    </div>
</template>
