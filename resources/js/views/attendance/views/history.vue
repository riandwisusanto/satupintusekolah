<script setup>
import { ref, onMounted, computed } from 'vue'
import { apiRequest } from '@/lib/apiClient'
import dayjs from 'dayjs'
import { useUser } from '../../../store'
import { formatDate, formatTime } from '../../../lib/formatters'

// State
const tableRef = ref()
const showDetailModal = ref(false)
const attendances = ref([])
const loading = ref(false)
const currentFilter = ref('recent') // recent, today, week, month, all
const { user } = useUser()

const isAdmin = computed(() => {
    return user.user.role_id === 1
})

// Summary statistics
const stats = ref({
    total_days: 0,
    absent_days: 0,
    attendance_rate: 0
})

// Recent attendances (last 7 days)
const recentAttendances = computed(() => {
    return attendances.value.slice(0, 7)
})

// Table columns for modal
const detailColumns = [
    { field: 'date', display: 'Tanggal', sortable: false },
    ...(isAdmin.value ? [{ field: 'teacher.name', display: 'Guru', sortable: false }] : []),
    { field: 'time_in', display: 'In', sortable: false },
    { field: 'time_out', display: 'Out', sortable: false },
    { field: 'photo_in', display: 'Foto In', sortable: false },
    { field: 'photo_out', display: 'Foto Out', sortable: false }
]

const calculateWorkDuration = (timeIn, timeOut) => {
    if (!timeIn || !timeOut) return '-'
    
    const start = dayjs(`${dayjs().format('YYYY-MM-DD')}T${timeIn}`)
    const end = dayjs(`${dayjs().format('YYYY-MM-DD')}T${timeOut}`)
    
    if (!start.isValid() || !end.isValid()) return '-'
    
    const diff = end.diff(start, 'minute')
    const hours = Math.floor(diff / 60)
    const minutes = diff % 60
    
    return `${hours}j ${minutes}m`
}

const fetchSummaryStats = async () => {
    try {
        const { ok, data } = await apiRequest(`teacher-attendances/monthly-report?month=${dayjs().format('YYYY-MM')}${!isAdmin.value ? `&teacher_id=${user.user.id}` : ''}`)
        if (ok) {
            
            const {statistics} = data.data
            stats.value = {
                total_days: statistics.total_days,
                absent_days: statistics.absent_days,
                attendance_rate: statistics.total_days > 0 ? ((statistics.total_days - statistics.absent_days) / statistics.total_days) * 100 : 0
            }
        }
    } catch (err) {
        console.error('Error fetching stats:', err)
    }
}

const fetchRecentAttendances = async () => {
    loading.value = true
    try {
        const { ok, data } = await apiRequest(`teacher-attendances/history/${user.user.id}?per_page=7&sort_field=date&sort_order=desc`)
        if (ok && data.data) {
            attendances.value = data.data
        }
    } catch (err) {
        console.error('Error fetching attendances:', err)
    } finally {
        loading.value = false
    }
}

const viewDetails = () => {
    showDetailModal.value = true
}

const closeDetailModal = () => {
    showDetailModal.value = false
}

const showPhotoModal = ref(false)
const currentPhoto = ref({ url: '', type: '', date: '' })

const viewPhoto = (photoData) => {
    currentPhoto.value = {
        url: `/storage/${photoData.photo}`,
        type: photoData.type,
        date: photoData.date || new Date().toISOString()
    }
    showPhotoModal.value = true
}

const closePhotoModal = () => {
    showPhotoModal.value = false
    currentPhoto.value = { url: '', type: '', date: '' }
}

const refreshData = () => {
    fetchSummaryStats()
    fetchRecentAttendances()
}

const getStatus = (status) => {
    switch (status) {
        case 'check_in':
            return 'Hadir'
        case 'check_out':
            return 'Hadir'
        case 'sick':
            return 'Sakit'
        case 'permission':
            return 'Izin'
        default:
            return 'Cuti'
    }
}

// Lifecycle
onMounted(() => {
    refreshData()
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
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ stats.total_days }}</h3>
                            <p>Hadir Bulan Ini</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ stats.absent_days || 0 }}</h3>
                            <p>Tidak Hadir</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-times"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ stats.attendance_rate.toFixed(2) || 0 }}%</h3>
                            <p>Tingkat Kehadiran</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Filters -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="btn-group" role="group">
                        <button 
                            @click="refreshData" 
                            class="btn btn-default"
                            :class="{ 'btn-primary': currentFilter === 'recent' }"
                        >
                            <i class="fas fa-history"></i> Terkini
                        </button>
                        <button 
                            @click="viewDetails" 
                            class="btn btn-default"
                        >
                            <i class="fas fa-list"></i> Lihat Semua
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="text-center">
                <i class="fas fa-spinner fa-spin"></i> Memuat data...
            </div>

            <!-- Recent Activities Table -->
            <div v-else class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock"></i>
                        Aktivitas Terkini (7 hari terakhir)
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div v-if="recentAttendances.length === 0" class="alert alert-info m-3">
                        <i class="fas fa-info-circle"></i>
                        Belum ada data absensi untuk 7 hari terakhir.
                    </div>
                    
                    <div v-else class="table-responsive">
                        <table class="table table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th v-if="isAdmin">Guru</th>
                                    <th>In</th>
                                    <th>Out</th>
                                    <th>Durasi</th>
                                    <th>Status</th>
                                    <th>Foto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="attendance in recentAttendances" :key="attendance.id">
                                    <td>
                                        <span class="badge badge-secondary">{{ formatDate(attendance.date) }}</span>
                                    </td>
                                    <td v-if="isAdmin">{{ attendance.teacher?.name }}</td>
                                    <td>{{ formatTime(attendance.time_in) }}</td>
                                    <td>{{ formatTime(attendance.time_out) }}</td>
                                    <td>{{ attendance.work_duration || calculateWorkDuration(attendance.time_in, attendance.time_out) }}</td>
                                    <td>{{ getStatus(attendance.status) }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button 
                                                v-if="attendance.photo_in" 
                                                @click="viewPhoto({ photo: attendance.photo_in, type: 'in', date: attendance.date })"
                                                class="btn btn-info btn-sm"
                                                title="Lihat Foto Check-In"
                                            >
                                                <i class="fas fa-camera"></i>
                                            </button>
                                            <button 
                                                v-if="attendance.photo_out" 
                                                @click="viewPhoto({ photo: attendance.photo_out, type: 'out', date: attendance.date })"
                                                class="btn btn-warning btn-sm"
                                                title="Lihat Foto Check-Out"
                                            >
                                                <i class="fas fa-camera"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Detail Modal -->
    <div v-if="showDetailModal" class="modal fade show" style="display: block; background: rgba(0,0,0,0.5); z-index: 9999;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        Detail Absensi Lengkap
                    </h4>
                    <button type="button" @click="closeDetailModal" class="close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <TableServerSide
                        ref="tableRef"
                        title=""
                        :columns="detailColumns"
                        :endpoint="`teacher-attendances/history/${user.user.id}`"
                        :initial-sort="{ field: 'date', order: 'desc' }"
                        :per_page="10"
                    >
                        <template #cell-date="{ row }">
                            {{ formatDate(row.date) }}
                        </template>
                        <template #cell-time_in="{ row }">
                            {{ formatTime(row.time_in) }}
                        </template>
                        <template #cell-time_out="{ row }">
                            {{ formatTime(row.time_out) }}
                        </template>
                        <template #cell-photo_in="{ row }">
                            <img v-if="row.photo_in" :src="`/storage/${row.photo_in}`" class="mini-photo" />
                            <span v-else>-</span>
                        </template>
                        <template #cell-photo_out="{ row }">
                            <img v-if="row.photo_out" :src="`/storage/${row.photo_out}`" class="mini-photo" />
                            <span v-else>-</span>
                        </template>
                    </TableServerSide>
                </div>
            </div>
        </div>
    </div>

    <!-- Photo Viewer Modal -->
    <div v-if="showPhotoModal" class="modal fade show" style="display: block; background: rgba(0,0,0,0.8); z-index: 9999;">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="fas fa-camera"></i>
                        {{ currentPhoto.type === 'in' ? 'Foto Check-In' : 'Foto Check-Out' }}
                    </h4>
                    <button type="button" @click="closePhotoModal" class="close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <div class="photo-container">
                        <img 
                            :src="currentPhoto.url" 
                            :alt="`${currentPhoto.type === 'in' ? 'Check-In' : 'Check-Out'} Photo`" 
                            class="attendance-photo-large"
                        />
                    </div>
                    <div class="photo-info mt-3">
                        <p><strong>Tipe:</strong> {{ currentPhoto.type === 'in' ? 'Check-In' : 'Check-Out' }}</p>
                        <p><strong>Tanggal:</strong> {{ formatDate(currentPhoto.date) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.mini-photo {
    width: 30px;
    height: 30px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.badge-secondary {
    background-color: #6c757d;
    color: white;
}

/* Photo Viewer Styles */
.photo-container {
    max-height: 70vh;
    overflow: auto;
    text-align: center;
}

.attendance-photo-large {
    max-width: 100%;
    max-height: 60vh;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.photo-info {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    margin-top: 15px;
}

.photo-info p {
    margin: 5px 0;
    font-size: 14px;
}

/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 9999;
    width: 100%;
    height: 100%;
    overflow: hidden;
    outline: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 60px 20px 20px 280px; /* Top padding untuk topbar, left padding untuk sidebar */
    box-sizing: border-box;
}

.modal-dialog {
    position: relative;
    width: auto;
    margin: 0;
    max-width: 90%;
    max-height: 90vh;
    overflow: auto;
}

.modal-content {
    position: relative;
    display: flex;
    flex-direction: column;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid rgba(0, 0, 0, 0.2);
    border-radius: 0.3rem;
    outline: 0;
}

.modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 1rem 1rem;
    border-bottom: 1px solid #dee2e6;
    border-top-left-radius: calc(0.3rem - 1px);
    border-top-right-radius: calc(0.3rem - 1px);
}

.modal-title {
    margin-bottom: 0;
    line-height: 1.5;
}

.modal-body {
    position: relative;
    flex: 1 1 auto;
    padding: 1rem;
}

.modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 1rem;
    border-top: 1px solid #dee2e6;
}

.close {
    float: right;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    color: #000;
    text-shadow: 0 1px 0 #fff;
    opacity: 0.5;
    background: transparent;
    border: 0;
    cursor: pointer;
}

.close:hover {
    color: #000;
    text-decoration: none;
    opacity: 0.75;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .modal {
        padding: 60px 10px 10px 10px; /* Reduced padding for mobile */
    }
    
    .modal-dialog {
        max-width: 95%;
        margin: 0;
    }
    
    .mini-photo {
        width: 25px;
        height: 25px;
    }
    
    .btn-group-sm > .btn {
        padding: 0.2rem 0.4rem;
        font-size: 0.8rem;
    }
    
    .attendance-photo-large {
        max-height: 50vh;
    }
    
    .photo-info {
        padding: 10px;
    }
    
    .small-box {
        margin-bottom: 1rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .table th, .table td {
        padding: 0.5rem;
    }
}
</style>
