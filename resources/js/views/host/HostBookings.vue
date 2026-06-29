<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import api from '@/api'

const auth   = useAuthStore()
const router = useRouter()

const bookings    = ref([])
const meta        = ref({})
const loading     = ref(false)
const actionError = ref(null)
const filter      = ref('all')

const STATUS_LABEL = {
    pending:   'Pending',
    confirmed: 'Confirmed',
    cancelled: 'Cancelled',
    completed: 'Completed',
}
const STATUS_STYLE = {
    pending:   'color: #92400e; background: #fef3c7;',
    confirmed: 'color: #1B4332; background: #dcfce7;',
    cancelled: 'color: #991b1b; background: #fee2e2;',
    completed: 'color: #374151; background: #f3f4f6;',
}

const counts = computed(() => ({
    all:       bookings.value.length,
    pending:   bookings.value.filter(b => b.status === 'pending').length,
    confirmed: bookings.value.filter(b => b.status === 'confirmed').length,
    cancelled: bookings.value.filter(b => b.status === 'cancelled').length,
}))

const filtered = computed(() =>
    filter.value === 'all'
        ? bookings.value
        : bookings.value.filter(b => b.status === filter.value)
)

async function fetchBookings(page = 1) {
    loading.value     = true
    actionError.value = null
    try {
        const { data } = await api.get('/host/bookings', { params: { page } })
        bookings.value = data.data
        meta.value     = data.meta ?? {}
    } catch (e) {
        actionError.value = 'Failed to load bookings.'
    } finally {
        loading.value = false
    }
}

async function confirm(booking) {
    const prev = booking.status
    booking.status    = 'confirmed'
    actionError.value = null
    try {
        await api.patch(`/host/bookings/${booking.id}/confirm`)
    } catch (e) {
        booking.status    = prev
        actionError.value = `Could not confirm booking #${booking.id}.`
    }
}

async function cancel(booking) {
    const prev = booking.status
    booking.status    = 'cancelled'
    actionError.value = null
    try {
        await api.patch(`/host/bookings/${booking.id}/cancel`)
    } catch (e) {
        booking.status    = prev
        actionError.value = `Could not cancel booking #${booking.id}.`
    }
}

async function handleLogout() {
    await auth.logout()
    router.push('/login')
}

onMounted(() => fetchBookings())
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
                class="btn btn-sm btn-outline-secondary"
                style="font-size: 13px;"
            >
                My Listings
            </RouterLink>
            <RouterLink
                to="/host/bookings"
                class="btn btn-sm"
                style="background: #2D6A4F; color: #fff; border: none; font-size: 13px;"
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
                    <h1 class="h4 fw-bold mb-1" style="color: #1B4332;">Booking Management</h1>
                    <p class="text-muted mb-0" style="font-size: 13px;">
                        Manage reservations across all your spaces
                    </p>
                </div>
                <span
                    v-if="counts.pending > 0"
                    class="badge rounded-pill align-self-center"
                    style="background: #fef3c7; color: #92400e; font-size: 12px; padding: .4em .85em;"
                >
                    {{ counts.pending }} awaiting action
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
                        { key: 'pending',   label: 'Pending' },
                        { key: 'confirmed', label: 'Confirmed' },
                        { key: 'cancelled', label: 'Cancelled' },
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
                    v-for="n in 5"
                    :key="n"
                    class="d-flex align-items-center gap-3 px-4 py-3"
                    :class="n < 5 ? 'border-bottom' : ''"
                    style="background: #fff;"
                >
                    <div class="sk" style="height: 14px; width: 22%;"></div>
                    <div class="sk" style="height: 14px; width: 13%;"></div>
                    <div class="sk" style="height: 14px; width: 17%;"></div>
                    <div class="sk ms-auto" style="height: 22px; width: 72px; border-radius: 20px;"></div>
                    <div class="sk" style="height: 14px; width: 54px;"></div>
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
                                    v-for="col in ['Space', 'Customer', 'Dates', 'Status', 'Total', '']"
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
                            <tr v-for="booking in filtered" :key="booking.id" style="background: #fff;">
                                <!-- Space -->
                                <td class="px-4 py-3" style="min-width: 180px;">
                                    <div class="fw-semibold" style="font-size: 14px; color: #1a1a2e;">
                                        {{ booking.workspace?.space?.title ?? '—' }}
                                    </div>
                                    <div class="text-muted" style="font-size: 12px;">
                                        {{ booking.workspace?.type?.label ?? '' }}
                                    </div>
                                </td>
                                <!-- Customer -->
                                <td class="py-3" style="font-size: 13px; color: #495057; white-space: nowrap;">
                                    {{ booking.customer?.name ?? '—' }}
                                </td>
                                <!-- Dates -->
                                <td class="py-3 font-monospace" style="font-size: 12px; color: #495057; white-space: nowrap;">
                                    {{ booking.start_date }} → {{ booking.end_date }}
                                </td>
                                <!-- Status -->
                                <td class="py-3">
                                    <span
                                        class="badge rounded-pill"
                                        :style="STATUS_STYLE[booking.status]"
                                        style="font-size: 11px; font-weight: 500; padding: .4em .85em;"
                                    >
                                        {{ STATUS_LABEL[booking.status] ?? booking.status }}
                                    </span>
                                </td>
                                <!-- Total -->
                                <td class="py-3 fw-semibold" style="font-size: 13px; color: #1B4332; white-space: nowrap;">
                                    ${{ booking.total_amount }}
                                </td>
                                <!-- Actions -->
                                <td class="py-3 pe-4 text-end" style="white-space: nowrap;">
                                    <div v-if="booking.status === 'pending'" class="d-flex gap-2 justify-content-end">
                                        <button
                                            class="btn btn-sm"
                                            style="background: #2D6A4F; color: #fff; border: none; font-size: 12px; border-radius: .5rem;"
                                            @click="confirm(booking)"
                                        >
                                            Confirm
                                        </button>
                                        <button
                                            class="btn btn-sm"
                                            style="background: #fff; color: #991b1b; border: 1px solid #fca5a5; font-size: 12px; border-radius: .5rem;"
                                            @click="cancel(booking)"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                    <span v-else class="text-muted" style="font-size: 12px;">—</span>
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
                                <button class="page-link" @click="fetchBookings(page)">{{ page }}</button>
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
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
                <h2 class="fw-semibold mb-2" style="font-size: 18px; color: #1A1A18;">
                    {{
                        filter === 'all'       ? 'No bookings yet'       :
                        filter === 'pending'   ? 'No pending bookings'   :
                        filter === 'confirmed' ? 'No confirmed bookings' :
                                                 'No cancelled bookings'
                    }}
                </h2>
                <p style="font-size: 13px; color: #6B6660; max-width: 300px;">
                    {{ filter === 'all' ? 'Customer reservations will appear here once guests start booking your spaces.' : 'Try switching the filter above.' }}
                </p>
            </div>

        </div>
    </div>
</template>
