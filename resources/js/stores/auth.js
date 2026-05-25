import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import api from '@/api'

export const useAuthStore = defineStore('auth', () => {

    const user  = ref(null)
    const token = ref(localStorage.getItem('auth_token') || null)

    const isAuthenticated = computed(() => !!user.value)
    const isHost          = computed(() => user.value?.role === 'host')
    const isAdmin         = computed(() => user.value?.role === 'admin')
    const isCustomer      = computed(() => user.value?.role === 'customer')

    async function fetchUser() {
        try {
            const { data } = await api.get('/me')
            user.value = data.user
        } catch {
            user.value = null
        }
    }

    async function login(credentials) {
        const { data } = await api.post('/login', credentials)
        token.value = data.token
        user.value  = data.user
        localStorage.setItem('auth_token', data.token)
    }

    async function register(payload) {
        const { data } = await api.post('/register', payload)
        return data
    }

    async function logout() {
        try {
            await api.post('/logout')
        } finally {
            user.value  = null
            token.value = null
            localStorage.removeItem('auth_token')
        }
    }

    return {
        user,
        token,
        isAuthenticated,
        isHost,
        isAdmin,
        isCustomer,
        fetchUser,
        login,
        register,
        logout,
    }
})
