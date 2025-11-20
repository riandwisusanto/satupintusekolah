<script setup>
import { ref, onMounted, computed } from 'vue'
import { alertError } from '@/lib/alert'
import { apiRequest } from '@/lib/apiClient'
import dayjs from 'dayjs'
import { useUser } from '../../../store'

// State
const loading = ref(false)
const attendances = ref([])
const pagination = ref({
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1
})
const {user} = useUser()

// Filters
const filters = ref({
    month: dayjs().format('YYYY-MM'),
    status: ''
})

const statusOptions = [
    { value: '', label: 'Semua Status' },
    { value: 'check_in', label: 'Check-In' },
    { value: 'check_out', label: 'Check-Out' },
    { value: 'sick', label: 'Sakit' },
    { value: 'permission', label: 'Izin' },
    { value: 'on_leave', label: 'Cuti' }
]

// Computed
const filteredAttendances = computed(() => {
    return attendances.value.filter(attendance => {
        const matchesMonth = attendance.date.startsWith(filters.value.month)
        const matchesStatus = !filters.value.status || attendance.status === filters.value.status
        return matchesMonth && matchesStatus
    })
})

// Methods
const fetchAttendanceHistory = async (page = 1) => {
    loading.value = true
    try {
        const { ok, data, error } = await apiRequest(`teacher-attendances/history/${user.user.id}`)
        
        if (ok) {
            attendances.value = data.data.attendances || []
            // pagination.value = {
            //     current_page: data.current_page || 1,
            //     per_page: data.per_page || 10,
            //     total: data.total || 0,
            //     last_page: data.last_page || 1
            // }
        } else {
            alertError(error)
        }
    } catch (err) {
        alertError(err.message)
    } finally {
        loading.value = false
    }
}

const changePage = (page) => {
    fetchAttendanceHistory(page)
}

const formatDate = (dateString) => {
    return dayjs(dateString).format('DD MMMM YYYY')
}

const formatTime = (timeString) => {
    if (!timeString) return '-'
    return dayjs(timeString).format('HH:mm')
}

// Lifecycle
onMounted(() => {
    fetchAttendanceHistory()
})
</script>

<template>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h4>History Absensi</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Absensi</a></li>
                        <li class="breadcrumb-item active">History</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>Bulan:</label>
                    <input 
                        type="month" 
                        v-model="filters.month" 
                        class="form-control"
                        @change="fetchAttendanceHistory(1)"
                    />
                </div>
                <div class="col-md-3">
                    <label>Status:</label>
                    <select v-model="filters.status" class="form-control" @change="fetchAttendanceHistory(1)">
                        <option v-for="option in statusOptions" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
                <div class="col-md-6 text-right">
                    <button @click="fetchAttendanceHistory(1)" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="text-center">
                <i class="fas fa-spinner fa-spin"></i> Memuat data...
            </div>

            <!-- Attendances Table -->
            <div v-else class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i>
                        History Absensi
                    </h3>
                </div>
                <div class="card-body">
                    <div v-if="filteredAttendances.length === 0" class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Tidak ada data absensi untuk filter yang dipilih.
                    </div>
                    
                    <div v-else>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Check-In</th>
                                        <th>Check-Out</th>
                                        <th>Durasi</th>
                                        <th>Status</th>
                                        <th>Foto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="attendance in filteredAttendances" :key="attendance.id">
                                        <td>{{ formatDate(attendance.date) }}</td>
                                        <td>{{ formatTime(attendance.time_in) }}</td>
                                        <td>{{ formatTime(attendance.time_out) }}</td>
                                        <td>{{ attendance.work_duration || '-' }}</td>
                                        <td v-html="attendance.status_badge"></td>
                                        <td>
                                            <div class="photo-preview-mini">
                                                <img 
                                                    v-if="attendance.photo_in" 
                                                    :src="`/storage/${attendance.photo_in}`" 
                                                    alt="Check-in" 
                                                    class="mini-photo"
                                                />
                                                <img 
                                                    v-if="attendance.photo_out" 
                                                    :src="`/storage/${attendance.photo_out}`" 
                                                    alt="Check-out" 
                                                    class="mini-photo"
                                                />
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    <div v-if="pagination.last_page > 1" class="d-flex justify-content-center mt-3">
                        <nav>
                            <ul class="pagination">
                                <li 
                                    v-for="page in pagination.last_page" 
                                    :key="page"
                                    :class="['page-item', { active: page === pagination.current_page }]"
                                >
                                    <button 
                                        @click="changePage(page)"
                                        :disabled="loading"
                                        class="page-link"
                                    >
                                        {{ page }}
                                    </button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.photo-preview-mini {
    display: flex;
    gap: 0.25rem;
}

.mini-photo {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

.pagination {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
}

.page-item {
    margin: 0 0.25rem;
}

.page-link {
    padding: 0.5rem 0.75rem;
    border: 1px solid #dee2e6;
    background: white;
    color: #007bff;
    cursor: pointer;
    border-radius: 0.25rem;
    transition: all 0.2s;
}

.page-link:hover {
    background: #e9ecef;
    border-color: #adb5bd;
}

.page-item.active .page-link {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.page-link:disabled {
    cursor: not-allowed;
    opacity: 0.5;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .photo-preview-mini {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .mini-photo {
        width: 60px;
        height: 60px;
    }
}
</style>
