<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/api'
import HostSidebar from '@/components/HostSidebar.vue'

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
const STATUS_BADGE = {
    pending:   'lf-badge--pending',
    confirmed: 'lf-badge--confirmed',
    cancelled: 'lf-badge--cancelled',
    completed: 'lf-badge--completed',
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

onMounted(() => fetchBookings())

const tabs = [
    { key: 'all',       label: 'All' },
    { key: 'pending',   label: 'Pending' },
    { key: 'confirmed', label: 'Confirmed' },
    { key: 'cancelled', label: 'Cancelled' },
]

function fmtDate(d) {
    return new Date(d).toLocaleDateString('en-US', { day: 'numeric', month: 'short' })
}
</script>

<template>
    <div class="d-flex align-items-stretch bg-linen" style="min-height: 100vh;">
        <HostSidebar />

        <main class="flex-grow-1 px-4 px-lg-5 py-5" style="min-width: 0;">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
                <div>
                    <h1 class="headline-lg mb-1" style="color: #1A1A18;">Bookings</h1>
                    <p class="mb-0" style="font-size: 14px; color: #6B6660;">Manage reservations across all your spaces.</p>
                </div>
                <span v-if="counts.pending > 0" class="lf-badge lf-badge--pending align-self-center"><span class="dot"></span>{{ counts.pending }} awaiting action</span>
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
                <div v-for="n in 5" :key="n" class="d-flex align-items-center gap-3 px-4 py-3" :style="n < 5 ? 'border-bottom: 1px solid #E4DFD7;' : ''">
                    <div class="sk" style="height: 14px; width: 22%;"></div>
                    <div class="sk" style="height: 14px; width: 14%;"></div>
                    <div class="sk" style="height: 14px; width: 18%;"></div>
                    <div class="sk ms-auto" style="height: 22px; width: 80px; border-radius: 12px;"></div>
                    <div class="sk" style="height: 28px; width: 130px; border-radius: 8px;"></div>
                </div>
            </div>

            <!-- Table -->
            <div v-else-if="filtered.length" class="lf-card overflow-hidden">
                <div class="table-responsive">
                    <table class="lf-table">
                        <thead>
                            <tr>
                                <th style="padding-left: 24px;">Space</th>
                                <th>Customer</th>
                                <th>Dates</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th class="text-end" style="padding-right: 24px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="booking in filtered" :key="booking.id">
                                <td style="padding-left: 24px; min-width: 180px;">
                                    <div class="fw-semibold" style="font-size: 14px; color: #1A1A18;">{{ booking.workspace?.space?.title ?? '—' }}</div>
                                    <div style="font-size: 12px; color: #6B6660;">{{ booking.workspace?.type?.label ?? '' }}</div>
                                </td>
                                <td style="white-space: nowrap; color: #4A4740;">{{ booking.customer?.name ?? '—' }}</td>
                                <td class="num" style="font-size: 12px; color: #4A4740; white-space: nowrap;">{{ fmtDate(booking.start_date) }} → {{ fmtDate(booking.end_date) }}</td>
                                <td>
                                    <span class="lf-badge" :class="STATUS_BADGE[booking.status]"><span class="dot"></span>{{ STATUS_LABEL[booking.status] ?? booking.status }}</span>
                                </td>
                                <td class="num fw-semibold" style="color: #1A1A18; white-space: nowrap;">{{ Math.round(booking.total_amount) }} {{ booking.currency || 'MAD' }}</td>
                                <td class="text-end" style="padding-right: 24px; white-space: nowrap;">
                                    <div v-if="booking.status === 'pending'" class="d-flex gap-2 justify-content-end">
                                        <button class="btn btn-sm btn-primary" @click="confirm(booking)">Confirm</button>
                                        <button class="btn btn-sm btn-outline-secondary" style="--bs-btn-color: #C0392B; --bs-btn-hover-color: #C0392B; --bs-btn-hover-border-color: #C0392B;" @click="cancel(booking)">Cancel</button>
                                    </div>
                                    <span v-else style="font-size: 12px; color: #A09890;">—</span>
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
                                <button class="page-link" @click="fetchBookings(page)">{{ page }}</button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="text-center py-5 d-flex flex-column align-items-center justify-content-center" style="min-height: 320px;">
                <div class="d-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 88px; height: 88px; background: #fff; border: 1px solid #E4DFD7;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#A09890" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <h2 class="headline-sm mb-2" style="color: #1A1A18;">
                    {{ filter === 'all' ? 'No bookings yet' : `No ${tabs.find(t => t.key === filter)?.label.toLowerCase()} bookings` }}
                </h2>
                <p style="font-size: 14px; color: #6B6660; max-width: 320px;">
                    {{ filter === 'all' ? 'Customer reservations will appear here once guests start booking your spaces.' : 'Try switching the filter above.' }}
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
