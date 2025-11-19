import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useUser = defineStore(
    'user',
    () => {
        const user = ref({
            accesstoken: '',
            user: {},
            role: [],
            permissions: [],
        })

        const menus = computed(() => {
            return [...new Set(user.value.permissions.map((p) => p.split('.')[0]))]
        })

        function setUser(newUser) {
            user.value = { ...user.value, ...newUser }
        }

        return { user, menus, setUser }
    },
    { persist: true }
)
