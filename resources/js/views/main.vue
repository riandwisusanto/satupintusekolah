<script setup>
import Navbar from '../components/TopBar.vue'
import Sidebar from '../components/Sidebar.vue'
import Footer from '../components/Footer.vue'

import { apiRequest } from '../lib/apiClient'
import { computed, onMounted, ref } from 'vue'
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
        user: user,
        permissions: user.permissions
    })
    

    loading.value = false
}

const bg = ref('')

const loadBackground = async () => {
    const { ok, data } = await apiRequest('configurations/background_image')
    if (ok) {
        bg.value = `/storage/${data.data.background_image}`
    }
}

onMounted(async () => {
    await me()
    await loadBackground()
})

const progressBar = ref(null)

router.beforeEach((to, from, next) => {
    progressBar.value?.start()
    next()
})

router.afterEach(() => {
    progressBar.value?.finish()
})

const backgroundStyle = computed(() => {
    return bg.value
        ? { backgroundImage: `url(${bg.value})` }
        : {}
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
        <div class="content-area" :style="backgroundStyle">
            <div class="overlay"></div>

            <div class="content-wrapper">
                <RouterView />
            </div>
        </div>
        <Footer />
    </div>
</template>

<style scoped>
.app-wrapper {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.content-area {
    position: relative;
    min-height: calc(100vh - 120px);
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    padding: 20px;
    /* HAPUS z-index kalau kamu pernah set */
}

.overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.15); /* bisa kamu sesuaikan */
    backdrop-filter: blur(2px);
    z-index: 1;
}

.content-wrapper {
    position: relative;
    z-index: 2;
}

</style>
