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
const actionError = ref(null)
const filter      = ref('all')

// ── Status display maps ──────────────────────────────────────────────────────
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

// ── Counts for filter tabs ───────────────────────────────────────────────────
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

// ── Data fetching ────────────────────────────────────────────────────────────
async function fetchSpaces(page = 1) {
    loading.value     = true
    actionError.value = null
    try {
        const { data } = await api.get('/admin/spaces', { params: { page } })
        spaces.value = data.data
        meta.value   = data.meta ?? {}
    } catch (e) {
        actionError.value = 'Failed to load spaces.'
    } finally {
        loading.value = false
    }
}

// ── Actions (optimistic) ─────────────────────────────────────────────────────
async function approve(space) {
    const prev = space.status
    space.status      = 'published'
    actionError.value = null
    try {
        await api.patch(`/admin/spaces/${space.slug}/approve`)
    } catch (e) {
        space.status      = prev
        actionError.value = `Could not approve "${space.title}".`
    }
}

async function reject(space) {
    const prev = space.status
    space.status      = 'paused'
    actionError.value = null
    try {
        await api.patch(`/admin/spaces/${space.slug}/reject`)
    } catch (e) {
        space.status      = prev
        actionError.value = `Could not reject "${space.title}".`
    }
}

async function handleLogout() {
    await auth.logout()
    router.push('/login')
}

onMounted(() => fetchSpaces())
</script>

<template>
    <!-- Nav ──────────────────────────────────────────────────────────────── -->
    <nav class="navbar navbar-light bg-white border-bottom px-4" style="min-height: 57px;">
        <RouterLink to="/admin" class="navbar-brand fw-bold" style="letter-spacing: -.3px; color: #1B4332;">
            CoworkPlatform — Admin
        </RouterLink>
        <div class="d-flex gap-2 align-items-center">
            <RouterLink
                to="/admin/spaces"
                class="btn btn-sm"
                style="background: #2D6A4F; color: #fff; border: none; font-size: 13px;"
            >
                Spaces
            </RouterLink>
            <span class="text-muted d-none d-md-inline" style="font-size: 13px;">{{ auth.user?.name }}</span>
            <RouterLink to="/" class="btn btn-sm btn-outline-secondary">Home</RouterLink>
            <button class="btn btn-sm btn-outline-secondary" @click="handleLogout">Logout</button>
        </div>
    </nav>

    <!-- Page ─────────────────────────────────────────────────────────────── -->
    <div style="background: #F7F4EF; min-height: calc(100vh - 57px);">
        <div class="container py-5">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
                <div>
                    <h1 class="h4 fw-bold mb-1" style="color: #1B4332;">Space Approvals</h1>
                    <p class="text-muted mb-0" style="font-size: 13px;">
                        Review and publish or pause submitted spaces
                    </p>
                </div>
                <span
                    v-if="counts.draft > 0"
                    class="badge rounded-pill align-self-center"
                    style="background: #fef3c7; color: #92400e; font-size: 12px; padding: .4em .85em;"
                >
                    {{ counts.draft }} pending review
                </span>
            </div>

            <!-- Error banner -->
            <div
                v-if="actionError"
                class="alert d-flex justify-content-between align-items-center mb-4 py-2 px-3"
                style="background: #fee2e2; color: #991b1b; border: none; border-radius: .75rem; font-size: 13px;"
            >
                {{ actionError }}
                <button
                    type="button"
                    class="btn-close btn-close-sm ms-3 flex-shrink-0"
                    style="filter: invert(20%) sepia(80%) saturate(600%) hue-rotate(320deg);"
                    @click="actionError = null"
                ></button>
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
            <div v-if="loading" class="card border-0 shadow-sm overflow-hidden" style="border-radius: .75rem;">
                <div
                    v-for="n in 6"
                    :key="n"
                    class="d-flex align-items-center gap-3 px-4 py-3"
                    :class="n < 6 ? 'border-bottom' : ''"
                    style="background: #fff;"
                >
                    <div class="sk" style="width: 32px; height: 32px; border-radius: .5rem; flex-shrink: 0;"></div>
                    <div class="sk" style="height: 14px; width: 24%;"></div>
                    <div class="sk" style="height: 14px; width: 13%;"></div>
                    <div class="sk" style="height: 14px; width: 11%;"></div>
                    <div class="sk ms-auto" style="height: 22px; width: 72px; border-radius: 20px;"></div>
                    <div class="d-flex gap-1">
                        <div class="sk" style="height: 28px; width: 66px; border-radius: 6px;"></div>
                        <div class="sk" style="height: 28px; width: 58px; border-radius: 6px;"></div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div v-else-if="filtered.length" class="card border-0 shadow-sm overflow-hidden" style="border-radius: .75rem;">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead style="background: #F7F4EF;">
                            <tr>
                                <th
                                    v-for="col in ['Space', 'Host', 'City', 'Status', '']"
                                    :key="col"
                                    class="py-3 border-bottom fw-semibold"
                                    :class="col === 'Space' ? 'px-4' : (col === '' ? 'pe-4 text-end' : '')"
                                    style="font-size: 11px; color: #6c757d; letter-spacing: .05em; text-transform: uppercase; white-space: nowrap;"
                                >
                                    {{ col }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="space in filtered" :key="space.id" style="background: #fff;">
                                <!-- Title + slug -->
                                <td class="px-4 py-3" style="min-width: 200px;">
                                    <div class="fw-semibold mb-1" style="font-size: 14px; color: #1a1a2e;">
                                        {{ space.title }}
                                    </div>
                                    <div class="font-monospace" style="font-size: 10px; color: #adb5bd;">
                                        {{ space.slug }}
                                    </div>
                                </td>
                                <!-- Host -->
                                <td class="py-3" style="font-size: 13px; color: #495057; white-space: nowrap;">
                                    {{ space.host?.name ?? '—' }}
                                </td>
                                <!-- City -->
                                <td class="py-3" style="font-size: 13px; color: #495057; white-space: nowrap;">
                                    {{ space.city ?? '—' }}<span v-if="space.country" class="text-muted">, {{ space.country }}</span>
                                </td>
                                <!-- Status badge -->
                                <td class="py-3">
                                    <span
                                        class="badge rounded-pill"
                                        :style="STATUS_STYLE[space.status]"
                                        style="font-size: 11px; font-weight: 500; padding: .4em .85em;"
                                    >
                                        {{ STATUS_LABEL[space.status] ?? space.status }}
                                    </span>
                                </td>
                                <!-- Actions -->
                                <td class="py-3 pe-4 text-end" style="white-space: nowrap;">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button
                                            v-if="space.status !== 'published'"
                                            class="btn btn-sm"
                                            style="background: #2D6A4F; color: #fff; border: none; font-size: 12px; border-radius: .5rem;"
                                            @click="approve(space)"
                                        >
                                            Approve
                                        </button>
                                        <button
                                            v-if="space.status !== 'paused'"
                                            class="btn btn-sm"
                                            style="background: #fff; color: #991b1b; border: 1px solid #fca5a5; font-size: 12px; border-radius: .5rem;"
                                            @click="reject(space)"
                                        >
                                            {{ space.status === 'published' ? 'Unpublish' : 'Reject' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="meta.last_page > 1"
                    class="d-flex justify-content-center py-3 border-top"
                    style="background: #F7F4EF;"
                >
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
            </div>

            <!-- Empty state -->
            <div
                v-else
                class="text-center py-5 d-flex flex-column align-items-center justify-content-center"
                style="min-height: 300px;"
            >
                <div
                    class="d-flex align-items-center justify-content-center rounded-circle mb-4"
                    style="width: 80px; height: 80px; background: #EEEAE3;"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#707973" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                        <rect x="9" y="3" width="6" height="4" rx="1" ry="1"/>
                        <line x1="9" y1="12" x2="15" y2="12"/>
                        <line x1="9" y1="16" x2="11" y2="16"/>
                    </svg>
                </div>
                <h2 class="fw-semibold mb-2" style="font-size: 18px; color: #1A1A18;">
                    {{
                        filter === 'all'       ? 'No spaces submitted yet'  :
                        filter === 'draft'     ? 'No spaces pending review' :
                        filter === 'published' ? 'No spaces are live yet'   :
                                                 'No spaces are paused'
                    }}
                </h2>
                <p style="font-size: 13px; color: #6B6660; max-width: 300px;">
                    {{ filter === 'draft' ? "Hosts haven't submitted any spaces for approval yet." : 'Try switching the filter above.' }}
                </p>
            </div>

        </div>
    </div>
</template>
