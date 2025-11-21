<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import UserProfileDropDown from './UserProfileDropDown.vue'

const isOnline = ref(navigator.onLine)
const isSlowConnection = ref(false)

const checkConnectionSpeed = async () => {
    if (!navigator.onLine) {
        isSlowConnection.value = false
        return
    }

    const start = performance.now()
    try {
        // Request ringan untuk ukur kecepatan koneksi
        await fetch('/favicon.ico', { method: 'GET', cache: 'no-store' })
        const duration = performance.now() - start
        isSlowConnection.value = duration > 1000 // threshold 1 detik
    } catch (err) {
        isSlowConnection.value = true
    }
}

const updateOnlineStatus = () => {
    isOnline.value = navigator.onLine
    checkConnectionSpeed()
}

onMounted(() => {
    window.addEventListener('online', updateOnlineStatus)
    window.addEventListener('offline', updateOnlineStatus)
    checkConnectionSpeed()
    setInterval(checkConnectionSpeed, 10000) // Cek setiap 30 detik
})

onUnmounted(() => {
    window.removeEventListener('online', updateOnlineStatus)
    window.removeEventListener('offline', updateOnlineStatus)
})


</script>

<template>
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto align-items-center">
            <!-- Status Koneksi -->
            <li class="nav-item d-flex align-items-center mr-3" title="Status Koneksi">
                <span v-if="!isOnline" class="text-secondary d-flex align-items-center gap-1">
                    <i class="fas fa-wifi"></i>
                    <small class="d-none d-sm-inline"></small>
                </span>
                <span
                    v-else-if="isSlowConnection"
                    class="text-warning d-flex align-items-center gap-1"
                >
                    <i class="fas fa-wifi"></i>
                    <small class="d-none d-sm-inline"></small>
                </span>
                <span v-else class="text-success d-flex align-items-center gap-1">
                    <i class="fas fa-wifi"></i>
                    <small class="d-none d-sm-inline"></small>
                </span>
            </li>

            <!-- User -->
            <UserProfileDropDown />

            <!-- Fullscreen -->
            <li class="nav-item">
                <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                    <i class="fas fa-expand-arrows-alt"></i>
                </a>
            </li>
        </ul>
    </nav>
</template>

<style scoped>
.main-header {
    position: sticky;
    top: 0;
    z-index: 1050;
    background: #ffffff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}
.navbar {
    background: #ffffff !important;
    color: #2d3436 !important;
    border-bottom: 2px solid #001f3f;
}
</style>
