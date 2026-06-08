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
function isStart(date)   { return date && startDate.value && toISO(date) === toISO(startDate.value) }
function isEnd(date)     { return date && endDate.value   && toISO(date) === toISO(endDate.value) }
function isInRange(date) {
    if (!date || !startDate.value || !endDate.value) return false
    return date > startDate.value && date < endDate.value
}

function selectDate(date) {
    if (!date || isBooked(date) || isPast(date)) return

    // First click or re-start after a completed selection
    if (!startDate.value || (startDate.value && endDate.value)) {
        startDate.value = date
        endDate.value   = null
        return
    }

    // Second click must be after start
    if (date <= startDate.value) {
        startDate.value = date
        return
    }

    // Reject range that spans a booked day
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
    <div class="calendar">
        <!-- Nav -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <button class="btn btn-sm btn-light px-2" @click="prevMonth">‹</button>
            <span class="fw-semibold" style="font-size: 14px;">{{ monthLabel }}</span>
            <button class="btn btn-sm btn-light px-2" @click="nextMonth">›</button>
        </div>

        <!-- Weekday headers -->
        <div class="cal-grid mb-1">
            <div
                v-for="d in ['Su','Mo','Tu','We','Th','Fr','Sa']"
                :key="d"
                class="text-center text-muted"
                style="font-size: 10px; font-weight: 600; padding-bottom: 4px;"
            >{{ d }}</div>
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
        <div class="d-flex gap-3 mt-3" style="font-size: 11px; color: #6c757d;">
            <span class="d-flex align-items-center gap-1">
                <span class="leg-dot" style="background: #1a1a2e;"></span> Selected
            </span>
            <span class="d-flex align-items-center gap-1">
                <span class="leg-dot" style="background: #dee2e6;"></span> Unavailable
            </span>
        </div>
    </div>
</template>

<style scoped>
.cal-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
}
.cal-cell {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: background .1s;
    user-select: none;
}
.cal-cell:not(.cal-cell--empty):not(.cal-cell--booked):not(.cal-cell--past):hover {
    background: #f1f0ea;
}
.cal-cell--empty  { cursor: default; }
.cal-cell--booked { background: #e9ecef; color: #adb5bd; text-decoration: line-through; cursor: not-allowed; }
.cal-cell--past   { color: #ced4da; cursor: not-allowed; }
.cal-cell--start,
.cal-cell--end    { background: #1a1a2e !important; color: #fff; font-weight: 600; border-radius: 6px; }
.cal-cell--range  { background: #d6d5e8; border-radius: 0; }
.leg-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
</style>
