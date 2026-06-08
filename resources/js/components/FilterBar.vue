<script setup>
import { ref } from 'vue'
import { useSpacesStore } from '@/stores/spaces'

const emit = defineEmits(['filter'])
const store = useSpacesStore()

const filters = ref({
    city: '',
    type: '',
    amenities: [],
    price: '',
})

const types = [
    { name: 'hot_desk',       label: 'Hot Desk' },
    { name: 'dedicated_desk', label: 'Dedicated Desk' },
    { name: 'private_office', label: 'Private Office' },
    { name: 'meeting_room',   label: 'Meeting Room' },
]

store.fetchAmenities()

function apply() {
    const params = {}
    if (filters.value.city)               params.city      = filters.value.city
    if (filters.value.type)               params.type      = filters.value.type
    if (filters.value.amenities.length)   params.amenities = filters.value.amenities
    if (filters.value.price)              params.price     = filters.value.price
    emit('filter', params)
}

function reset() {
    filters.value = { city: '', type: '', amenities: [], price: '' }
    emit('filter', {})
}

function toggleAmenity(id) {
    const idx = filters.value.amenities.indexOf(id)
    if (idx === -1) filters.value.amenities.push(id)
    else filters.value.amenities.splice(idx, 1)
}
</script>

<template>
    <div class="filter-bar bg-white border-bottom py-3 sticky-top">
        <div class="container">
            <div class="row g-2 align-items-end">

                <div class="col-12 col-md-3">
                    <label class="form-label small mb-1">City</label>
                    <input
                        v-model="filters.city"
                        type="text"
                        class="form-control form-control-sm"
                        placeholder="Casablanca, Paris…"
                        @keyup.enter="apply"
                    />
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">Type</label>
                    <select v-model="filters.type" class="form-select form-select-sm">
                        <option value="">All types</option>
                        <option v-for="t in types" :key="t.name" :value="t.name">{{ t.label }}</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">Max price / day</label>
                    <select v-model="filters.price" class="form-select form-select-sm">
                        <option value="">Any price</option>
                        <option value="0,25">Under $25</option>
                        <option value="0,50">Under $50</option>
                        <option value="0,100">Under $100</option>
                        <option value="100,999">$100+</option>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label small mb-1">Amenities</label>
                    <div class="d-flex flex-wrap gap-1">
                        <button
                            v-for="amenity in store.amenities.slice(0, 5)"
                            :key="amenity.id"
                            type="button"
                            class="btn px-2 py-1"
                            :class="filters.amenities.includes(amenity.id)
                                ? 'btn-primary'
                                : 'btn-outline-secondary'"
                            style="font-size: 11px; border-radius: 20px; line-height: 1.4;"
                            @click="toggleAmenity(amenity.id)"
                        >
                            {{ amenity.name }}
                        </button>
                    </div>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button class="btn btn-primary btn-sm flex-grow-1" @click="apply">Search</button>
                    <button class="btn btn-outline-secondary btn-sm" @click="reset">Reset</button>
                </div>

            </div>
        </div>
    </div>
</template>
