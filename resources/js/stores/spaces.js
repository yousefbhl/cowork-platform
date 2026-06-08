import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/api'

export const useSpacesStore = defineStore('spaces', () => {
    const spaces    = ref([])
    const space     = ref(null)
    const meta      = ref({})
    const amenities = ref([])
    const loading   = ref(false)

    async function fetchSpaces(params = {}) {
        loading.value = true
        try {
            const { data } = await api.get('/spaces', { params })
            spaces.value = data.data
            meta.value   = data.meta
        } finally {
            loading.value = false
        }
    }

    async function fetchSpace(slug) {
        loading.value = true
        space.value = null
        try {
            const { data } = await api.get(`/spaces/${slug}`)
            space.value = data.data
        } catch (e) {
            console.error('Failed to load space:', e.response?.status, slug)
            space.value = null
        } finally {
            loading.value = false
        }
    }

    async function fetchAmenities() {
        if (amenities.value.length) return
        const { data } = await api.get('/amenities')
        amenities.value = data.data
    }

    return { spaces, space, meta, amenities, loading, fetchSpaces, fetchSpace, fetchAmenities }
})
