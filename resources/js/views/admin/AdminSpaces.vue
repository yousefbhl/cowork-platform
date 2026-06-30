<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/api'
import AdminSidebar from '@/components/AdminSidebar.vue'

const spaces      = ref([])
const meta        = ref({})
const loading     = ref(false)
const actionError = ref(null)
const filter      = ref('all')

const STATUS_LABEL = {
    draft:     'Pending review',
    published: 'Live',
    paused:    'Paused',
}
const STATUS_BADGE = {
    draft:     'lf-badge--pending',
    published: 'lf-badge--confirmed',
    paused:    'lf-badge--completed',
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

onMounted(() => fetchSpaces())

const tabs = [
    { key: 'all',       label: 'All' },
    { key: 'draft',     label: 'Pending' },
    { key: 'published', label: 'Live' },
    { key: 'paused',    label: 'Paused' },
]
</script>

<template>
    <div class="d-flex align-items-stretch bg-linen" style="min-height: 100vh;">
        <AdminSidebar />

        <main class="flex-grow-1 px-4 px-lg-5 py-5" style="min-width: 0;">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
                <div>
                    <h1 class="headline-lg mb-1" style="color: #1A1A18;">Space Approvals</h1>
                    <p class="mb-0" style="font-size: 14px; color: #6B6660;">Review and publish or pause submitted spaces.</p>
                </div>
                <span v-if="counts.draft > 0" class="lf-badge lf-badge--pending align-self-center"><span class="dot"></span>{{ counts.draft }} pending review</span>
            </div>

            <!-- Error banner -->
            <div v-if="actionError" class="d-flex justify-content-between align-items-center mb-4 px-3 py-2" style="background: rgba(192,57,43,.08); color: #7B1A12; border: 1px solid rgba(192,57,43,.2); border-radius: 10px; font-size: 13px;">
                {{ actionError }}
                <button type="button" class="btn-ghost p-0" style="color: #7B1A12;" @click="actionError = null" aria-label="Dismiss">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <!-- Filter pills -->
            <div class="lf-seg d-inline-flex mb-4">
                <button v-for="tab in tabs" :key="tab.key" class="lf-seg__btn d-inline-flex align-items-center gap-2" :class="{ active: filter === tab.key }" @click="filter = tab.key">
                    {{ tab.label }}
                    <span v-if="counts[tab.key]" class="lf-seg__count">{{ counts[tab.key] }}</span>
                </button>
            </div>

            <!-- Loading skeleton -->
            <div v-if="loading" class="lf-card overflow-hidden">
                <div v-for="n in 6" :key="n" class="d-flex align-items-center gap-3 px-4 py-3" :style="n < 6 ? 'border-bottom: 1px solid #E4DFD7;' : ''">
                    <div class="sk" style="width: 36px; height: 36px; border-radius: 8px; flex-shrink: 0;"></div>
                    <div class="sk" style="height: 14px; width: 24%;"></div>
                    <div class="sk" style="height: 14px; width: 13%;"></div>
                    <div class="sk ms-auto" style="height: 22px; width: 90px; border-radius: 12px;"></div>
                    <div class="sk" style="height: 28px; width: 150px; border-radius: 8px;"></div>
                </div>
            </div>

            <!-- Table -->
            <div v-else-if="filtered.length" class="lf-card overflow-hidden">
                <div class="table-responsive">
                    <table class="lf-table">
                        <thead>
                            <tr>
                                <th style="padding-left: 24px;">Space</th>
                                <th>Host</th>
                                <th>City</th>
                                <th>Status</th>
                                <th class="text-end" style="padding-right: 24px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="space in filtered" :key="space.id">
                                <td style="padding-left: 24px; min-width: 220px;">
                                    <div class="fw-semibold mb-1" style="font-size: 14px; color: #1A1A18;">{{ space.title }}</div>
                                    <div class="mono-ref" style="color: #A09890;">{{ space.slug }}</div>
                                </td>
                                <td style="white-space: nowrap; color: #4A4740;">{{ space.host?.name ?? '—' }}</td>
                                <td style="white-space: nowrap; color: #4A4740;">{{ space.city ?? '—' }}<span v-if="space.country" style="color: #A09890;">, {{ space.country }}</span></td>
                                <td>
                                    <span class="lf-badge" :class="STATUS_BADGE[space.status]"><span class="dot"></span>{{ STATUS_LABEL[space.status] ?? space.status }}</span>
                                </td>
                                <td class="text-end" style="padding-right: 24px; white-space: nowrap;">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button v-if="space.status !== 'published'" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" @click="approve(space)">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            Approve
                                        </button>
                                        <button v-if="space.status !== 'paused'" class="btn btn-sm btn-outline-secondary" style="--bs-btn-color: #C0392B; --bs-btn-hover-color: #C0392B; --bs-btn-hover-border-color: #C0392B;" @click="reject(space)">
                                            {{ space.status === 'published' ? 'Unpublish' : 'Reject' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="meta.last_page > 1" class="d-flex justify-content-center py-3" style="border-top: 1px solid #E4DFD7; background: #FAFAF8;">
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li v-for="page in meta.last_page" :key="page" class="page-item" :class="{ active: page === meta.current_page }">
                                <button class="page-link" @click="fetchSpaces(page)">{{ page }}</button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="text-center py-5 d-flex flex-column align-items-center justify-content-center" style="min-height: 320px;">
                <div class="d-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 88px; height: 88px; background: #fff; border: 1px solid #E4DFD7;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#A09890" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1" ry="1"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="11" y2="16"/></svg>
                </div>
                <h2 class="headline-sm mb-2" style="color: #1A1A18;">
                    {{
                        filter === 'all'       ? 'No spaces submitted yet'  :
                        filter === 'draft'     ? 'No spaces pending review' :
                        filter === 'published' ? 'No spaces are live yet'   :
                                                 'No spaces are paused'
                    }}
                </h2>
                <p style="font-size: 14px; color: #6B6660; max-width: 320px;">
                    {{ filter === 'draft' ? "Hosts haven't submitted any spaces for approval yet." : 'Try switching the filter above.' }}
                </p>
            </div>

        </main>
    </div>
</template>

<style scoped>
.lf-seg { background: #EEEAE3; border: 1px solid #E4DFD7; border-radius: 9999px; padding: 4px; }
.lf-seg__btn {
    border: none; background: transparent; color: #6B6660;
    font-size: 13px; font-weight: 600; padding: 6px 16px; border-radius: 9999px;
    cursor: pointer; transition: background .15s, color .15s;
}
.lf-seg__btn.active { background: #2D6A4F; color: #fff; }
.lf-seg__count {
    font-size: 10px; min-width: 18px; height: 18px; padding: 0 5px; border-radius: 9px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(0,0,0,.08); color: inherit;
}
.lf-seg__btn.active .lf-seg__count { background: rgba(255,255,255,.25); }
</style>
