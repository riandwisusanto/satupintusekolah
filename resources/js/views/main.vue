<script setup>
import Navbar from '../components/TopBar.vue'
import Sidebar from '../components/Sidebar.vue'
import Footer from '../components/Footer.vue'

import { apiRequest } from '../lib/apiClient'
import { onMounted, ref } from 'vue'
import { alertError } from '../lib/alert'
import { useUser } from '../store'
import { useRouter } from 'vue-router'
import ProgressBar from '../components/ProgressBar.vue'

const router = useRouter()
const credential = useUser()
const loading = ref(true)

const me = async () => {
    const { ok, data, error } = await apiRequest('me')

    if (!ok) {
        alertError(error)
        router.push({ name: 'Login' })
        return
    }

    const { user } = data.data
    credential.setUser({
        name: user.name,
        role: user.roles,
        permissions: user.permissions,
        email: user.email,
        employe: user.employe,
    })

    loading.value = false
}

onMounted(me)

const progressBar = ref(null)

router.beforeEach((to, from, next) => {
    progressBar.value?.start()
    next()
})

router.afterEach(() => {
    progressBar.value?.finish()
})
</script>

<template>
    <ProgressBar ref="progressBar" />

    <div v-if="loading" class="flex items-center justify-center h-screen">
        <span>Loading...</span>
    </div>

    <div v-else>
        <Navbar />
        <Sidebar />
        <RouterView />
        <Footer />
    </div>
</template>
