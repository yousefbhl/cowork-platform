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

const section = ref('profile')

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

    <div class="bg-linen" style="min-height: calc(100vh - 68px);">
        <div class="container py-5" style="max-width: 980px;">

            <!-- Page header -->
            <div class="mb-4">
                <h1 class="headline-lg mb-1" style="color: #1A1A18;">Account Settings</h1>
                <p class="mb-0" style="font-size: 14px; color: #6B6660;">Manage your profile, preferences, and security.</p>
            </div>

            <div class="row g-4 align-items-start">

                <!-- Sidebar -->
                <div class="col-12 col-md-4 col-lg-3">
                    <div class="lf-card p-3 mb-3 d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 44px; height: 44px; background: #2D6A4F; color: #fff; font-size: 17px;">
                            {{ initials }}
                        </div>
                        <div style="min-width: 0;">
                            <p class="fw-semibold mb-0 text-truncate" style="font-size: 14px; color: #1A1A18;">{{ auth.user?.name }}</p>
                            <p class="mb-0 text-truncate" style="font-size: 12px; color: #A09890;">{{ auth.user?.email }}</p>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-1">
                        <button type="button" class="lf-profile-nav" :class="{ active: section === 'profile' }" @click="section = 'profile'">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                            Profile
                        </button>
                        <button type="button" class="lf-profile-nav" :class="{ active: section === 'security' }" @click="section = 'security'">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            Security
                        </button>
                        <div class="px-3 pt-3">
                            <span class="lf-badge lf-badge--available">{{ ROLE_LABEL[auth.user?.role] ?? auth.user?.role }}</span>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="col-12 col-md-8 col-lg-9">

                    <!-- Profile card -->
                    <div v-show="section === 'profile'" class="lf-card overflow-hidden">
                        <div class="px-4 py-3" style="border-bottom: 1px solid #E4DFD7;">
                            <h2 class="headline-sm mb-1" style="color: #1A1A18;">Public Profile</h2>
                            <p class="mb-0" style="font-size: 13px; color: #6B6660;">Update your display name and email address.</p>
                        </div>
                        <div class="px-4 py-4">
                            <div v-if="profileSuccess" class="d-flex align-items-center gap-2 px-3 py-2 mb-4" style="background: rgba(45,106,79,.10); color: #1B4332; font-size: 13px; border-radius: 8px;">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                Profile updated successfully.
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Full name</label>
                                <input v-model="profile.name" type="text" class="form-control py-2" :class="{ 'is-invalid': profileErrors.name }" placeholder="Your full name" @input="profileSuccess = false" />
                                <div v-if="profileErrors.name" class="invalid-feedback">{{ profileErrors.name[0] }}</div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Email address</label>
                                <input v-model="profile.email" type="email" class="form-control py-2" :class="{ 'is-invalid': profileErrors.email }" placeholder="you@example.com" @input="profileSuccess = false" />
                                <div v-if="profileErrors.email" class="invalid-feedback">{{ profileErrors.email[0] }}</div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button class="btn btn-primary px-4 py-2" :disabled="profileSaving" @click="saveProfile">
                                    {{ profileSaving ? 'Saving…' : 'Save changes' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Password card -->
                    <div v-show="section === 'security'" class="lf-card overflow-hidden">
                        <div class="px-4 py-3" style="border-bottom: 1px solid #E4DFD7;">
                            <h2 class="headline-sm mb-1" style="color: #1A1A18;">Security</h2>
                            <p class="mb-0" style="font-size: 13px; color: #6B6660;">You'll need your current password to make changes.</p>
                        </div>
                        <div class="px-4 py-4">
                            <div v-if="passwordSuccess" class="d-flex align-items-center gap-2 px-3 py-2 mb-4" style="background: rgba(45,106,79,.10); color: #1B4332; font-size: 13px; border-radius: 8px;">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                Password changed successfully.
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Current password</label>
                                <input v-model="password.current_password" type="password" class="form-control py-2" :class="{ 'is-invalid': passwordErrors.current_password }" placeholder="Your current password" @input="passwordSuccess = false" />
                                <div v-if="passwordErrors.current_password" class="invalid-feedback">{{ passwordErrors.current_password[0] }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New password</label>
                                <input v-model="password.new_password" type="password" class="form-control py-2" :class="{ 'is-invalid': passwordErrors.new_password }" placeholder="At least 8 characters" @input="passwordSuccess = false" />
                                <div v-if="passwordErrors.new_password" class="invalid-feedback">{{ passwordErrors.new_password[0] }}</div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Confirm new password</label>
                                <input v-model="password.new_password_confirmation" type="password" class="form-control py-2" :class="{ 'is-invalid': passwordErrors.new_password_confirmation }" placeholder="Repeat new password" @input="passwordSuccess = false" />
                                <div v-if="passwordErrors.new_password_confirmation" class="invalid-feedback">{{ passwordErrors.new_password_confirmation[0] }}</div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button class="btn btn-primary px-4 py-2" :disabled="passwordSaving" @click="savePassword">
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

<style scoped>
.lf-profile-nav {
    display: flex; align-items: center; gap: 10px;
    width: 100%; text-align: left; border: none; background: transparent;
    color: #6B6660; font-size: 14px; font-weight: 500;
    padding: 9px 14px; border-radius: 8px; cursor: pointer;
    transition: background .12s, color .12s;
}
.lf-profile-nav:hover { background: rgba(45,106,79,.06); color: #1A1A18; }
.lf-profile-nav.active { background: #E4DFD7; color: #1B4332; font-weight: 600; }
</style>
