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
const error    = ref(null)
const loading  = ref(false)
const showPass = ref(false)

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
    <div class="lf-auth">
        <!-- Left brand panel -->
        <aside class="lf-auth__brand d-none d-lg-flex flex-column">
            <div class="d-flex align-items-center gap-2">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"/><path d="M2 21c0-3 1.85-5.36 5.08-6"/></svg>
                <span class="font-display" style="font-size: 20px; color: #fff;">CoworkPlatform</span>
            </div>
            <div class="mt-auto">
                <h1 class="headline-lg mb-3" style="color: #fff; font-size: 44px;">Find your focus in spaces designed for deep work.</h1>
                <p style="font-size: 16px; color: rgba(255,255,255,.72); max-width: 400px; line-height: 1.6;">
                    Join a community of professionals who value environment as much as execution.
                </p>
            </div>
            <p class="mb-0 mt-5" style="font-size: 12px; color: rgba(255,255,255,.4);">© 2024 CoworkPlatform</p>
        </aside>

        <!-- Right form -->
        <main class="lf-auth__form">
            <div style="width: 100%; max-width: 440px;">
                <h2 class="headline-md mb-1" style="color: #1A1A18;">Create an account</h2>
                <p class="mb-4" style="font-size: 14px; color: #6B6660;">
                    Already have an account?
                    <RouterLink to="/login" class="text-forest fw-semibold">Log in</RouterLink>
                </p>

                <div v-if="error" class="alert alert-danger py-2 mb-3" style="font-size: 14px;">{{ error }}</div>

                <!-- Role selector -->
                <label class="label-caps d-block mb-2" style="color: #6B6660;">How will you use CoworkPlatform?</label>
                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <button type="button" class="lf-role" :class="{ active: form.role === 'customer' }" @click="form.role = 'customer'">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1A1A18" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                            <span class="lf-role__radio" :class="{ checked: form.role === 'customer' }"></span>
                            <span class="d-block fw-semibold mt-2" style="font-size: 14px; color: #1A1A18;">I need a workspace</span>
                            <span class="d-block" style="font-size: 12px; color: #6B6660;">Book desks, meeting rooms, or private offices.</span>
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="lf-role" :class="{ active: form.role === 'host' }" @click="form.role = 'host'">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1A1A18" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            <span class="lf-role__radio" :class="{ checked: form.role === 'host' }"></span>
                            <span class="d-block fw-semibold mt-2" style="font-size: 14px; color: #1A1A18;">I have a space to list</span>
                            <span class="d-block" style="font-size: 12px; color: #6B6660;">List your unused commercial space for others.</span>
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Full name</label>
                    <input v-model="form.name" type="text" class="form-control py-2" placeholder="Jane Doe" />
                </div>
                <div class="mb-3">
                    <label class="form-label">Email address</label>
                    <input v-model="form.email" type="email" class="form-control py-2" placeholder="jane@example.com" />
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="position-relative">
                        <input v-model="form.password" :type="showPass ? 'text' : 'password'" class="form-control pe-5 py-2" placeholder="••••••••" />
                        <button type="button" class="position-absolute btn-ghost p-0" style="right: 14px; top: 50%; transform: translateY(-50%);" @click="showPass = !showPass" aria-label="Toggle password">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#A09890" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Confirm password</label>
                    <input v-model="form.password_confirmation" type="password" class="form-control py-2" placeholder="••••••••" @keyup.enter="submit" />
                </div>

                <button class="btn btn-primary w-100 py-2 d-inline-flex align-items-center justify-content-center gap-2" :disabled="loading" @click="submit">
                    {{ loading ? 'Creating account…' : 'Create account' }}
                    <svg v-if="!loading" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </button>
            </div>
        </main>
    </div>
</template>

<style scoped>
.lf-auth { min-height: 100vh; display: grid; grid-template-columns: 1fr; }
@media (min-width: 992px) { .lf-auth { grid-template-columns: 1fr 1fr; } }
.lf-auth__brand { background: linear-gradient(155deg, #1B4332 0%, #2D6A4F 100%); padding: 48px; }
.lf-auth__form { background: #F7F4EF; display: flex; align-items: center; justify-content: center; padding: 40px 24px; }

.lf-role {
    position: relative; width: 100%; height: 100%; text-align: left;
    background: #fff; border: 1.5px solid #E4DFD7; border-radius: 12px; padding: 14px;
    cursor: pointer; transition: border-color .15s, background .15s;
}
.lf-role:hover { border-color: #B8B2A8; }
.lf-role.active { border-color: #2D6A4F; background: rgba(45,106,79,.04); }
.lf-role__radio {
    position: absolute; top: 14px; right: 14px;
    width: 18px; height: 18px; border-radius: 50%; border: 1.5px solid #D4CEC6;
}
.lf-role__radio.checked { border-color: #2D6A4F; }
.lf-role__radio.checked::after { content: ''; position: absolute; inset: 3px; border-radius: 50%; background: #2D6A4F; }
</style>
