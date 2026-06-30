<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useBookingStore } from '@/stores/booking'

const props = defineProps({
    workspaceId: { type: Number, required: true },
})
const emit = defineEmits(['range-selected'])

const bookingStore = useBookingStore()
const current      = ref(new Date())
const bookedDays   = ref([])
const startDate    = ref(null)
const endDate      = ref(null)

const monthLabel = computed(() =>
    current.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
)

const days = computed(() => {
    const year     = current.value.getFullYear()
    const month    = current.value.getMonth()
    const first    = new Date(year, month, 1)
    const last     = new Date(year, month + 1, 0)
    const startPad = first.getDay()
    const grid     = []

    for (let i = 0; i < startPad; i++) grid.push(null)
    for (let d = 1; d <= last.getDate(); d++) grid.push(new Date(year, month, d))

    return grid
})

function toISO(date) {
    return date.toISOString().split('T')[0]
}

function isBooked(date)  { return date && bookedDays.value.includes(toISO(date)) }
function isPast(date) {
    if (!date) return false
    const today = new Date(); today.setHours(0, 0, 0, 0)
    return date < today
}
function isToday(date) {
    if (!date) return false
    const today = new Date()
    return toISO(date) === toISO(today)
}
function isStart(date)   { return date && startDate.value && toISO(date) === toISO(startDate.value) }
function isEnd(date)     { return date && endDate.value   && toISO(date) === toISO(endDate.value) }
function isInRange(date) {
    if (!date || !startDate.value || !endDate.value) return false
    return date > startDate.value && date < endDate.value
}

function selectDate(date) {
    if (!date || isBooked(date) || isPast(date)) return

    if (!startDate.value || (startDate.value && endDate.value)) {
        startDate.value = date
        endDate.value   = null
        return
    }

    if (date <= startDate.value) {
        startDate.value = date
        return
    }

    const blocked = bookedDays.value.some(bd => {
        const d = new Date(bd)
        return d >= startDate.value && d < date
    })
    if (blocked) {
        startDate.value = date
        endDate.value   = null
        return
    }

    endDate.value = date
    emit('range-selected', {
        start_date: toISO(startDate.value),
        end_date:   toISO(endDate.value),
    })
}

async function loadAvailability() {
    const year  = current.value.getFullYear()
    const month = current.value.getMonth()
    const from  = toISO(new Date(year, month, 1))
    const to    = toISO(new Date(year, month + 1, 0))
    bookedDays.value = await bookingStore.fetchAvailability(props.workspaceId, from, to)
}

function prevMonth() {
    current.value = new Date(current.value.getFullYear(), current.value.getMonth() - 1, 1)
}
function nextMonth() {
    current.value = new Date(current.value.getFullYear(), current.value.getMonth() + 1, 1)
}

watch(current, loadAvailability)
onMounted(loadAvailability)
</script>

<template>
    <div class="lf-calendar">
        <!-- Nav -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <button class="lf-cal-nav" aria-label="Previous month" @click="prevMonth">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <span class="fw-semibold" style="font-size: 15px; color: #1A1A18;">{{ monthLabel }}</span>
            <button class="lf-cal-nav" aria-label="Next month" @click="nextMonth">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>

        <!-- Weekday headers -->
        <div class="cal-grid mb-1">
            <div v-for="d in ['Su','Mo','Tu','We','Th','Fr','Sa']" :key="d" class="text-center label-caps" style="color: #A09890; padding-bottom: 6px;">{{ d }}</div>
        </div>

        <!-- Day cells -->
        <div class="cal-grid">
            <div
                v-for="(date, i) in days"
                :key="i"
                class="cal-cell"
                :class="{
                    'cal-cell--empty':  !date,
                    'cal-cell--booked': isBooked(date),
                    'cal-cell--past':   isPast(date),
                    'cal-cell--today':  isToday(date) && !isStart(date) && !isEnd(date),
                    'cal-cell--start':  isStart(date),
                    'cal-cell--end':    isEnd(date),
                    'cal-cell--range':  isInRange(date),
                }"
                @click="selectDate(date)"
            >
                <span v-if="date">{{ date.getDate() }}</span>
            </div>
        </div>

        <!-- Legend -->
        <div class="d-flex gap-3 mt-3" style="font-size: 11px; color: #6B6660;">
            <span class="d-flex align-items-center gap-1"><span class="leg-dot" style="background: #2D6A4F;"></span> Selected</span>
            <span class="d-flex align-items-center gap-1"><span class="leg-dot" style="background: #D4CEC6;"></span> Unavailable</span>
        </div>
    </div>
</template>

<style scoped>
.cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; }
.lf-cal-nav {
    width: 30px; height: 30px; border-radius: 8px;
    border: 1px solid #E4DFD7; background: #fff; color: #6B6660;
    display: inline-flex; align-items: center; justify-content: center; cursor: pointer;
    transition: border-color .12s, color .12s;
}
.lf-cal-nav:hover { border-color: #B8B2A8; color: #1A1A18; }
.cal-cell {
    aspect-ratio: 1; display: flex; align-items: center; justify-content: center;
    font-size: 14px; color: #1A1A18; border-radius: 8px; cursor: pointer;
    transition: background .1s; user-select: none;
}
.cal-cell:not(.cal-cell--empty):not(.cal-cell--booked):not(.cal-cell--past):hover { background: rgba(45,106,79,.10); }
.cal-cell--empty  { cursor: default; }
.cal-cell--booked { color: #D4CEC6; text-decoration: line-through; cursor: not-allowed; }
.cal-cell--past   { color: #D4CEC6; cursor: not-allowed; }
.cal-cell--today  { box-shadow: inset 0 -2px 0 #D4930A; }
.cal-cell--start, .cal-cell--end { background: #2D6A4F !important; color: #fff; font-weight: 600; }
.cal-cell--range  { background: rgba(45,106,79,.12); color: #1B4332; border-radius: 0; }
.leg-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
</style>
