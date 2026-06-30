<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api'
import HostSidebar from '@/components/HostSidebar.vue'

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

onMounted(() => fetchRemote())
</script>

<template>
    <div class="d-flex align-items-stretch bg-linen" style="min-height: 100vh;">
        <HostSidebar />

        <main class="flex-grow-1 px-4 px-lg-5 py-5" style="min-width: 0;">
            <div style="max-width: 760px; margin: 0 auto;">

                <!-- Header -->
                <div class="mb-4">
                    <h1 class="headline-lg mb-1" style="color: #1A1A18;">Create a new listing</h1>
                    <p class="mb-0" style="font-size: 14px; color: #6B6660;">
                        Your space will be reviewed before going live. Fields marked <span class="text-forest">*</span> are required.
                    </p>
                </div>

                <!-- General error -->
                <div v-if="errors._general" class="mb-4 px-3 py-2" style="background: rgba(192,57,43,.08); color: #7B1A12; border: 1px solid rgba(192,57,43,.2); border-radius: 10px; font-size: 13px;">
                    {{ errors._general }}
                </div>

                <form @submit.prevent="submit" novalidate>

                    <!-- Section 1: Space details -->
                    <section class="lf-card p-4 p-lg-4 mb-4">
                        <h2 class="label-caps mb-4" style="color: #6B6660;">Space details</h2>

                        <div class="mb-4">
                            <label class="form-label">Title <span class="text-forest">*</span></label>
                            <input v-model="form.title" type="text" class="form-control py-2" :class="{ 'is-invalid': errors.title }" maxlength="255" placeholder="e.g. Bright open-plan desk in central Casablanca" @input="delete errors.title" />
                            <div v-if="errors.title" class="invalid-feedback field-error">{{ errors.title }}</div>
                            <div class="mt-1" style="font-size: 11px; color: #A09890;">{{ form.title.length }}/255</div>
                        </div>

                        <div>
                            <label class="form-label">Description</label>
                            <textarea v-model="form.description" class="form-control py-2" rows="4" placeholder="Describe your space — layout, vibe, nearby transport, what makes it special…" style="resize: vertical;"></textarea>
                        </div>
                    </section>

                    <!-- Section 2: Location -->
                    <section class="lf-card p-4 mb-4">
                        <h2 class="label-caps mb-4" style="color: #6B6660;">Location</h2>

                        <div class="mb-3">
                            <label class="form-label">Street address</label>
                            <input v-model="form.address" type="text" class="form-control py-2" :class="{ 'is-invalid': errors.address }" maxlength="500" placeholder="123 Boulevard Mohammed V, floor 3" @input="delete errors.address" />
                            <div v-if="errors.address" class="invalid-feedback field-error">{{ errors.address }}</div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label">City</label>
                                <input v-model="form.city" type="text" class="form-control py-2" :class="{ 'is-invalid': errors.city }" maxlength="100" placeholder="Casablanca" @input="delete errors.city" />
                                <div v-if="errors.city" class="invalid-feedback field-error">{{ errors.city }}</div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="form-label">Country</label>
                                <input v-model="form.country" type="text" class="form-control py-2" :class="{ 'is-invalid': errors.country }" maxlength="100" placeholder="Morocco" @input="delete errors.country" />
                                <div v-if="errors.country" class="invalid-feedback field-error">{{ errors.country }}</div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label">Latitude <span class="text-tertiary-lf fw-normal">(optional)</span></label>
                                <input v-model="form.lat" type="number" class="form-control font-mono py-2" :class="{ 'is-invalid': errors.lat }" step="any" min="-90" max="90" placeholder="33.5731" @input="delete errors.lat" />
                                <div v-if="errors.lat" class="invalid-feedback field-error">{{ errors.lat }}</div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="form-label">Longitude <span class="text-tertiary-lf fw-normal">(optional)</span></label>
                                <input v-model="form.lng" type="number" class="form-control font-mono py-2" :class="{ 'is-invalid': errors.lng }" step="any" min="-180" max="180" placeholder="-7.5898" @input="delete errors.lng" />
                                <div v-if="errors.lng" class="invalid-feedback field-error">{{ errors.lng }}</div>
                            </div>
                        </div>
                    </section>

                    <!-- Section 3: Workspaces & pricing -->
                    <section class="lf-card p-4 mb-4">
                        <h2 class="label-caps mb-1" style="color: #6B6660;">Workspaces &amp; pricing <span class="text-forest">*</span></h2>
                        <p class="mb-3" style="font-size: 13px; color: #6B6660;">Define the areas available for booking and set their rates. Customers book by type.</p>

                        <div v-if="errors.workspaces" class="mb-3 field-error" style="color: #C0392B; font-size: 13px;">{{ errors.workspaces }}</div>

                        <div class="d-flex flex-column gap-3">
                            <div v-for="(ws, idx) in workspaces" :key="idx" class="lf-ws-row">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="label-caps text-forest">Workspace {{ idx + 1 }}</span>
                                    <button v-if="workspaces.length > 1" type="button" class="btn-ghost p-0 d-inline-flex align-items-center gap-1" style="color: #C0392B; font-size: 12px;" @click="removeWorkspace(idx)">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        Remove
                                    </button>
                                </div>

                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm-5">
                                        <label class="form-label" style="font-size: 12px;">Type <span class="text-forest">*</span></label>
                                        <select v-model="ws.workspace_type_id" class="form-select" :class="{ 'is-invalid': errors[`workspaces.${idx}.workspace_type_id`] }" @change="clearWsError(idx, 'workspace_type_id')">
                                            <option value="">Select type…</option>
                                            <option v-for="type in workspaceTypes" :key="type.id" :value="type.id">{{ type.label }}</option>
                                        </select>
                                        <div v-if="errors[`workspaces.${idx}.workspace_type_id`]" class="invalid-feedback field-error">{{ errors[`workspaces.${idx}.workspace_type_id`] }}</div>
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label class="form-label" style="font-size: 12px;">Capacity <span class="text-forest">*</span></label>
                                        <input v-model.number="ws.capacity" type="number" class="form-control" :class="{ 'is-invalid': errors[`workspaces.${idx}.capacity`] }" min="1" placeholder="1" @input="clearWsError(idx, 'capacity')" />
                                        <div v-if="errors[`workspaces.${idx}.capacity`]" class="invalid-feedback field-error">{{ errors[`workspaces.${idx}.capacity`] }}</div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <label class="form-label" style="font-size: 12px;">Currency</label>
                                        <select v-model="ws.currency" class="form-select">
                                            <option>MAD</option><option>USD</option><option>EUR</option><option>GBP</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-12 col-sm-4">
                                        <label class="form-label" style="font-size: 12px;">Daily rate <span class="text-forest">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="font-size: 12px; background: #fff; color: #2D6A4F; font-weight: 600;">{{ ws.currency }}</span>
                                            <input v-model="ws.price_daily" type="number" class="form-control font-mono" :class="{ 'is-invalid': errors[`workspaces.${idx}.price_daily`] }" min="0" step="0.01" placeholder="0.00" @input="clearWsError(idx, 'price_daily')" />
                                            <div v-if="errors[`workspaces.${idx}.price_daily`]" class="invalid-feedback field-error">{{ errors[`workspaces.${idx}.price_daily`] }}</div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label class="form-label" style="font-size: 12px;">Hourly <span class="text-tertiary-lf fw-normal">(opt.)</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="font-size: 12px; background: #fff;">{{ ws.currency }}</span>
                                            <input v-model="ws.price_hourly" type="number" class="form-control font-mono" min="0" step="0.01" placeholder="—" />
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label class="form-label" style="font-size: 12px;">Monthly <span class="text-tertiary-lf fw-normal">(opt.)</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="font-size: 12px; background: #fff;">{{ ws.currency }}</span>
                                            <input v-model="ws.price_monthly" type="number" class="form-control font-mono" min="0" step="0.01" placeholder="—" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="lf-add-row mt-3" @click="addWorkspace">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Add another workspace type
                        </button>
                        <p class="mb-0 mt-3 d-flex align-items-center gap-1" style="font-size: 12px; color: #A09890;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            CoworkPlatform takes 8% — you receive 92%
                        </p>
                    </section>

                    <!-- Section 4: Amenities -->
                    <section class="lf-card p-4 mb-4">
                        <h2 class="label-caps mb-1" style="color: #6B6660;">Amenities</h2>
                        <p class="mb-3" style="font-size: 13px; color: #6B6660;">Select all that your space offers.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <button v-for="amenity in amenities" :key="amenity.id" type="button" class="lf-amenity-chip" :class="{ active: form.amenities.includes(amenity.id) }" @click="toggleAmenity(amenity.id)">
                                {{ amenity.name }}
                            </button>
                        </div>
                    </section>

                    <!-- Section 5: Photos -->
                    <section class="lf-card p-4 mb-4">
                        <h2 class="label-caps mb-1" style="color: #6B6660;">Photos</h2>
                        <p class="mb-3" style="font-size: 13px; color: #6B6660;">JPG, PNG or WebP · max 5 MB each · first photo becomes the cover.</p>

                        <label class="lf-dropzone d-flex flex-column align-items-center justify-content-center gap-2">
                            <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 48px; height: 48px; background: #EEEAE3;">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#6B6660" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            </div>
                            <span style="font-size: 14px; color: #1A1A18; font-weight: 600;">Drag &amp; drop photos here</span>
                            <span style="font-size: 13px; color: #6B6660;">or click to browse from your computer</span>
                            <input type="file" accept="image/jpeg,image/png,image/webp" multiple class="d-none" @change="onPhotosSelected" />
                        </label>

                        <div v-if="photoPreviews.length" class="row g-2 mt-1">
                            <div v-for="(preview, idx) in photoPreviews" :key="idx" class="col-4 col-md-3 position-relative">
                                <img :src="preview" class="w-100 object-fit-cover" style="aspect-ratio: 4/3; border-radius: 8px;" :alt="`Photo ${idx + 1}`" />
                                <span v-if="idx === 0" class="position-absolute top-0 start-0 m-1 px-2 py-1" style="background: rgba(26,26,24,.72); color: #fff; font-size: 9px; font-weight: 600; border-radius: 4px;">Cover</span>
                                <button type="button" class="position-absolute top-0 end-0 m-1 d-flex align-items-center justify-content-center" style="width: 22px; height: 22px; padding: 0; background: rgba(26,26,24,.6); color: #fff; border: none; border-radius: 50%;" @click="removePhoto(idx)">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>
                        </div>
                    </section>

                    <!-- Actions -->
                    <div class="d-flex justify-content-between align-items-center pb-5">
                        <RouterLink to="/host/listings" class="btn btn-outline-secondary px-4 py-2">Cancel</RouterLink>
                        <button type="submit" class="btn btn-primary px-4 py-2" :disabled="submitting" style="min-width: 150px;">
                            <span v-if="submitting"><span class="spinner-border spinner-border-sm me-2"></span>Submitting…</span>
                            <span v-else>Submit listing</span>
                        </button>
                    </div>

                </form>
            </div>
        </main>
    </div>
</template>

<style scoped>
.lf-ws-row { background: #F7F4EF; border: 1px solid #E4DFD7; border-radius: 12px; padding: 16px; }
.lf-add-row {
    width: 100%; background: #F7F4EF; color: #2D6A4F;
    border: 1.5px dashed #A8C5B5; border-radius: 10px; padding: 12px;
    font-size: 13px; font-weight: 600;
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    cursor: pointer; transition: background .15s, border-color .15s;
}
.lf-add-row:hover { background: rgba(45,106,79,.06); border-color: #2D6A4F; }

.lf-amenity-chip {
    background: #F7F4EF; color: #4A4740;
    border: 1px solid #D4CEC6; border-radius: 9999px; padding: 6px 14px;
    font-size: 13px; cursor: pointer; transition: background .15s, color .15s, border-color .15s;
}
.lf-amenity-chip:hover { border-color: #B8B2A8; }
.lf-amenity-chip.active { background: #2D6A4F; color: #fff; border-color: #2D6A4F; }

.lf-dropzone {
    border: 2px dashed #D4CEC6; border-radius: 12px; padding: 28px;
    cursor: pointer; background: #F7F4EF; min-height: 140px;
    transition: border-color .15s, background .15s;
}
.lf-dropzone:hover { border-color: #2D6A4F; background: rgba(45,106,79,.03); }
</style>
