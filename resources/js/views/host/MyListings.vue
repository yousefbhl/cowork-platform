<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import api from '@/api'

const auth   = useAuthStore()
const router = useRouter()

const spaces      = ref([])
const meta        = ref({})
const loading     = ref(false)
const filter      = ref('all')

const STATUS_LABEL = {
    draft:     'Pending review',
    published: 'Live',
    paused:    'Paused',
}
const STATUS_STYLE = {
    draft:     'color: #92400e; background: #fef3c7;',
    published: 'color: #1B4332; background: #dcfce7;',
    paused:    'color: #991b1b; background: #fee2e2;',
}

const counts = computed(() => ({
    all:       spaces.value.length,
    draft:     spaces.value.filter(s => s.status === 'draft').length,
    published: spaces.value.filter(s => s.status === 'published').length,
    paused:    spaces.value.filter(s => s.status === 'paused').length,
}))

const filtered = computed(() =>
    filter.value === 'all'
        ? spaces.value
        : spaces.value.filter(s => s.status === filter.value)
)

function minPrice(workspaces) {
    const prices = (workspaces ?? []).map(w => w.price_daily).filter(Boolean)
    return prices.length ? Math.min(...prices) : null
}

async function fetchSpaces(page = 1) {
    loading.value = true
    try {
        const { data } = await api.get('/host/spaces', { params: { page } })
        spaces.value = data.data
        meta.value   = data.meta ?? {}
    } finally {
        loading.value = false
    }
}

async function handleLogout() {
    await auth.logout()
    router.push('/login')
}

onMounted(() => fetchSpaces())
</script>

<template>
    <!-- Nav -->
    <nav class="navbar navbar-light bg-white border-bottom px-4" style="min-height: 57px;">
        <RouterLink to="/host" class="navbar-brand fw-bold" style="letter-spacing: -.3px; color: #1B4332;">
            CoworkPlatform — Host
        </RouterLink>
        <div class="d-flex gap-2 align-items-center">
            <RouterLink
                to="/host/listings"
                class="btn btn-sm"
                style="background: #2D6A4F; color: #fff; border: none; font-size: 13px;"
            >
                My Listings
            </RouterLink>
            <RouterLink
                to="/host/bookings"
                class="btn btn-sm btn-outline-secondary"
                style="font-size: 13px;"
            >
                Bookings
            </RouterLink>
            <span class="text-muted d-none d-md-inline" style="font-size: 13px;">{{ auth.user?.name }}</span>
            <RouterLink to="/" class="btn btn-sm btn-outline-secondary">Home</RouterLink>
            <button class="btn btn-sm btn-outline-secondary" @click="handleLogout">Logout</button>
        </div>
    </nav>

    <!-- Page -->
    <div style="background: #F7F4EF; min-height: calc(100vh - 57px);">
        <div class="container py-5">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
                <div>
                    <h1 class="h4 fw-bold mb-1" style="color: #1B4332;">My Listings</h1>
                    <p class="text-muted mb-0" style="font-size: 13px;">
                        {{ counts.all }} space{{ counts.all !== 1 ? 's' : '' }} · {{ counts.published }} live
                    </p>
                </div>
                <RouterLink
                    to="/host/listings/new"
                    class="btn"
                    style="background: #2D6A4F; color: #fff; border: none; border-radius: .5rem; font-size: 13px;"
                >
                    + New Listing
                </RouterLink>
            </div>

            <!-- Filter tabs -->
            <div class="d-flex gap-1 mb-4 flex-wrap">
                <button
                    v-for="tab in [
                        { key: 'all',       label: 'All' },
                        { key: 'draft',     label: 'Pending' },
                        { key: 'published', label: 'Live' },
                        { key: 'paused',    label: 'Paused' },
                    ]"
                    :key="tab.key"
                    class="btn btn-sm d-flex align-items-center gap-2"
                    :style="filter === tab.key
                        ? 'background: #1B4332; color: #fff; border: none;'
                        : 'background: #fff; color: #495057; border: 1px solid #dee2e6;'"
                    @click="filter = tab.key"
                >
                    {{ tab.label }}
                    <span
                        class="badge rounded-pill"
                        :style="filter === tab.key
                            ? 'background: rgba(255,255,255,.25); color: #fff;'
                            : 'background: #e9ecef; color: #495057;'"
                        style="font-size: 10px; min-width: 20px;"
                    >
                        {{ counts[tab.key] }}
                    </span>
                </button>
            </div>

            <!-- Loading skeleton -->
            <div v-if="loading" class="d-flex flex-column gap-3">
                <div
                    v-for="n in 4"
                    :key="n"
                    class="card border-0 shadow-sm d-flex flex-row placeholder-glow overflow-hidden"
                    style="border-radius: .75rem; height: 100px;"
                >
                    <div style="width: 120px; background: #e9ecef; flex-shrink: 0;"></div>
                    <div class="card-body d-flex flex-column gap-2 justify-content-center">
                        <span class="placeholder rounded col-4"></span>
                        <span class="placeholder rounded col-3"></span>
                    </div>
                </div>
            </div>

            <!-- Cards -->
            <div v-else-if="filtered.length" class="d-flex flex-column gap-3">
                <div
                    v-for="space in filtered"
                    :key="space.id"
                    class="card border-0 shadow-sm d-flex flex-row overflow-hidden"
                    style="border-radius: .75rem; background: #fff;"
                >
                    <!-- Thumbnail -->
                    <div style="width: 130px; flex-shrink: 0; background: #F7F4EF; overflow: hidden;">
                        <img
                            v-if="space.cover_photo"
                            :src="`/storage/${space.cover_photo}`"
                            :alt="space.title"
                            class="w-100 h-100 object-fit-cover"
                            style="aspect-ratio: 4/3;"
                        />
                        <div
                            v-else
                            class="w-100 h-100 d-flex align-items-center justify-content-center"
                            style="min-height: 100px;"
                        >
                            <span style="font-size: 1.75rem; opacity: .3;">🏢</span>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2 px-4 py-3">
                        <div>
                            <div class="fw-semibold mb-1" style="font-size: 15px; color: #1a1a2e;">
                                {{ space.title }}
                            </div>
                            <div class="text-muted mb-2" style="font-size: 13px;">
                                {{ space.city }}<span v-if="space.country">, {{ space.country }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span
                                    class="badge rounded-pill"
                                    :style="STATUS_STYLE[space.status]"
                                    style="font-size: 11px; font-weight: 500; padding: .35em .75em;"
                                >
                                    {{ STATUS_LABEL[space.status] ?? space.status }}
                                </span>
                                <span class="text-muted" style="font-size: 12px;">
                                    {{ space.workspaces?.length ?? 0 }} workspace type{{ (space.workspaces?.length ?? 0) !== 1 ? 's' : '' }}
                                </span>
                            </div>
                        </div>

                        <div class="text-end flex-shrink-0">
                            <div v-if="minPrice(space.workspaces)" class="fw-bold mb-1" style="font-size: 16px; color: #1B4332;">
                                ${{ minPrice(space.workspaces) }}
                                <span class="fw-normal text-muted" style="font-size: 12px;">/day</span>
                            </div>
                            <RouterLink
                                :to="`/spaces/${space.slug}`"
                                class="btn btn-sm"
                                style="background: #F7F4EF; color: #2D6A4F; border: 1px solid #d1e7dd; font-size: 12px; border-radius: .5rem;"
                                target="_blank"
                            >
                                View public page ↗
                            </RouterLink>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="meta.last_page > 1" class="d-flex justify-content-center mt-4">
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li
                            v-for="page in meta.last_page"
                            :key="page"
                            class="page-item"
                            :class="{ active: page === meta.current_page }"
                        >
                            <button class="page-link" @click="fetchSpaces(page)">{{ page }}</button>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- Empty state -->
            <div v-else-if="!loading" class="text-center py-5">
                <div class="mb-3" style="font-size: 3rem;">🌿</div>
                <p class="fw-semibold mb-1" style="color: #2D6A4F; font-size: 15px;">
                    {{ filter === 'all' ? 'No listings yet' : `No ${filter === 'draft' ? 'pending' : filter} listings` }}
                </p>
                <p class="text-muted" style="font-size: 13px;">
                    {{ filter === 'all' ? 'Your spaces will appear here once created' : 'Try switching the filter above' }}
                </p>
            </div>

        </div>
    </div>
</template>
