<template>
    <li class="nav-item dropdown user-menu">
        <a
            href="#"
            class="nav-link dropdown-toggle d-flex align-items-center gap-2"
            data-toggle="dropdown"
        >
            <img
                :src="photo"
                class="rounded-circle shadow"
                alt="User Image"
                style="width: 32px; height: 32px; object-fit: cover"
            />
            <span class="d-none d-md-inline">{{ user.name }}</span>
        </a>

        <ul class="dropdown-menu dropdown-menu-right">
            <li
                class="user-header d-flex flex-column align-items-center"
                style="background: linear-gradient(to bottom, #4a90e2, #7ed6df);"
            >
                <img :src="photo" class="rounded-circle shadow" alt="User Image" />
                <p style="color: white" class="mt-2 text-center">
                    {{ user.name }} <br />
                    <small>{{ user.email }}</small>
                </p>
            </li>

            <li class="user-footer d-flex px-3 pb-3">
                <a href="#" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-user"></i> Profile
                </a>

                <button
                    class="btn btn-danger btn-sm ml-auto d-flex align-items-center"
                    @click="onLogout"
                >
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </button>
            </li>
        </ul>
    </li>
</template>

<script setup>
import { computed } from 'vue'
import { useUser } from '../store.js'
import { alertSuccess, alertError } from '../lib/alert.js'
import { useLocalStorage } from '@vueuse/core'
import { useLoading } from 'vue-loading-overlay'
import { useRouter } from 'vue-router'
import Swal from 'sweetalert2'

const router = useRouter()
const credential = useUser()
const tokenLocal = useLocalStorage('token', '')
const $loading = useLoading()

const user = computed(() => credential.user)

// FOTO PROFIL
const photo = computed(() => {
    console.log();
    
    const p = `/storage/${credential.user.user?.photo}`
    return p ? p : '/assets/images/avatar5.png'
})

// LOGOUT HANDLER
async function logout() {
    const loader = $loading.show()

    const response = await fetch(`${import.meta.env.VITE_API_PATH}/api/v1/logout`, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${credential.user.accesstoken}`,
        },
    })

    const data = await response.json()
    loader.hide()

    if (response.status === 200) {
        await alertSuccess(data.message)
        tokenLocal.value = ''
        router.push({ name: 'Login' })
    } else {
        await alertError(data.message)
    }
}

// KONFIRMASI LOGOUT
function onLogout() {
    Swal.fire({
        title: 'Yakin ingin keluar?',
        text: 'Anda akan keluar dari sistem!',
        icon: 'warning',
        confirmButtonText: '<i class="fas fa-check"></i> Ya, keluar',
        cancelButtonText: '<i class="fas fa-times"></i> Batal',
        showCancelButton: true,
        buttonsStyling: false,
        confirmButtonColor: '#07945f',
        cancelButtonColor: '#d33',
        customClass: {
            confirmButton: 'btn btn-success btn-sm mr-2',
            cancelButton: 'btn btn-danger btn-sm',
        },
    }).then((res) => {
        if (res.isConfirmed) logout()
    })
}
</script>
