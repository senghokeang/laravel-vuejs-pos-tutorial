import { defineStore } from 'pinia'
import router from '../router'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
    }),

    actions: {
        async getUser() {
            try {
                const response = await axios.get('/api/auth/user')
                this.user = response.data
            } catch {
                this.user = null
            }
        },
        logout() {
            axios.post('/api/logout').then(() => { this.user = null; router.push('/login'); });
        }
    }
})