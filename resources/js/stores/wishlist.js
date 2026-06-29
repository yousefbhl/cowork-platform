import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/api'

export const useWishlistStore = defineStore('wishlist', () => {
    const spaces      = ref([])
    const savedIds    = ref([])
    const loading     = ref(false)
    const initialized = ref(false)

    async function fetchWishlist() {
        loading.value = true
        try {
            const { data } = await api.get('/wishlist')
            spaces.value   = data.data
            savedIds.value = data.data.map(s => s.id)
            initialized.value = true
        } finally {
            loading.value = false
        }
    }

    function ensureLoaded() {
        if (!initialized.value && !loading.value) {
            fetchWishlist()
        }
    }

    function isSaved(spaceId) {
        return savedIds.value.includes(spaceId)
    }

    async function toggle(space) {
        if (isSaved(space.id)) {
            savedIds.value = savedIds.value.filter(id => id !== space.id)
            spaces.value   = spaces.value.filter(s => s.id !== space.id)
            try {
                await api.delete(`/wishlist/${space.slug}`)
            } catch {
                savedIds.value = [...savedIds.value, space.id]
                spaces.value   = [...spaces.value, space]
            }
        } else {
            savedIds.value = [...savedIds.value, space.id]
            spaces.value   = [...spaces.value, space]
            try {
                await api.post(`/wishlist/${space.slug}`)
            } catch {
                savedIds.value = savedIds.value.filter(id => id !== space.id)
                spaces.value   = spaces.value.filter(s => s.id !== space.id)
            }
        }
    }

    return { spaces, savedIds, loading, initialized, isSaved, toggle, fetchWishlist, ensureLoaded }
})
