<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import api from '@/api'
import { photoUrl } from '@/utils/photoUrl'
import HostSidebar from '@/components/HostSidebar.vue'

const auth   = useAuthStore()
const router = useRouter()

const spaces      = ref([])
const meta        = ref({})
const loading     = ref(false)
const filter      = ref('all')

const STATUS_LABEL = {
    draft:     'Draft',
    published: 'Published',
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

function minPrice(workspaces) {
    const prices = (workspaces ?? []).map(w => w.price_daily).filter(Boolean)
    return prices.length ? Math.round(Math.min(...prices)) : null
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

onMounted(() => fetchSpaces())

const tabs = [
    { key: 'all',       label: 'All' },
    { key: 'published', label: 'Published' },
    { key: 'draft',     label: 'Drafts' },
    { key: 'paused',    label: 'Paused' },
]
</script>

<template>
    <div class="d-flex align-items-stretch bg-linen" style="min-height: 100vh;">
        <HostSidebar />

        <main class="flex-grow-1 px-4 px-lg-5 py-5" style="min-width: 0;">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
                <div>
                    <h1 class="headline-lg mb-1" style="color: #1A1A18;">My Listings</h1>
                    <p class="mb-0" style="font-size: 14px; color: #6B6660;">Manage and track the performance of your workspaces.</p>
                </div>
                <RouterLink to="/host/listings/new" class="btn btn-primary px-3 py-2 d-inline-flex align-items-center gap-2">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add new listing
                </RouterLink>
            </div>

            <!-- Filter pills -->
            <div class="lf-seg d-inline-flex mb-4">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    class="lf-seg__btn d-inline-flex align-items-center gap-2"
                    :class="{ active: filter === tab.key }"
                    @click="filter = tab.key"
                >
                    {{ tab.label }}
                    <span v-if="counts[tab.key]" class="lf-seg__count">{{ counts[tab.key] }}</span>
                </button>
            </div>

            <!-- Loading skeleton -->
            <div v-if="loading" class="row g-3 g-lg-4">
                <div v-for="n in 4" :key="n" class="col-12 col-xl-6">
                    <div class="lf-card d-flex overflow-hidden" style="height: 168px;">
                        <div class="sk" style="width: 168px; flex-shrink: 0; border-radius: 0;"></div>
                        <div class="p-4 flex-grow-1 d-flex flex-column gap-2">
                            <div class="sk" style="height: 16px; width: 60%;"></div>
                            <div class="sk" style="height: 12px; width: 40%;"></div>
                            <div class="sk mt-auto" style="height: 32px; width: 100%; border-radius: 8px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cards -->
            <div v-else-if="filtered.length" class="row g-3 g-lg-4">
                <div v-for="space in filtered" :key="space.id" class="col-12 col-xl-6">
                    <div class="lf-listing-card d-flex h-100">
                        <!-- Thumbnail -->
                        <div class="lf-listing-card__photo">
                            <img v-if="space.cover_photo" :src="photoUrl(space.cover_photo)" :alt="space.title" class="w-100 h-100 object-fit-cover" />
                            <div v-else class="w-100 h-100 d-flex align-items-center justify-content-center" style="background: #EEEAE3;">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#B8B2A8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                            </div>
                            <span class="lf-badge position-absolute" :class="STATUS_BADGE[space.status]" style="top: 10px; left: 10px;">{{ STATUS_LABEL[space.status] ?? space.status }}</span>
                        </div>

                        <!-- Body -->
                        <div class="p-3 d-flex flex-column flex-grow-1" style="min-width: 0;">
                            <h3 class="headline-sm mb-1 text-truncate" style="color: #1A1A18;">{{ space.title }}</h3>
                            <p class="mb-2 d-flex align-items-center gap-1" style="font-size: 13px; color: #6B6660;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#A09890" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                {{ space.city }}<template v-if="space.country">, {{ space.country }}</template>
                            </p>
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                <span class="lf-chip">{{ space.workspaces?.length ?? 0 }} workspace type{{ (space.workspaces?.length ?? 0) !== 1 ? 's' : '' }}</span>
                            </div>

                            <div class="mt-auto d-flex justify-content-between align-items-center pt-3" style="border-top: 1px solid #E4DFD7;">
                                <span v-if="minPrice(space.workspaces)" class="mono-price" style="font-size: 15px; color: #1A1A18;">
                                    from {{ minPrice(space.workspaces) }} MAD<span style="font-size: 11px; color: #A09890;">/day</span>
                                </span>
                                <span v-else></span>
                                <RouterLink :to="`/spaces/${space.slug}`" target="_blank" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
                                    View
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                </RouterLink>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="text-center py-5 d-flex flex-column align-items-center justify-content-center" style="min-height: 320px;">
                <div class="d-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 88px; height: 88px; background: #fff; border: 1px solid #E4DFD7;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#A09890" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <h2 class="headline-sm mb-2" style="color: #1A1A18;">
                    {{ filter === 'all' ? 'No listings yet' : `No ${tabs.find(t => t.key === filter)?.label.toLowerCase()} listings` }}
                </h2>
                <p class="mb-4" style="font-size: 14px; color: #6B6660; max-width: 320px;">
                    {{ filter === 'all' ? 'Create your first space to start accepting bookings.' : 'Try switching the filter above.' }}
                </p>
                <RouterLink v-if="filter === 'all'" to="/host/listings/new" class="btn btn-primary px-4 py-2">Create your first listing</RouterLink>
            </div>

            <!-- Pagination -->
            <div v-if="meta.last_page > 1" class="d-flex justify-content-center mt-4">
                <nav>
                    <ul class="pagination mb-0">
                        <li v-for="page in meta.last_page" :key="page" class="page-item" :class="{ active: page === meta.current_page }">
                            <button class="page-link" @click="fetchSpaces(page)">{{ page }}</button>
                        </li>
                    </ul>
                </nav>
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

.lf-listing-card {
    background: #fff; border: 1px solid #E4DFD7; border-radius: 1.125rem; overflow: hidden;
    box-shadow: 0 1px 3px rgba(26,26,24,.06), 0 4px 12px rgba(26,26,24,.04);
    transition: border-color .2s, box-shadow .2s;
}
.lf-listing-card:hover { border-color: rgba(45,106,79,.30); box-shadow: 0 4px 16px rgba(26,26,24,.08); }
.lf-listing-card__photo { position: relative; width: 150px; flex-shrink: 0; background: #E4DFD7; }
</style>
