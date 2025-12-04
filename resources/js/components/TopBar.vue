<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import UserProfileDropDown from './UserProfileDropDown.vue'
import { apiRequest } from '../lib/apiClient'

const isOnline = ref(navigator.onLine)
const isSlowConnection = ref(false)

// School data
const schoolData = ref({
    name: '',
    logo: ''
})

// Handle image error
const handleImageError = (event) => {
    // Set default logo if image fails to load
    event.target.src = '/assets/images/logo-jago.png'
}

// Load school data
const loadSchoolData = async () => {
    try {
        // Get school name
        const nameResponse = await apiRequest('configurations/school_name')
        if (nameResponse.ok && nameResponse.data.data) {
            schoolData.value.name = nameResponse.data.data
        }
        

        // Get school logo
        const logoResponse = await apiRequest('configurations/school_logo')
        if (logoResponse.ok && logoResponse.data.data) {
            schoolData.value.logo = `/storage/${logoResponse.data.data}`
        }
    } catch (error) {
        console.error('Failed to load school data:', error)
        // Don't set default values, keep empty if config is not available
    }
}

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
    loadSchoolData() // Load school data
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
            
            <!-- School Brand -->
            <li class="nav-item school-brand align-items-center">
                <img 
                    v-if="schoolData.logo"
                    :src="schoolData.logo" 
                    :alt="schoolData.name"
                    class="school-logo"
                    @error="handleImageError"
                />
                <span v-if="schoolData.name" class="school-name">{{ schoolData.name }}</span>
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

/* School Brand Styling */
.school-brand {
    margin-left: 1rem;
    padding: 0.5rem 0;
    transition: all 0.3s ease;
}

.school-logo {
    height: 32px;
    width: 32px;
    object-fit: contain;
    margin-right: 0.75rem;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
}

.school-logo:hover {
    transform: scale(1.05);
}

.school-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #001f3f;
    letter-spacing: 0.5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 300px;
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .school-brand {
        margin-left: 0.5rem;
    }
    
    .school-logo {
        height: 24px;
        width: 24px;
        margin-right: 0.5rem;
    }
    
    .school-name {
        font-size: 0.9rem;
        max-width: 120px;
    }
}

@media (max-width: 768px) {
    .school-brand {
        margin-left: 0.75rem;
    }
    
    .school-logo {
        height: 26px;
        width: 26px;
        margin-right: 0.5rem;
    }
    
    .school-name {
        font-size: 0.95rem;
        max-width: 150px;
    }
}

@media (max-width: 1024px) {
    .school-name {
        max-width: 200px;
        font-size: 1rem;
    }
    
    .school-logo {
        height: 28px;
        width: 28px;
        margin-right: 0.5rem;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .school-name {
        color: #000000;
    }
}

/* Animation for loading state */
.school-brand {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
</style>
