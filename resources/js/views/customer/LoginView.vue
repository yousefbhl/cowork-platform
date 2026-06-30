<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth    = useAuthStore()
const router  = useRouter()
const route   = useRoute()

const form     = ref({ email: '', password: '' })
const error    = ref(null)
const loading  = ref(false)
const showPass = ref(false)

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
    <div class="lf-auth">
        <!-- Left brand panel -->
        <aside class="lf-auth__brand d-none d-lg-flex flex-column">
            <div class="font-display" style="font-size: 20px; color: #fff;">CoworkPlatform</div>
            <div class="mt-auto">
                <h1 class="display-hero mb-3" style="color: #fff;">Your next<br />great workday</h1>
                <p style="font-size: 17px; color: rgba(255,255,255,.72); max-width: 380px; line-height: 1.6;">
                    Find curated spaces designed for focus, collaboration, and everything in between.
                </p>
            </div>
            <p class="mb-0 mt-5" style="font-size: 12px; color: rgba(255,255,255,.4);">© 2024 CoworkPlatform. All rights reserved.</p>
        </aside>

        <!-- Right form -->
        <main class="lf-auth__form">
            <div style="width: 100%; max-width: 400px;">
                <h2 class="headline-lg mb-2" style="color: #1A1A18;">Welcome back</h2>
                <p class="mb-4" style="font-size: 15px; color: #6B6660;">Enter your details to access your account.</p>

                <div v-if="error" class="alert alert-danger py-2 mb-3" style="font-size: 14px;">{{ error }}</div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <div class="position-relative">
                        <svg class="position-absolute" style="left: 14px; top: 50%; transform: translateY(-50%);" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#A09890" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/></svg>
                        <input v-model="form.email" type="email" class="form-control ps-5 py-2" placeholder="name@example.com" />
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0">Password</label>
                        <a href="#" class="text-forest fw-semibold" style="font-size: 13px;">Forgot?</a>
                    </div>
                    <div class="position-relative">
                        <svg class="position-absolute" style="left: 14px; top: 50%; transform: translateY(-50%);" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#A09890" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input v-model="form.password" :type="showPass ? 'text' : 'password'" class="form-control ps-5 pe-5 py-2" placeholder="••••••••" @keyup.enter="submit" />
                        <button type="button" class="position-absolute btn-ghost p-0" style="right: 14px; top: 50%; transform: translateY(-50%);" @click="showPass = !showPass" aria-label="Toggle password">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#A09890" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <button class="btn btn-primary w-100 py-2 mt-2" :disabled="loading" @click="submit">
                    {{ loading ? 'Signing in…' : 'Sign in' }}
                </button>

                <p class="text-center mt-4 mb-0" style="font-size: 14px; color: #6B6660;">
                    Don't have an account?
                    <RouterLink to="/register" class="text-forest fw-semibold">Sign up</RouterLink>
                </p>
            </div>
        </main>
    </div>
</template>

<style scoped>
.lf-auth { min-height: 100vh; display: grid; grid-template-columns: 1fr; }
@media (min-width: 992px) { .lf-auth { grid-template-columns: 1fr 1fr; } }
.lf-auth__brand {
    background: linear-gradient(155deg, #1A1A18 0%, #1B4332 100%);
    padding: 48px;
}
.lf-auth__form { background: #F7F4EF; display: flex; align-items: center; justify-content: center; padding: 40px 24px; }
</style>
