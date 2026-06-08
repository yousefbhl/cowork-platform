import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/api'

export const useBookingStore = defineStore('booking', () => {
    const bookings   = ref([])
    const meta       = ref({})
    const loading    = ref(false)
    const submitting = ref(false)

    async function fetchBookings(params = {}) {
        loading.value = true
        try {
            const { data } = await api.get('/bookings', { params })
            bookings.value = data.data
            meta.value     = data.meta ?? {}
        } finally {
            loading.value = false
        }
    }

    async function fetchAvailability(workspaceId, from, to) {
        const { data } = await api.get(`/workspaces/${workspaceId}/availability`, {
            params: { from, to },
        })
        return data.booked_days ?? []
    }

    async function createBooking(payload) {
        submitting.value = true
        try {
            const { data } = await api.post('/bookings', payload)
            return { success: true, booking: data.data }
        } catch (e) {
            const errors = e.response?.data?.errors
            return {
                success: false,
                message: errors
                    ? Object.values(errors).flat().join(' ')
                    : (e.response?.data?.message ?? 'Booking failed'),
            }
        } finally {
            submitting.value = false
        }
    }

    async function cancelBooking(id) {
        await api.patch(`/bookings/${id}/cancel`)
        await fetchBookings()
    }

    return {
        bookings, meta, loading, submitting,
        fetchBookings, fetchAvailability, createBooking, cancelBooking,
    }
})
