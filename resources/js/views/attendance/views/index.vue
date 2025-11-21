<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { alertSuccess, alertError } from '@/lib/alert'
import { apiRequest } from '@/lib/apiClient'
import dayjs from 'dayjs'
import CameraCapture from '../components/CameraCapture.vue'
import { useUser } from '../../../store'
import {formatTime, formatDate} from '@/lib/formatters'

const {user} = useUser()

// State
const loading = ref(false)
const todayAttendance = ref(null)
const attendanceHistory = ref([])
const showCamera = ref(false)
const cameraMode = ref('check-in') // 'check-in' or 'check-out'
const capturedImage = ref('')

// Computed
const canCheckIn = computed(() => {
    return !todayAttendance.value || todayAttendance.value.status === 'check_out'
})

const canCheckOut = computed(() => {
    return todayAttendance.value && todayAttendance.value.status === 'check_in'
})

const currentStatus = computed(() => {
    if (!todayAttendance.value) return 'Belum Absen'
    switch (todayAttendance.value.status) {
        case 'check_in': return 'Sudah Check-In'
        case 'check_out': return 'Sudah Check-Out'
        default: return todayAttendance.value.status
    }
})

// Methods
const fetchTodayAttendance = async () => {
    loading.value = true
    try {
        const { ok, data, error } = await apiRequest('teacher-attendances/today/' + user.user.id)
        
        if (ok) {
            todayAttendance.value = data.data?.attendances[0] || null
        } else {
            todayAttendance.value = null
        }
    } catch (err) {
        alertError(err.message)
    } finally {
        loading.value = false
    }
}

const openCamera = (mode) => {
    cameraMode.value = mode
    capturedImage.value = ''
    showCamera.value = true
}

const handleCameraCapture = async (imageData) => {
    capturedImage.value = imageData
    showCamera.value = false
    
    if (cameraMode.value === 'check-in') {
        await performCheckIn(imageData)
    } else {
        await performCheckOut(imageData)
    }
}

const performCheckIn = async (photoData) => {
    loading.value = true
    
    try {
        const formData = new FormData()
        formData.append('teacher_id', user.user.id)
        formData.append('date', new Date().toISOString().split('T')[0])
        formData.append('time_in', formatTime(new Date().toISOString().split('T')[1].split('.')[0]))
        formData.append('photo_in', dataURLtoFile(photoData, 'check-in.jpg'))
        formData.append('status', 'check_in')

        const { ok, data, error } = await apiRequest('teacher-attendances/check-in', {
            method: 'POST',
            body: formData,
            headers: { 'Content-Type': 'multipart/form-data' },
        })
        
        if (ok) {
            alertSuccess('Check-in berhasil!')
            await fetchTodayAttendance()
        } else {
            alertError(error)
            showCamera.value = true // Show camera again to retry
        }
    } catch (err) {
        alertError(err.message)
        showCamera.value = true // Show camera again to retry
    } finally {
        loading.value = false
    }
}

const performCheckOut = async (photoData) => {
    if (!todayAttendance.value) {
        alertError('Anda belum melakukan check-in')
        return
    }

    loading.value = true
    try {
        const formData = new FormData()
        formData.append('photo_out', dataURLtoFile(photoData, 'check-out.jpg'))
        formData.append('time_out', formatTime(new Date().toISOString().split('T')[1].split('.')[0]))

        const { ok, data, error } = await apiRequest(`teacher-attendances/${todayAttendance.value.id}/check-out`, {
            method: 'POST',
            body: formData,
            headers: { 'Content-Type': 'multipart/form-data' },
        })
        
        if (ok) {
            alertSuccess('Check-out berhasil!')
            await fetchTodayAttendance()
        } else {
            alertError(error)
            showCamera.value = true // Show camera again to retry
        }
    } catch (err) {
        alertError(err.message)
        showCamera.value = true // Show camera again to retry
    } finally {
        loading.value = false
    }
}

const dataURLtoFile = (dataurl, filename) => {
    const arr = dataurl.split(',')
    const mime = arr[0].match(/:(.*?);/)[1]
    const bstr = atob(arr[1])
    let n = bstr.length
    const u8arr = new Uint8Array(n)
    
    while (n--) {
        u8arr[n] = bstr.charCodeAt(n)
    }
    
    return new File([u8arr], filename, { type: mime })
}

const getStatusIcon = () => {
    if (!todayAttendance.value) return 'fas fa-clock not-yet'
    switch (todayAttendance.value.status) {
        case 'check_in': return 'fas fa-sign-in-alt check-in'
        case 'check_out': return 'fas fa-sign-out-alt check-out'
        default: return 'fas fa-clock not-yet'
    }
}

// Lifecycle
onMounted(() => {
    fetchTodayAttendance()
})
</script>

<template>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h4 class="font-serif-formal">Absensi Guru</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Absensi</a></li>
                        <li class="breadcrumb-item active">Absensi Guru</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Loading -->
            <div v-if="loading && !showCamera" class="text-center">
                <i class="fas fa-spinner fa-spin"></i> Memuat data...
            </div>

            <!-- Camera Modal -->
            <div v-if="showCamera" class="camera-modal">
                <div class="camera-modal-content">
                    <div class="camera-modal-header">
                        <h5 class="font-serif-formal">{{ cameraMode === 'check-in' ? 'Check-In' : 'Check-Out' }}</h5>
                        <button @click="showCamera = false" class="btn btn-tool">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="camera-modal-body">
                        <CameraCapture @capture="handleCameraCapture" />
                    </div>
                </div>
            </div>

            <!-- Attendance Status Card -->
            <div v-if="!showCamera" class="row">
                <div class="col-md-6">
                    <div class="card card-outline card-navy shadow-sm">
                        <div class="card-header border-bottom-0">
                            <h3 class="card-title font-serif-formal">
                                <i class="fas fa-user-clock mr-1"></i>
                                Status Absensi Hari Ini
                            </h3>
                        </div>
                        <div class="card-body text-center">
                            <div class="attendance-status">
                                <i :class="getStatusIcon()" class="status-icon"></i>
                                <h4 class="font-serif-formal">{{ currentStatus }}</h4>
                                <p v-if="todayAttendance" class="text-muted">
                                    {{ formatDate(todayAttendance.date) }}
                                </p>
                            </div>
                            
                            <div class="mt-3">
                                <button 
                                    v-if="canCheckIn" 
                                    @click="openCamera('check-in')"
                                    class="btn btn-navy btn-lg shadow-sm"
                                >
                                    <i class="fas fa-sign-in-alt mr-1"></i>
                                    Check-In
                                </button>
                                
                                <button 
                                    v-if="canCheckOut" 
                                    @click="openCamera('check-out')"
                                    class="btn btn-danger btn-lg shadow-sm"
                                >
                                    <i class="fas fa-sign-out-alt mr-1"></i>
                                    Check-Out
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Attendance Details -->
                <div v-if="todayAttendance" class="col-md-6">
                    <div class="card card-outline card-navy shadow-sm">
                        <div class="card-header border-bottom-0">
                            <h3 class="card-title font-serif-formal">
                                <i class="fas fa-info-circle mr-1"></i>
                                Detail Absensi
                            </h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm ledger-table">
                                <tr>
                                    <th>Check-In:</th>
                                    <td>{{ formatTime(todayAttendance.time_in) }}</td>
                                </tr>
                                <tr>
                                    <th>Check-Out:</th>
                                    <td>{{ formatTime(todayAttendance.time_out) }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td v-html="todayAttendance.status"></td>
                                </tr>
                            </table>
                            
                            <!-- Photo Preview -->
                            <div v-if="todayAttendance.photo_in || todayAttendance.photo_out" class="mt-3">
                                <h6 class="font-serif-formal">Foto Absensi:</h6>
                                <div class="photo-preview">
                                    <img 
                                        v-if="todayAttendance.photo_in" 
                                        :src="`/storage/${todayAttendance.photo_in}`" 
                                        alt="Check-in" 
                                        class="attendance-photo shadow-sm"
                                    />
                                    <img 
                                        v-if="todayAttendance.photo_out" 
                                        :src="`/storage/${todayAttendance.photo_out}`" 
                                        alt="Check-out" 
                                        class="attendance-photo shadow-sm"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.camera-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.camera-modal-content {
    background: white;
    border-radius: 12px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.camera-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
}

.camera-modal-body {
    padding: 1rem;
}

.attendance-status {
    margin: 2rem 0;
}

.status-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.status-icon.check-in {
    color: #28a745;
}

.status-icon.check-out {
    color: #dc3545;
}

.status-icon.not-yet {
    color: #6c757d;
}

.attendance-photo {
    max-width: 150px;
    height: auto;
    border-radius: 8px;
    margin: 0.5rem;
    border: 2px solid #dee2e6;
}

.photo-preview {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .camera-modal-content {
        width: 95%;
        margin: 1rem;
    }
    
    .attendance-status {
        margin: 1rem 0;
    }
    
    .status-icon {
        font-size: 3rem;
    }
    
    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }
}
</style>
