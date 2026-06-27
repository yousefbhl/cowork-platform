<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import api from '@/api'

const auth   = useAuthStore()
const router = useRouter()

// ── Form state ────────────────────────────────────────────────────────────────
const form = ref({
    title:       '',
    description: '',
    address:     '',
    city:        '',
    country:     '',
    lat:         '',
    lng:         '',
    amenities:   [],
})

// ── Workspace rows ────────────────────────────────────────────────────────────
function emptyWorkspace() {
    return { workspace_type_id: '', capacity: 1, price_daily: '', price_hourly: '', price_monthly: '', currency: 'MAD' }
}
const workspaces = ref([emptyWorkspace()])

function addWorkspace() {
    workspaces.value.push(emptyWorkspace())
}

function removeWorkspace(idx) {
    workspaces.value.splice(idx, 1)
}

// ── Photos ────────────────────────────────────────────────────────────────────
const photoFiles    = ref([])
const photoPreviews = ref([])

function onPhotosSelected(e) {
    const files = [...e.target.files]
    photoFiles.value    = files
    photoPreviews.value = files.map(f => URL.createObjectURL(f))
}

function removePhoto(idx) {
    photoFiles.value.splice(idx, 1)
    URL.revokeObjectURL(photoPreviews.value[idx])
    photoPreviews.value.splice(idx, 1)
}

// ── Remote data ───────────────────────────────────────────────────────────────
const amenities      = ref([])
const workspaceTypes = ref([])

async function fetchRemote() {
    const [aRes, wRes] = await Promise.all([
        api.get('/amenities'),
        api.get('/workspace-types'),
    ])
    amenities.value      = aRes.data.data
    workspaceTypes.value = wRes.data.data
}

function toggleAmenity(id) {
    const idx = form.value.amenities.indexOf(id)
    if (idx === -1) form.value.amenities.push(id)
    else form.value.amenities.splice(idx, 1)
}

// ── Validation ────────────────────────────────────────────────────────────────
const errors = ref({})

function clearWsError(idx, field) {
    delete errors.value[`workspaces.${idx}.${field}`]
}

function validate() {
    const e = {}

    if (!form.value.title.trim())        e.title = 'Title is required.'
    if (form.value.title.length > 255)   e.title = 'Title must be 255 characters or fewer.'
    if (form.value.address.length > 500) e.address = 'Address must be 500 characters or fewer.'
    if (form.value.city.length > 100)    e.city = 'City must be 100 characters or fewer.'
    if (form.value.country.length > 100) e.country = 'Country must be 100 characters or fewer.'

    if (form.value.lat !== '') {
        const n = Number(form.value.lat)
        if (isNaN(n) || n < -90 || n > 90) e.lat = 'Latitude must be between -90 and 90.'
    }
    if (form.value.lng !== '') {
        const n = Number(form.value.lng)
        if (isNaN(n) || n < -180 || n > 180) e.lng = 'Longitude must be between -180 and 180.'
    }

    if (workspaces.value.length === 0) {
        e.workspaces = 'At least one workspace type is required.'
    }

    workspaces.value.forEach((ws, idx) => {
        if (!ws.workspace_type_id)
            e[`workspaces.${idx}.workspace_type_id`] = 'Workspace type is required.'

        const cap = Number(ws.capacity)
        if (ws.capacity === '' || isNaN(cap) || cap < 1)
            e[`workspaces.${idx}.capacity`] = 'Capacity must be at least 1.'

        const pd = Number(ws.price_daily)
        if (ws.price_daily === '' || isNaN(pd) || pd < 0)
            e[`workspaces.${idx}.price_daily`] = 'Daily price is required (min 0).'
    })

    return e
}

// ── Submit ────────────────────────────────────────────────────────────────────
const submitting = ref(false)

async function submit() {
    errors.value = validate()
    if (Object.keys(errors.value).length) {
        document.querySelector('.field-error')?.scrollIntoView({ behavior: 'smooth', block: 'center' })
        return
    }

    submitting.value = true

    const payload = { title: form.value.title }
    if (form.value.description) payload.description = form.value.description
    if (form.value.address)     payload.address     = form.value.address
    if (form.value.city)        payload.city        = form.value.city
    if (form.value.country)     payload.country     = form.value.country
    if (form.value.lat !== '')  payload.lat         = Number(form.value.lat)
    if (form.value.lng !== '')  payload.lng         = Number(form.value.lng)
    if (form.value.amenities.length) payload.amenities = form.value.amenities

    payload.workspaces = workspaces.value.map(ws => {
        const row = {
            workspace_type_id: Number(ws.workspace_type_id),
            capacity:          Number(ws.capacity),
            price_daily:       Number(ws.price_daily),
            currency:          ws.currency || 'MAD',
        }
        if (ws.price_hourly  !== '') row.price_hourly  = Number(ws.price_hourly)
        if (ws.price_monthly !== '') row.price_monthly = Number(ws.price_monthly)
        return row
    })

    try {
        const { data } = await api.post('/host/spaces', payload)
        const slug = data.data.slug

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

onMounted(() => fetchRemote())
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
                    Your space will be reviewed before going live. Fields marked <span style="color: #2D6A4F;">*</span> are required.
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
                        <div class="form-text" style="font-size: 11px;">{{ form.title.length }}/255</div>
                    </div>

                    <div>
                        <label class="form-label fw-semibold" style="font-size: 13px;">Description</label>
                        <textarea
                            v-model="form.description"
                            class="form-control"
                            rows="4"
                            placeholder="Describe your space — layout, vibe, nearby transport, what makes it special…"
                            style="border-radius: .5rem; font-size: 14px; resize: vertical;"
                        ></textarea>
                    </div>
                </div>

                <!-- ── Section 2: Location ──────────────────────────────── -->
                <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: .75rem; background: #fff;">
                    <h6 class="fw-bold mb-4" style="color: #1B4332; font-size: 13px; letter-spacing: .05em; text-transform: uppercase;">
                        Location
                    </h6>

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

                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" style="font-size: 13px;">
                                Latitude <span class="text-muted fw-normal">(optional)</span>
                            </label>
                            <input
                                v-model="form.lat"
                                type="number"
                                class="form-control font-monospace"
                                :class="{ 'is-invalid': errors.lat }"
                                step="any" min="-90" max="90"
                                placeholder="33.5731"
                                style="border-radius: .5rem; font-size: 13px;"
                                @input="delete errors.lat"
                            />
                            <div v-if="errors.lat" class="invalid-feedback field-error">{{ errors.lat }}</div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" style="font-size: 13px;">
                                Longitude <span class="text-muted fw-normal">(optional)</span>
                            </label>
                            <input
                                v-model="form.lng"
                                type="number"
                                class="form-control font-monospace"
                                :class="{ 'is-invalid': errors.lng }"
                                step="any" min="-180" max="180"
                                placeholder="-7.5898"
                                style="border-radius: .5rem; font-size: 13px;"
                                @input="delete errors.lng"
                            />
                            <div v-if="errors.lng" class="invalid-feedback field-error">{{ errors.lng }}</div>
                        </div>
                    </div>
                </div>

                <!-- ── Section 3: Workspace types & pricing ─────────────── -->
                <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: .75rem; background: #fff;">
                    <h6 class="fw-bold mb-1" style="color: #1B4332; font-size: 13px; letter-spacing: .05em; text-transform: uppercase;">
                        Workspace types &amp; pricing <span style="color: #2D6A4F;">*</span>
                    </h6>
                    <p class="text-muted mb-3" style="font-size: 12px;">
                        Add at least one workspace type. Customers book by type.
                    </p>

                    <div v-if="errors.workspaces" class="mb-3 field-error" style="color: #dc3545; font-size: 13px;">
                        {{ errors.workspaces }}
                    </div>

                    <!-- Workspace rows -->
                    <div class="d-flex flex-column gap-3">
                        <div
                            v-for="(ws, idx) in workspaces"
                            :key="idx"
                            class="p-3"
                            style="background: #F7F4EF; border-radius: .65rem; border: 1px solid #e9ecef;"
                        >
                            <!-- Row header -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-semibold" style="font-size: 12px; color: #2D6A4F; text-transform: uppercase; letter-spacing: .04em;">
                                    Workspace {{ idx + 1 }}
                                </span>
                                <button
                                    v-if="workspaces.length > 1"
                                    type="button"
                                    class="btn btn-sm"
                                    style="color: #991b1b; background: transparent; border: none; font-size: 12px; padding: 0;"
                                    @click="removeWorkspace(idx)"
                                >
                                    Remove
                                </button>
                            </div>

                            <!-- Type + Capacity + Currency -->
                            <div class="row g-2 mb-2">
                                <div class="col-12 col-sm-5">
                                    <label class="form-label fw-semibold" style="font-size: 12px;">
                                        Type <span style="color: #2D6A4F;">*</span>
                                    </label>
                                    <select
                                        v-model="ws.workspace_type_id"
                                        class="form-select"
                                        :class="{ 'is-invalid': errors[`workspaces.${idx}.workspace_type_id`] }"
                                        style="border-radius: .5rem; font-size: 13px;"
                                        @change="clearWsError(idx, 'workspace_type_id')"
                                    >
                                        <option value="">Select type…</option>
                                        <option
                                            v-for="type in workspaceTypes"
                                            :key="type.id"
                                            :value="type.id"
                                        >
                                            {{ type.label }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="errors[`workspaces.${idx}.workspace_type_id`]"
                                        class="invalid-feedback field-error"
                                    >
                                        {{ errors[`workspaces.${idx}.workspace_type_id`] }}
                                    </div>
                                </div>

                                <div class="col-6 col-sm-4">
                                    <label class="form-label fw-semibold" style="font-size: 12px;">
                                        Capacity <span style="color: #2D6A4F;">*</span>
                                    </label>
                                    <input
                                        v-model.number="ws.capacity"
                                        type="number"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors[`workspaces.${idx}.capacity`] }"
                                        min="1"
                                        placeholder="1"
                                        style="border-radius: .5rem; font-size: 13px;"
                                        @input="clearWsError(idx, 'capacity')"
                                    />
                                    <div
                                        v-if="errors[`workspaces.${idx}.capacity`]"
                                        class="invalid-feedback field-error"
                                    >
                                        {{ errors[`workspaces.${idx}.capacity`] }}
                                    </div>
                                </div>

                                <div class="col-6 col-sm-3">
                                    <label class="form-label fw-semibold" style="font-size: 12px;">Currency</label>
                                    <select
                                        v-model="ws.currency"
                                        class="form-select"
                                        style="border-radius: .5rem; font-size: 13px;"
                                    >
                                        <option>MAD</option>
                                        <option>USD</option>
                                        <option>EUR</option>
                                        <option>GBP</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Prices -->
                            <div class="row g-2">
                                <div class="col-12 col-sm-4">
                                    <label class="form-label fw-semibold" style="font-size: 12px;">
                                        Daily price <span style="color: #2D6A4F;">*</span>
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <span
                                            class="input-group-text"
                                            style="font-size: 12px; background: #fff; border-right: none; border-radius: .5rem 0 0 .5rem; color: #2D6A4F; font-weight: 600;"
                                        >
                                            {{ ws.currency }}
                                        </span>
                                        <input
                                            v-model="ws.price_daily"
                                            type="number"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors[`workspaces.${idx}.price_daily`] }"
                                            min="0"
                                            step="0.01"
                                            placeholder="0.00"
                                            style="border-radius: 0 .5rem .5rem 0; font-size: 13px;"
                                            @input="clearWsError(idx, 'price_daily')"
                                        />
                                        <div
                                            v-if="errors[`workspaces.${idx}.price_daily`]"
                                            class="invalid-feedback field-error"
                                        >
                                            {{ errors[`workspaces.${idx}.price_daily`] }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6 col-sm-4">
                                    <label class="form-label fw-semibold" style="font-size: 12px;">
                                        Hourly <span class="text-muted fw-normal">(opt.)</span>
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <span
                                            class="input-group-text"
                                            style="font-size: 12px; background: #fff; border-right: none; border-radius: .5rem 0 0 .5rem;"
                                        >
                                            {{ ws.currency }}
                                        </span>
                                        <input
                                            v-model="ws.price_hourly"
                                            type="number"
                                            class="form-control"
                                            min="0"
                                            step="0.01"
                                            placeholder="—"
                                            style="border-radius: 0 .5rem .5rem 0; font-size: 13px;"
                                        />
                                    </div>
                                </div>

                                <div class="col-6 col-sm-4">
                                    <label class="form-label fw-semibold" style="font-size: 12px;">
                                        Monthly <span class="text-muted fw-normal">(opt.)</span>
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <span
                                            class="input-group-text"
                                            style="font-size: 12px; background: #fff; border-right: none; border-radius: .5rem 0 0 .5rem;"
                                        >
                                            {{ ws.currency }}
                                        </span>
                                        <input
                                            v-model="ws.price_monthly"
                                            type="number"
                                            class="form-control"
                                            min="0"
                                            step="0.01"
                                            placeholder="—"
                                            style="border-radius: 0 .5rem .5rem 0; font-size: 13px;"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add row -->
                    <button
                        type="button"
                        class="btn btn-sm mt-3"
                        style="background: #F7F4EF; color: #2D6A4F; border: 1px dashed #a8c5b5; border-radius: .5rem; font-size: 13px; width: 100%;"
                        @click="addWorkspace"
                    >
                        + Add another workspace type
                    </button>
                </div>

                <!-- ── Section 4: Amenities ─────────────────────────────── -->
                <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: .75rem; background: #fff;">
                    <h6 class="fw-bold mb-1" style="color: #1B4332; font-size: 13px; letter-spacing: .05em; text-transform: uppercase;">
                        Amenities
                    </h6>
                    <p class="text-muted mb-3" style="font-size: 12px;">Select all that your space offers</p>

                    <div class="d-flex flex-wrap gap-2">
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
                </div>

                <!-- ── Section 5: Photos ────────────────────────────────── -->
                <div class="card border-0 shadow-sm p-4 mb-5" style="border-radius: .75rem; background: #fff;">
                    <h6 class="fw-bold mb-1" style="color: #1B4332; font-size: 13px; letter-spacing: .05em; text-transform: uppercase;">
                        Photos
                    </h6>
                    <p class="text-muted mb-3" style="font-size: 12px;">
                        JPG, PNG or WebP · max 5 MB each · first photo becomes the cover
                    </p>

                    <label
                        class="d-flex flex-column align-items-center justify-content-center gap-2 p-4 mb-3"
                        style="border: 2px dashed #d1e7dd; border-radius: .75rem; cursor: pointer; background: #F7F4EF; min-height: 120px;"
                    >
                        <span style="font-size: 1.75rem;">📷</span>
                        <span style="font-size: 13px; color: #2D6A4F; font-weight: 500;">Click to select photos</span>
                        <span style="font-size: 11px; color: #adb5bd;">or drag and drop</span>
                        <input type="file" accept="image/jpeg,image/png,image/webp" multiple class="d-none" @change="onPhotosSelected" />
                    </label>

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
