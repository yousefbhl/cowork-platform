<script setup>
import { ref, watch } from 'vue'
import { useSpacesStore } from '@/stores/spaces'

const emit  = defineEmits(['filter', 'chips'])
const store = useSpacesStore()

const PRICE_MAX = 2000

const filters = ref({
    city: '',
    type: '',
    amenities: [],
    priceMax: PRICE_MAX,
})

const types = [
    { name: 'hot_desk',       label: 'Hot Desk' },
    { name: 'dedicated_desk', label: 'Dedicated Desk' },
    { name: 'private_office', label: 'Private Office' },
    { name: 'meeting_room',   label: 'Meeting Room' },
]

store.fetchAmenities()

function buildParams() {
    const params = {}
    if (filters.value.city)             params.city      = filters.value.city
    if (filters.value.type)             params.type      = filters.value.type
    if (filters.value.amenities.length) params.amenities = filters.value.amenities
    if (filters.value.priceMax < PRICE_MAX) params.price = `0,${filters.value.priceMax}`
    return params
}

function buildChips() {
    const chips = []
    if (filters.value.city)
        chips.push({ key: 'city', label: filters.value.city })
    if (filters.value.type)
        chips.push({ key: 'type', label: types.find(t => t.name === filters.value.type)?.label })
    if (filters.value.priceMax < PRICE_MAX)
        chips.push({ key: 'price', label: `Under ${filters.value.priceMax} MAD` })
    filters.value.amenities.forEach(id => {
        const a = store.amenities.find(x => x.id === id)
        if (a) chips.push({ key: `amenity:${id}`, label: a.name })
    })
    return chips
}

let timer = null
function apply() {
    clearTimeout(timer)
    timer = setTimeout(() => {
        emit('filter', buildParams())
        emit('chips', buildChips())
    }, 250)
}

watch(filters, apply, { deep: true })

function selectType(name) {
    filters.value.type = filters.value.type === name ? '' : name
}

function toggleAmenity(id) {
    const idx = filters.value.amenities.indexOf(id)
    if (idx === -1) filters.value.amenities.push(id)
    else filters.value.amenities.splice(idx, 1)
}

function clearField(key) {
    if (key === 'type')  filters.value.type = ''
    else if (key === 'price') filters.value.priceMax = PRICE_MAX
    else if (key === 'city')  filters.value.city = ''
    else if (key.startsWith('amenity:')) {
        const id = Number(key.split(':')[1])
        filters.value.amenities = filters.value.amenities.filter(x => x !== id)
    }
}

function clearAll() {
    filters.value = { city: '', type: '', amenities: [], priceMax: PRICE_MAX }
}

defineExpose({ clearField, clearAll })
</script>

<template>
    <aside class="lf-filters bg-linen">
        <h2 class="headline-sm mb-4" style="color: #1A1A18;">Filters</h2>

        <!-- Location -->
        <div class="mb-4">
            <label class="label-caps d-block mb-2" style="color: #6B6660;">Location</label>
            <div class="position-relative">
                <svg class="position-absolute" style="left: 12px; top: 50%; transform: translateY(-50%);" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#A09890" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <input v-model="filters.city" type="text" class="form-control ps-5" placeholder="City, e.g. Casablanca" />
            </div>
        </div>

        <!-- Workspace Type -->
        <div class="mb-4 pt-1">
            <h3 class="label-caps mb-3" style="color: #6B6660;">Workspace Type</h3>
            <div class="d-flex flex-column gap-2">
                <button
                    v-for="t in types"
                    :key="t.name"
                    type="button"
                    class="lf-filter-row"
                    :class="{ active: filters.type === t.name }"
                    @click="selectType(t.name)"
                >
                    <span class="lf-check" :class="{ checked: filters.type === t.name }">
                        <svg v-if="filters.type === t.name" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </span>
                    {{ t.label }}
                </button>
            </div>
        </div>

        <!-- Price Range -->
        <div class="mb-4 pt-1">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="label-caps mb-0" style="color: #6B6660;">Price Range (MAD)</h3>
                <span class="mono-sm text-forest">0 – {{ filters.priceMax === PRICE_MAX ? 'Any' : filters.priceMax }}</span>
            </div>
            <input
                v-model.number="filters.priceMax"
                type="range" min="100" :max="PRICE_MAX" step="50"
                class="lf-range w-100"
            />
        </div>

        <!-- Amenities -->
        <div v-if="store.amenities.length" class="pt-1">
            <h3 class="label-caps mb-3" style="color: #6B6660;">Amenities</h3>
            <div class="d-flex flex-column gap-2">
                <button
                    v-for="a in store.amenities.slice(0, 8)"
                    :key="a.id"
                    type="button"
                    class="lf-filter-row"
                    :class="{ active: filters.amenities.includes(a.id) }"
                    @click="toggleAmenity(a.id)"
                >
                    <span class="lf-check" :class="{ checked: filters.amenities.includes(a.id) }">
                        <svg v-if="filters.amenities.includes(a.id)" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </span>
                    {{ a.name }}
                </button>
            </div>
        </div>
    </aside>
</template>

<style scoped>
.lf-filters {
    width: 100%;
    padding: 24px 20px;
    border-bottom: 1px solid #D4CEC6;
}
@media (min-width: 992px) {
    .lf-filters {
        width: 260px;
        flex-shrink: 0;
        padding: 28px 24px;
        border-right: 1px solid #D4CEC6;
        border-bottom: none;
        align-self: stretch;
    }
}
.lf-filter-row {
    display: flex; align-items: center; gap: 12px;
    background: transparent; border: none; padding: 4px 0;
    font-size: 15px; color: #6B6660; text-align: left; cursor: pointer;
    transition: color .12s ease;
}
.lf-filter-row.active { color: #1A1A18; font-weight: 500; }
.lf-filter-row:hover { color: #1A1A18; }
.lf-check {
    width: 18px; height: 18px; border-radius: 5px;
    border: 1px solid #D4CEC6; background: #fff;
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0; transition: background .12s ease, border-color .12s ease;
}
.lf-filter-row:hover .lf-check { border-color: #2D6A4F; }
.lf-check.checked { background: #2D6A4F; border-color: #2D6A4F; }

.lf-range { -webkit-appearance: none; appearance: none; height: 4px; background: #D4CEC6; border-radius: 2px; outline: none; }
.lf-range::-webkit-slider-thumb {
    -webkit-appearance: none; appearance: none;
    width: 18px; height: 18px; border-radius: 50%;
    background: #2D6A4F; cursor: pointer;
    box-shadow: 0 0 0 2px #F7F4EF;
}
.lf-range::-moz-range-thumb {
    width: 18px; height: 18px; border-radius: 50%; border: none;
    background: #2D6A4F; cursor: pointer; box-shadow: 0 0 0 2px #F7F4EF;
}
.lf-range:focus::-webkit-slider-thumb { box-shadow: 0 0 0 3px rgba(45,106,79,.18), 0 0 0 2px #F7F4EF; }
</style>
