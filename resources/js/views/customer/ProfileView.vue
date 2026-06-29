<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import AppNav from '@/components/AppNav.vue'
import api from '@/api'

const auth = useAuthStore()

const profile = reactive({ name: '', email: '' })
const profileErrors  = ref({})
const profileSuccess = ref(false)
const profileSaving  = ref(false)

const password = reactive({
    current_password:          '',
    new_password:              '',
    new_password_confirmation: '',
})
const passwordErrors  = ref({})
const passwordSuccess = ref(false)
const passwordSaving  = ref(false)

onMounted(() => {
    profile.name  = auth.user?.name  ?? ''
    profile.email = auth.user?.email ?? ''
})

async function saveProfile() {
    profileErrors.value  = {}
    profileSuccess.value = false
    profileSaving.value  = true
    try {
        const { data } = await api.patch('/profile', profile)
        auth.user            = data.user
        profile.name         = data.user.name
        profile.email        = data.user.email
        profileSuccess.value = true
    } catch (e) {
        if (e.response?.status === 422) {
            profileErrors.value = e.response.data.errors ?? {}
        }
    } finally {
        profileSaving.value = false
    }
}

async function savePassword() {
    passwordErrors.value  = {}
    passwordSuccess.value = false
    passwordSaving.value  = true
    try {
        await api.patch('/profile/password', password)
        passwordSuccess.value            = true
        password.current_password        = ''
        password.new_password            = ''
        password.new_password_confirmation = ''
    } catch (e) {
        if (e.response?.status === 422) {
            passwordErrors.value = e.response.data.errors ?? {}
        }
    } finally {
        passwordSaving.value = false
    }
}

const initials = computed(() => auth.user?.name?.charAt(0)?.toUpperCase() ?? '?')

const ROLE_LABEL = { customer: 'Customer', host: 'Host', admin: 'Admin' }
</script>

<template>
    <AppNav />

    <div style="background: #F7F4EF; min-height: calc(100vh - 57px);">
        <div class="container py-5" style="max-width: 880px;">

            <!-- Page header -->
            <div class="mb-5">
                <h1 class="fw-bold mb-1" style="font-size: 24px; color: #1B4332; letter-spacing: -.3px;">Account Settings</h1>
                <p class="text-muted mb-0" style="font-size: 14px;">Manage your profile and security preferences.</p>
            </div>

            <div class="row g-4 align-items-start">

                <!-- Sidebar -->
                <div class="col-12 col-md-3">
                    <div
                        class="card border-0 shadow-sm p-4 mb-3 text-center"
                        style="border-radius: .75rem; background: #fff; border: 1px solid #E4DFD7 !important;"
                    >
                        <div
                            class="rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto mb-3"
                            style="width: 56px; height: 56px; background: #2D6A4F; color: #fff; font-size: 20px;"
                        >
                            {{ initials }}
                        </div>
                        <p class="fw-semibold mb-0 text-truncate" style="font-size: 14px; color: #1A1A18;">{{ auth.user?.name }}</p>
                        <p class="mb-2" style="font-size: 11px; color: #A09890; word-break: break-all;">{{ auth.user?.email }}</p>
                        <span
                            class="badge rounded-pill"
                            style="background: #dcfce7; color: #1B4332; font-size: 11px; font-weight: 500; padding: .3em .8em;"
                        >
                            {{ ROLE_LABEL[auth.user?.role] ?? auth.user?.role }}
                        </span>
                    </div>

                    <div class="d-flex flex-column gap-1">
                        <a
                            href="#profile-section"
                            class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 text-decoration-none"
                            style="background: #E4DFD7; color: #1B4332; font-size: 14px; font-weight: 500;"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                            </svg>
                            Profile
                        </a>
                        <a
                            href="#password-section"
                            class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 text-decoration-none"
                            style="color: #6B6660; font-size: 14px;"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            Security
                        </a>
                    </div>
                </div>

                <!-- Content -->
                <div class="col-12 col-md-9">

                    <!-- Profile card -->
                    <div
                        id="profile-section"
                        class="card border-0 shadow-sm mb-4"
                        style="border-radius: .75rem; border: 1px solid #E4DFD7 !important;"
                    >
                        <div
                            class="card-header bg-white px-4 py-3"
                            style="border-radius: .75rem .75rem 0 0; border-bottom: 1px solid #E4DFD7;"
                        >
                            <h2 class="fw-semibold mb-0" style="font-size: 15px; color: #1A1A18;">Profile</h2>
                            <p class="text-muted mb-0 mt-1" style="font-size: 13px;">Update your display name and email address.</p>
                        </div>

                        <div class="card-body px-4 py-4">
                            <div
                                v-if="profileSuccess"
                                class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 mb-4"
                                style="background: #dcfce7; color: #1B4332; font-size: 13px;"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Profile updated successfully.
                            </div>

                            <div class="mb-3">
                                <label
                                    class="form-label"
                                    style="font-size: 11px; font-weight: 600; letter-spacing: .05em; color: #6B6660; text-transform: uppercase;"
                                >
                                    Full name
                                </label>
                                <input
                                    v-model="profile.name"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': profileErrors.name }"
                                    placeholder="Your full name"
                                    style="border-color: #D4CEC6; border-radius: .5rem;"
                                    @input="profileSuccess = false"
                                />
                                <div v-if="profileErrors.name" class="invalid-feedback">{{ profileErrors.name[0] }}</div>
                            </div>

                            <div class="mb-4">
                                <label
                                    class="form-label"
                                    style="font-size: 11px; font-weight: 600; letter-spacing: .05em; color: #6B6660; text-transform: uppercase;"
                                >
                                    Email address
                                </label>
                                <input
                                    v-model="profile.email"
                                    type="email"
                                    class="form-control"
                                    :class="{ 'is-invalid': profileErrors.email }"
                                    placeholder="you@example.com"
                                    style="border-color: #D4CEC6; border-radius: .5rem;"
                                    @input="profileSuccess = false"
                                />
                                <div v-if="profileErrors.email" class="invalid-feedback">{{ profileErrors.email[0] }}</div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button
                                    class="btn px-5 py-2"
                                    style="background: #1B4332; color: #fff; border-radius: 9999px; font-size: 14px; font-weight: 600; border: none;"
                                    :disabled="profileSaving"
                                    @click="saveProfile"
                                >
                                    {{ profileSaving ? 'Saving…' : 'Save changes' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Password card -->
                    <div
                        id="password-section"
                        class="card border-0 shadow-sm"
                        style="border-radius: .75rem; border: 1px solid #E4DFD7 !important;"
                    >
                        <div
                            class="card-header bg-white px-4 py-3"
                            style="border-radius: .75rem .75rem 0 0; border-bottom: 1px solid #E4DFD7;"
                        >
                            <h2 class="fw-semibold mb-0" style="font-size: 15px; color: #1A1A18;">Security</h2>
                            <p class="text-muted mb-0 mt-1" style="font-size: 13px;">You'll need your current password to make changes.</p>
                        </div>

                        <div class="card-body px-4 py-4">
                            <div
                                v-if="passwordSuccess"
                                class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 mb-4"
                                style="background: #dcfce7; color: #1B4332; font-size: 13px;"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Password changed successfully.
                            </div>

                            <div class="mb-3">
                                <label
                                    class="form-label"
                                    style="font-size: 11px; font-weight: 600; letter-spacing: .05em; color: #6B6660; text-transform: uppercase;"
                                >
                                    Current password
                                </label>
                                <input
                                    v-model="password.current_password"
                                    type="password"
                                    class="form-control"
                                    :class="{ 'is-invalid': passwordErrors.current_password }"
                                    placeholder="Your current password"
                                    style="border-color: #D4CEC6; border-radius: .5rem;"
                                    @input="passwordSuccess = false"
                                />
                                <div v-if="passwordErrors.current_password" class="invalid-feedback">{{ passwordErrors.current_password[0] }}</div>
                            </div>

                            <div class="mb-3">
                                <label
                                    class="form-label"
                                    style="font-size: 11px; font-weight: 600; letter-spacing: .05em; color: #6B6660; text-transform: uppercase;"
                                >
                                    New password
                                </label>
                                <input
                                    v-model="password.new_password"
                                    type="password"
                                    class="form-control"
                                    :class="{ 'is-invalid': passwordErrors.new_password }"
                                    placeholder="At least 8 characters"
                                    style="border-color: #D4CEC6; border-radius: .5rem;"
                                    @input="passwordSuccess = false"
                                />
                                <div v-if="passwordErrors.new_password" class="invalid-feedback">{{ passwordErrors.new_password[0] }}</div>
                            </div>

                            <div class="mb-4">
                                <label
                                    class="form-label"
                                    style="font-size: 11px; font-weight: 600; letter-spacing: .05em; color: #6B6660; text-transform: uppercase;"
                                >
                                    Confirm new password
                                </label>
                                <input
                                    v-model="password.new_password_confirmation"
                                    type="password"
                                    class="form-control"
                                    :class="{ 'is-invalid': passwordErrors.new_password_confirmation }"
                                    placeholder="Repeat new password"
                                    style="border-color: #D4CEC6; border-radius: .5rem;"
                                    @input="passwordSuccess = false"
                                />
                                <div v-if="passwordErrors.new_password_confirmation" class="invalid-feedback">{{ passwordErrors.new_password_confirmation[0] }}</div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button
                                    class="btn px-5 py-2"
                                    style="background: #1B4332; color: #fff; border-radius: 9999px; font-size: 14px; font-weight: 600; border: none;"
                                    :disabled="passwordSaving"
                                    @click="savePassword"
                                >
                                    {{ passwordSaving ? 'Updating…' : 'Update password' }}
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</template>
