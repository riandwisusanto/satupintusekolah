<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useUser } from '../store.js'
import { alertSuccess, alertError } from '../lib/alert.js'
import { useLocalStorage } from '@vueuse/core'
import { useLoading } from 'vue-loading-overlay'
import { useRouter } from 'vue-router'
import Swal from 'sweetalert2'

const router = useRouter()
const token_load = useUser()
const token = useLocalStorage('token', '')
const credential = useUser()
const $loading = useLoading()

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

const handleLogout = () => {
    Swal.fire({
        title: 'Yakin ingin keluar?',
        text: 'Anda akan keluar dari sistem!',
        icon: 'warning',
        iconHtml: '<i class="fas fa-sign-out-alt" style="font-size:32px;color:#f39c12;"></i>',
        color: '#333',
        showCancelButton: true,
        confirmButtonColor: '#07945f',
        cancelButtonColor: '#d33',
        confirmButtonText: '<i class="fas fa-check"></i> Ya, keluar',
        cancelButtonText: '<i class="fas fa-times"></i> Batal',
        buttonsStyling: false,
        customClass: {
            popup: 'p-4 rounded shadow',
            confirmButton: 'btn btn-success btn-sm mr-2',
            cancelButton: 'btn btn-danger btn-sm',
            title: 'fs-4 fw-bold',
            htmlContainer: 'mb-3',
        },
    }).then(async (result) => {
        if (result.isConfirmed) {
            await logout()
        }
    })
}

const logout = async () => {
    const loader = $loading.show()
    const response = await fetch(`${import.meta.env.VITE_API_PATH}/api/v1/logout`, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${token_load.user.accesstoken}`,
        },
    })

    const responseBody = await response.json()
    loader.hide()

    if (response.status === 200) {
        await alertSuccess(responseBody.message)
        token.value = ''
        router.push({ name: 'Login' })
    } else {
        await alertError(responseBody.message)
    }
}

const profilePhoto = computed(() => {
    if (credential.user.user.photo) {
        return credential.user.user.photo
    }

    return '/public/assets/images/avatar5.png'
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
            <li class="nav-item dropdown user-menu">
                <a
                    href="#"
                    class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                    data-toggle="dropdown"
                >
                    <img
                        :src="profilePhoto"
                        class="rounded-circle shadow"
                        alt="User Image"
                        style="width: 32px; height: 32px; object-fit: cover"
                    />
                    <span class="d-none d-md-inline">{{ credential.user.name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li
                        class="user-header d-flex flex-column align-items-center"
                        style="background: linear-gradient(to bottom, #4a90e2, #7ed6df);"
                    >
                        <img
                            :src="profilePhoto"
                            class="rounded-circle shadow"
                            alt="User Image"
                        />
                        <p style="color: #fff">
                            {{ credential.user.name }} <br />
                            {{ credential.user.email }}
                        </p>
                    </li>
                    <li class="user-footer d-flex px-3 pb-3">
                        <a href="#" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-user"></i> Profile
                        </a>
                        <button
                            class="btn btn-danger btn-sm ml-auto d-flex align-items-center"
                            @click="handleLogout"
                        >
                            <i class="fas fa-sign-out-alt mr-1"></i> Logout
                        </button>
                    </li>
                </ul>
            </li>

            <!-- Fullscreen -->
            <li class="nav-item">
                <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                    <i class="fas fa-expand-arrows-alt"></i>
                </a>
            </li>
        </ul>
    </nav>
</template>
