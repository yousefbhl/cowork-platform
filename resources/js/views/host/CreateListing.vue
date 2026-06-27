<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import api from '@/api'

const auth   = useAuthStore()
const router = useRouter()

// ── Form state ───────────────────────────────────────────────────────────────
const form = ref({
    title:       '',
    description: '',
    address:     '',
    city:        '',
    country:     '',
    lat:         '',
    lng:         '',
    amenities:   [],   // array of amenity IDs
})

// ── Photos ────────────────────────────────────────────────────────────────────
const photoFiles   = ref([])
const photoPreviews = ref([])

function onPhotosSelected(e) {
    const files = [...e.target.files]
    photoFiles.value = files
    photoPreviews.value = files.map(f => URL.createObjectURL(f))
}

function removePhoto(idx) {
    photoFiles.value.splice(idx, 1)
    URL.revokeObjectURL(photoPreviews.value[idx])
    photoPreviews.value.splice(idx, 1)
}

// ── Amenities ─────────────────────────────────────────────────────────────────
const amenities        = ref([])
const loadingAmenities = ref(false)

function toggleAmenity(id) {
    const idx = form.value.amenities.indexOf(id)
    if (idx === -1) form.value.amenities.push(id)
    else form.value.amenities.splice(idx, 1)
}

// ── Validation ────────────────────────────────────────────────────────────────
const errors = ref({})

function validate() {
    const e = {}
    if (!form.value.title.trim())          e.title = 'Title is required.'
    if (form.value.title.length > 255)     e.title = 'Title must be 255 characters or fewer.'
    if (form.value.address.length > 500)   e.address = 'Address must be 500 characters or fewer.'
    if (form.value.city.length > 100)      e.city = 'City must be 100 characters or fewer.'
    if (form.value.country.length > 100)   e.country = 'Country must be 100 characters or fewer.'
    if (form.value.lat !== '') {
        const n = Number(form.value.lat)
        if (isNaN(n) || n < -90 || n > 90)
            e.lat = 'Latitude must be a number between -90 and 90.'
    }
    if (form.value.lng !== '') {
        const n = Number(form.value.lng)
        if (isNaN(n) || n < -180 || n > 180)
            e.lng = 'Longitude must be a number between -180 and 180.'
    }
    return e
}

// ── Submit ────────────────────────────────────────────────────────────────────
const submitting = ref(false)

async function submit() {
    errors.value = validate()
    if (Object.keys(errors.value).length) return

    submitting.value = true

    // Build payload — omit empty optional fields
    const payload = { title: form.value.title }
    if (form.value.description) payload.description = form.value.description
    if (form.value.address)     payload.address     = form.value.address
    if (form.value.city)        payload.city        = form.value.city
    if (form.value.country)     payload.country     = form.value.country
    if (form.value.lat !== '')  payload.lat         = Number(form.value.lat)
    if (form.value.lng !== '')  payload.lng         = Number(form.value.lng)
    if (form.value.amenities.length) payload.amenities = form.value.amenities

    try {
        const { data } = await api.post('/host/spaces', payload)
        const slug = data.data.slug

        // Upload photos one by one
        for (const file of photoFiles.value) {
            const fd = new FormData()
            fd.append('photo', file)
            await api.post(`/host/spaces/${slug}/photos`, fd, {
                headers: { 'Content-Type': 'multipart/form-data' },
            })
        }

        router.push('/host/listings')
    } catch (e) {
        if (e.response?.status === 422) {
            const serverErrors = e.response.data.errors ?? {}
            errors.value = Object.fromEntries(
                Object.entries(serverErrors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
            )
            // scroll to first error
            document.querySelector('.field-error')?.scrollIntoView({ behavior: 'smooth', block: 'center' })
        } else {
            errors.value = { _general: e.response?.data?.message ?? 'Something went wrong. Please try again.' }
        }
    } finally {
        submitting.value = false
    }
}

async function handleLogout() {
    await auth.logout()
    router.push('/login')
}

async function fetchAmenities() {
    loadingAmenities.value = true
    try {
        const { data } = await api.get('/amenities')
        amenities.value = data.data
    } finally {
        loadingAmenities.value = false
    }
}

onMounted(() => fetchAmenities())
</script>

<template>
    <!-- Nav ──────────────────────────────────────────────────────────────── -->
    <nav class="navbar navbar-light bg-white border-bottom px-4" style="min-height: 57px;">
        <RouterLink to="/host" class="navbar-brand fw-bold" style="letter-spacing: -.3px; color: #1B4332;">
            CoworkPlatform — Host
        </RouterLink>
        <div class="d-flex gap-2 align-items-center">
            <RouterLink to="/host/listings" class="btn btn-sm btn-outline-secondary" style="font-size: 13px;">
                My Listings
            </RouterLink>
            <RouterLink to="/host/bookings" class="btn btn-sm btn-outline-secondary" style="font-size: 13px;">
                Bookings
            </RouterLink>
            <span class="text-muted d-none d-md-inline" style="font-size: 13px;">{{ auth.user?.name }}</span>
            <RouterLink to="/" class="btn btn-sm btn-outline-secondary">Home</RouterLink>
            <button class="btn btn-sm btn-outline-secondary" @click="handleLogout">Logout</button>
        </div>
    </nav>

    <!-- Page ─────────────────────────────────────────────────────────────── -->
    <div style="background: #F7F4EF; min-height: calc(100vh - 57px);">
        <div class="container py-5" style="max-width: 720px;">

            <!-- Header -->
            <div class="mb-5">
                <h1 class="h4 fw-bold mb-1" style="color: #1B4332;">Create a new listing</h1>
                <p class="text-muted mb-0" style="font-size: 13px;">
                    Your space will be reviewed before going live. Fields marked * are required.
                </p>
            </div>

            <!-- General error -->
            <div
                v-if="errors._general"
                class="alert mb-4 py-2 px-3"
                style="background: #fee2e2; color: #991b1b; border: none; border-radius: .75rem; font-size: 13px;"
            >
                {{ errors._general }}
            </div>

            <form @submit.prevent="submit" novalidate>

                <!-- ── Section 1: Space details ─────────────────────────── -->
                <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: .75rem; background: #fff;">
                    <h6 class="fw-bold mb-4" style="color: #1B4332; font-size: 13px; letter-spacing: .05em; text-transform: uppercase;">
                        Space details
                    </h6>

                    <!-- Title -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size: 13px;">
                            Title <span style="color: #2D6A4F;">*</span>
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': errors.title }"
                            maxlength="255"
                            placeholder="e.g. Bright open-plan desk in central Casablanca"
                            style="border-radius: .5rem; font-size: 14px;"
                            @input="delete errors.title"
                        />
                        <div v-if="errors.title" class="invalid-feedback field-error">{{ errors.title }}</div>
                        <div class="form-text" style="font-size: 11px;">
                            {{ form.title.length }}/255
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-0">
                        <label class="form-label fw-semibold" style="font-size: 13px;">Description</label>
                        <textarea
                            v-model="form.description"
                            class="form-control"
                            :class="{ 'is-invalid': errors.description }"
                            rows="4"
                            placeholder="Describe your space — layout, vibe, nearby transport, what makes it special…"
                            style="border-radius: .5rem; font-size: 14px; resize: vertical;"
                        ></textarea>
                        <div v-if="errors.description" class="invalid-feedback field-error">{{ errors.description }}</div>
                    </div>
                </div>

                <!-- ── Section 2: Location ──────────────────────────────── -->
                <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: .75rem; background: #fff;">
                    <h6 class="fw-bold mb-4" style="color: #1B4332; font-size: 13px; letter-spacing: .05em; text-transform: uppercase;">
                        Location
                    </h6>

                    <!-- Address -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 13px;">Street address</label>
                        <input
                            v-model="form.address"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': errors.address }"
                            maxlength="500"
                            placeholder="123 Boulevard Mohammed V, floor 3"
                            style="border-radius: .5rem; font-size: 14px;"
                            @input="delete errors.address"
                        />
                        <div v-if="errors.address" class="invalid-feedback field-error">{{ errors.address }}</div>
                    </div>

                    <!-- City + Country -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" style="font-size: 13px;">City</label>
                            <input
                                v-model="form.city"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': errors.city }"
                                maxlength="100"
                                placeholder="Casablanca"
                                style="border-radius: .5rem; font-size: 14px;"
                                @input="delete errors.city"
                            />
                            <div v-if="errors.city" class="invalid-feedback field-error">{{ errors.city }}</div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" style="font-size: 13px;">Country</label>
                            <input
                                v-model="form.country"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': errors.country }"
                                maxlength="100"
                                placeholder="Morocco"
                                style="border-radius: .5rem; font-size: 14px;"
                                @input="delete errors.country"
                            />
                            <div v-if="errors.country" class="invalid-feedback field-error">{{ errors.country }}</div>
                        </div>
                    </div>

                    <!-- Lat + Lng (optional) -->
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" style="font-size: 13px;">
                                Latitude
                                <span class="text-muted fw-normal">(optional)</span>
                            </label>
                            <input
                                v-model="form.lat"
                                type="number"
                                class="form-control font-monospace"
                                :class="{ 'is-invalid': errors.lat }"
                                step="any"
                                min="-90"
                                max="90"
                                placeholder="33.5731"
                                style="border-radius: .5rem; font-size: 13px;"
                                @input="delete errors.lat"
                            />
                            <div v-if="errors.lat" class="invalid-feedback field-error">{{ errors.lat }}</div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" style="font-size: 13px;">
                                Longitude
                                <span class="text-muted fw-normal">(optional)</span>
                            </label>
                            <input
                                v-model="form.lng"
                                type="number"
                                class="form-control font-monospace"
                                :class="{ 'is-invalid': errors.lng }"
                                step="any"
                                min="-180"
                                max="180"
                                placeholder="-7.5898"
                                style="border-radius: .5rem; font-size: 13px;"
                                @input="delete errors.lng"
                            />
                            <div v-if="errors.lng" class="invalid-feedback field-error">{{ errors.lng }}</div>
                        </div>
                    </div>
                </div>

                <!-- ── Section 3: Amenities ─────────────────────────────── -->
                <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: .75rem; background: #fff;">
                    <h6 class="fw-bold mb-1" style="color: #1B4332; font-size: 13px; letter-spacing: .05em; text-transform: uppercase;">
                        Amenities
                    </h6>
                    <p class="text-muted mb-3" style="font-size: 12px;">Select all that your space offers</p>

                    <div v-if="loadingAmenities" class="d-flex flex-wrap gap-2 placeholder-glow">
                        <span v-for="n in 8" :key="n" class="placeholder rounded-pill" style="width: 80px; height: 30px;"></span>
                    </div>

                    <div v-else class="d-flex flex-wrap gap-2">
                        <button
                            v-for="amenity in amenities"
                            :key="amenity.id"
                            type="button"
                            class="btn btn-sm"
                            :style="form.amenities.includes(amenity.id)
                                ? 'background: #2D6A4F; color: #fff; border: none;'
                                : 'background: #F7F4EF; color: #495057; border: 1px solid #dee2e6;'"
                            style="border-radius: 2rem; font-size: 13px; padding: .35em .85em;"
                            @click="toggleAmenity(amenity.id)"
                        >
                            {{ amenity.name }}
                        </button>
                    </div>

                    <div v-if="errors.amenities" class="mt-2 field-error" style="color: #dc3545; font-size: 13px;">
                        {{ errors.amenities }}
                    </div>
                </div>

                <!-- ── Section 4: Photos ────────────────────────────────── -->
                <div class="card border-0 shadow-sm p-4 mb-5" style="border-radius: .75rem; background: #fff;">
                    <h6 class="fw-bold mb-1" style="color: #1B4332; font-size: 13px; letter-spacing: .05em; text-transform: uppercase;">
                        Photos
                    </h6>
                    <p class="text-muted mb-3" style="font-size: 12px;">
                        JPG, PNG or WebP · max 5 MB each · first photo becomes the cover
                    </p>

                    <!-- Drop zone -->
                    <label
                        class="d-flex flex-column align-items-center justify-content-center gap-2 p-4 mb-3"
                        style="border: 2px dashed #d1e7dd; border-radius: .75rem; cursor: pointer; background: #F7F4EF; min-height: 120px;"
                    >
                        <span style="font-size: 1.75rem;">📷</span>
                        <span style="font-size: 13px; color: #2D6A4F; font-weight: 500;">
                            Click to select photos
                        </span>
                        <span style="font-size: 11px; color: #adb5bd;">or drag and drop</span>
                        <input
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            multiple
                            class="d-none"
                            @change="onPhotosSelected"
                        />
                    </label>

                    <!-- Previews -->
                    <div v-if="photoPreviews.length" class="row g-2">
                        <div
                            v-for="(preview, idx) in photoPreviews"
                            :key="idx"
                            class="col-4 col-md-3 position-relative"
                        >
                            <img
                                :src="preview"
                                class="w-100 object-fit-cover"
                                style="aspect-ratio: 4/3; border-radius: .5rem;"
                                :alt="`Photo ${idx + 1}`"
                            />
                            <span
                                v-if="idx === 0"
                                class="position-absolute top-0 start-0 badge m-1"
                                style="background: #2D6A4F; font-size: 9px;"
                            >
                                Cover
                            </span>
                            <button
                                type="button"
                                class="position-absolute top-0 end-0 btn btn-sm m-1 d-flex align-items-center justify-content-center"
                                style="width: 22px; height: 22px; padding: 0; background: rgba(0,0,0,.55); color: #fff; border: none; border-radius: 50%; font-size: 12px;"
                                @click="removePhoto(idx)"
                            >
                                ×
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ── Workspace note ───────────────────────────────────── -->
                <div
                    class="d-flex align-items-start gap-2 mb-4 p-3"
                    style="background: #dcfce7; border-radius: .75rem; font-size: 13px; color: #1B4332;"
                >
                    <span class="flex-shrink-0">ℹ️</span>
                    <span>
                        <strong>Workspace types & pricing</strong> (Hot Desk, Private Office, etc.) are configured
                        after your listing is created and approved. You'll find the option in your listing settings.
                    </span>
                </div>

                <!-- ── Actions ──────────────────────────────────────────── -->
                <div class="d-flex justify-content-between align-items-center">
                    <RouterLink
                        to="/host/listings"
                        class="btn btn-outline-secondary"
                        style="border-radius: .5rem;"
                    >
                        Cancel
                    </RouterLink>
                    <button
                        type="submit"
                        class="btn px-4"
                        :disabled="submitting"
                        style="background: #2D6A4F; color: #fff; border: none; border-radius: .5rem; min-width: 140px;"
                    >
                        <span v-if="submitting">
                            <span class="spinner-border spinner-border-sm me-2"></span>Submitting…
                        </span>
                        <span v-else>Submit listing</span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</template>
